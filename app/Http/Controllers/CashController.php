<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Database\PortfolioDB;
use App\Models\Cash;

class CashController extends Controller
{    
    private function returnCash(string $value) {
        return response($value, 200)
            ->header('Content-Type', 'text/plain');            
    } 

    private function getCash() : string {
        $cashValue = 0;
        
        PortfolioDB::ensureDB();
        $cash = Cash::first();
        if ($cash === null) {
            $cash = new Cash();
            $cash->value = $cashValue;
            $cash->save();
            Log::debug('Cash record saved...');
        } else {
            $cashValue = $cash->value;
        }

        Log::debug('Cash returned:' . $cashValue);
        return (string)$cashValue;
    }

    private function saveCash(string $newCashvalue) {
        $cash = Cash::first();
        if ($cash === null) {
            Log::debug('saveCash: no cash record found.' );
        }

        $cashToSave = $cash ?? new Cash();
        $cashToSave->value = $newCashvalue;
        $cashToSave->save();
    }

    public function addCashImpl($value) {
        $oldCash = $this->getCash();
        $newCash = bcadd($oldCash,  $value, 2);
        if ($newCash < 0) {
            return $oldCash;
        }

        Log::debug('addCash: $newCash: ' . $newCash);
        $this->saveCash($newCash);  
        return $newCash;           
    }

    public function addCash(Request $request)
    {
        // Handle POST request 
        if ($request->isMethod('post')) {
            $amountStr = (string)$request->query('value');
            if (!is_numeric($amountStr)) {
                Log::debug('addCash: is not numeric: $amountStr: ' . $amountStr);
                return response()->json(['message' => 'Invalid value:' . $amountStr ], 400);
            }

            $newCash = $this->addCashImpl($amountStr);
            if ($newCash < 0) {
                Log::debug('addCash: is not numeric: $amountStr: ' . $amountStr);
                return response()->json(['message' => 'Result cash should be positive' ], 400);
            }
    
            return $this->returnCash($newCash);
        }

        return response()->json(['message' => 'Invalid request method.'], 405);
    }

    public function cash(Request $request)
    {
        if ($request->isMethod('get')) {
            $cashValue = $this->getCash();            
            return $this->returnCash($cashValue);
        }

        return response()->json(['message' => 'Invalid request method.'], 405);
    }

}
