<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Database\PortfolioDB;
use App\Models\Holding;

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
                $splitted = explode('|', $value);
                $column = $splitted[0];
                $order = count($splitted) > 1 ? $splitted[1] : 'asc';
                if (PortfolioDB::column_exist('holdings', $column)) {
                    $sorting[] = [ $column, (strcasecmp($order, 'desc') == 1 ? 'desc' : 'asc')  ];
                }
            }
        }

        foreach($sorting as [$column, $order]) {
            $query->orderBy($column, $order);
        }

        $query->select( [ 
            'instrument', 
            DB::raw('(SUM(purchase_quantity) - SUM(sold_quantity)) as quantity'), 
            DB::raw('( (SUM(purchase_quantity*purchase_price) - SUM(sold_quantity*sold_price)) / (SUM(purchase_quantity) - SUM(sold_quantity))) as price'
        )]);
        $query->groupBy('instrument');

        Log::debug('SQL:' . $query->toSql());
        return response()->json($query->get());
    }

    public function bought(Request $request)
    {
        PortfolioDB::ensureDB();
        $request->validate([
            'instrument' => 'required|string',
            'purchase_quantity' => 'required|numeric',
            'purchase_price' => 'required|numeric',
        ]);

        $instrument = $request->input('instrument');
        [ $first, $second ] = getFirstSecond($instrument);

        $holding = new Holding();
        $holding->instrument = $request->input('instrument');
        $holding->instrument_first = $first; 
        $holding->instrument_second = $second; 
        $holding->purchase_quantity = $request->input('purchase_quantity');
        $holding->purchase_price = $request->input('purchase_price');        
        $holding->save();
    }

    private function getFirstSecond(string $instrument) {
        return explode('-', $instrument);
    }

    public function sold(Request $request)
    {
        PortfolioDB::ensureDB();
        $request->validate([
            'instrument' => 'required|string',
            'sell_quantity' => 'required|numeric',
            'selling_price' => 'required|numeric',
        ]);

        $instrument = $request->input('instrument');
        [ $first, $second ] = getFirstSecond($instrument);

        $holding = new Holding();
        $holding->instrument = $instrument; 
        $holding->instrument_first = $first; 
        $holding->instrument_second = $second; 
        $holding->sell_quantity = $request->input('sold_quantity');
        $holding->selling_price = $request->input('sold_price');
        $holding->save();
    }
}
