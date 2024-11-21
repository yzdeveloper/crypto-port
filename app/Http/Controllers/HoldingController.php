<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Database\PortfolioDB;
use App\Models\Holding;
use App\Models\Pnl;
use App\Http\Controllers\CashController;


class HoldingController extends Controller
{
    public function get(Request $request)
    {
        PortfolioDB::ensureDB();
        $query = Holding::query();

        // Filtering  (optional)  
        $instrument_search = $request->query('instrument');
        if (!is_null($instrument_search)) {
            $query->where('instrument', 'like', '%' . $instrument_search . '%');
        }

        // Sorting  (optional)
        $sorting = [];
        foreach($request->query() as $key => $value) {
            if (str_starts_with($key, 'sort_by')) {
                Log::debug('sorting:' . $key . ' ' . $value);
                $splitted = explode('|', $value);
                $column = $splitted[0];
                $order = count($splitted) > 1 ? $splitted[1] : 'asc';
                Log::debug('sorting2:' . $order);
                if (PortfolioDB::column_exist('holdings', $column)) {
                    $sorting[] = [ $column, (strcasecmp($order, 'desc') == 0 ? 'desc' : 'asc')  ];
                }
            }
        }

        foreach($sorting as [$column, $order]) {
            $query->orderBy($column, $order);
        }

        $query->select( [ 
            'instrument', 
            'quantity',
            'price'
        ]);

        Log::debug('SQL:' . $query->toSql());
        return response()->json($query->get());
    }

    private static function returnPnl(string $value) {
        Log::info('Pnl:' . $value);
        return response($value, 200)
            ->header('Content-Type', 'text/plain');            
    } 

    private static function getPnl() {
        $pnlValue = 0;
        
        PortfolioDB::ensureDB();
        $pnl = Pnl::first();
        if ($pnl === null) {
            $pnl = new Pnl();
            $pnl->value = $pnlValue;
            $pnl->save();
            Log::debug('Pnl record saved...');
        } else {
            $pnlValue = $pnl->value;
        }

        return $pnlValue;
    }


    public function getReleasedPnl(Request $request)
    {
        $pnlValue = self::getPnl();
        Log::debug('Pnl:' . $pnlValue);
        return self::returnPnl($pnlValue);
    }

    public function bought(Request $request)
    {
        PortfolioDB::ensureDB();
        $request->validate([
            'instrument' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $instrument = $request->input('instrument');
        $quantity = $request->input('quantity');
        $price = $request->input('price');
        Log::info('bought: ' . $instrument . ' quantity: ' . $quantity . ' price: ' . $price);

        $existing_instrument = Holding::query()->where('instrument', $instrument)->get();
        if (count($existing_instrument) > 0 ) {
            $holding = $existing_instrument->first();
            $old_quantity = $holding->quantity;
            Log::debug('$old_quantity:' . $old_quantity);
            $old_price = $holding->price;
            Log::debug('$old_price:' . $old_price);
            $new_quantity = $quantity;
            Log::debug('$new_quantity:' . $new_quantity);
            $new_price =  $price;
            Log::debug('$new_price:' . $new_price);
            $holding->quantity = bcadd($old_quantity, $new_quantity, 9);
            Log::debug('$holding->quantity:' . $holding->quantity);
            $volume = bcadd(bcmul($old_quantity, $old_price, 9), bcmul($new_quantity, $new_price, 9), 9);
            Log::debug('$volume:' . $volume);
            $holding->price = bcdiv($volume, $holding->quantity, 9);        
            Log::debug('$holding->price:' . $holding->price);
        } else {
            $holding = new Holding();
            $holding->instrument = $instrument;
            $holding->quantity = $quantity;
            $holding->price = $price;        
        }

        DB::beginTransaction();

        try {
            $holding->save();
            
            // update cash
            $volume = bcsub(0, bcmul($quantity, $price, 9), 9); 
            app('App\Http\Controllers\CashController')->addCashImpl($volume);

            DB::commit();                    
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => 'failed'], 500);            
        }   
    }

    public function sold(Request $request)
    {
        Log::debug('sold: ++' );
        PortfolioDB::ensureDB();

        $request->validate([
            'instrument' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        $instrument = $request->input('instrument');
        $quantity = $request->input('quantity');
        $price = $request->input('price');
        $quantity_own = '0';
        Log::info('selling: ' . $instrument . ' quantity: ' . $quantity . ' price: ' . $price);

        Log::debug('sold: Got:' . $instrument . ' sold ' . $quantity . ' for ' . $price );
        $existing_instrument = Holding::query()->where('instrument', $instrument)->get();
        if (count($existing_instrument) > 0 ) {
            Log::debug('sold: Instrument found');
            $holding = $existing_instrument->first();
            $quantity_own = $holding->quantity;
            Log::debug('sold: Own: ' . $quantity_own . ' selling ' . $quantity);
            if (bccomp($quantity_own, $quantity, 9) >= 0) {               
                Log::debug('sold: Adequate quantity.');
                $old_quantity = $holding->quantity;
                // $holding->price - stay the same
                $holding->quantity = bcsub($holding->quantity, $quantity, 9);

                DB::beginTransaction();

                try {
                    if (bccomp(0, $holding->quantity, 9) === 0) {
                        $holding->delete();
                    } else {
                        $holding->save();
                    }

                    Log::debug('sold: holding updated.');

                    // Update PnL
                    $releasedPnL = bcmul($quantity, bcsub($price, $holding->price, 9), 9);
                    self::updatePnl($releasedPnL);
                    Log::debug('sold: pnl updated.');
                    
                    // update cash
                    $volume = bcmul($quantity, $price, 9); 
                    Log::debug('sold: volume.' . $volume);
                    app('App\Http\Controllers\CashController')->addCashImpl($volume);
                    Log::debug('sold: cash updated.');

                    DB::commit();                    
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json(['success' => 'failed'], 500);            
                }   

                return response()->json(['success' => 'success'], 200);            
            }
        } 
            
        return response()->json(['message' => 'Have ' . $quantity_own . ' only, but selling ' . $quantity ], 400);            
    }

    private static function updatePnl(string $pnl) {
        $pnlValue = self::getPnl();
        Log::debug('Pnl:' . $pnlValue);
        $newPnl = bcadd($pnlValue,  $pnl, 2);
        Log::debug('updatePnl: $newPnl: ' . $newPnl);
        self::savePnl($newPnl);             
    }

    private static function savePnl(string $newPnlValue) {
        $pnl = Pnl::first();
        if ($pnl === null) {
            Log::debug('savePnl: no pnl record found.' );
        }

        $pnlToSave = $pnl ?? new Pnl();
        $pnlToSave->value = $newPnlValue;
        $pnlToSave->save();
    }
}
