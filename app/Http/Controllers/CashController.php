<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use App\Models\Cash;

class CashController extends Controller
{    
    private function returnCash(float $value) {
        return response(strVal($value), 200)
            ->header('Content-Type', 'text/plain');            
    } 

    private function createDatabaseIfNotExists() {
        $databasePath = env('DB_DATABASE', database_path('database.sqlite'));
   
        if (!File::exists($databasePath)) {
            File::put($databasePath, '');    
            Log::debug('Database created: ' . $databasePath);
        }
    }

    private function getCash() {
        $tableName = 'cash';
        $cashValue = 0;

        $this->createDatabaseIfNotExists();
        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, 
                function ($table) {
                    $table->id();
                    $table->decimal('value', 25, 2);  
                    $table->timestamps();  
                });
        }
        
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
        return $cashValue;
    }

    private function saveCash($newCashvalue) {
        $cash = Cash::first();
        if ($cash === null) {
            Log::debug('saveCash: no cash record found.' );
        }

        $cashToSave = $cash ?? new Cash();
        $cashToSave->value = $newCashvalue;

        try{
            $cashToSave->save();
        }
        catch (\PDOException $e) {
            Log::error('saveCash: saving:' . $e->getMessage() );
        }

        Log::debug('saveCash: saved.' );
    }

    private function convertToFloat($value)
    {
        // Attempt to convert to float
        $floatValue = floatval($value);
    
        // Check if the result is a valid number
        if (is_numeric($floatValue)) {
            return $floatValue;
        }
    
        // If not a valid float, handle the error
        throw new InvalidArgumentException("The provided value is not a valid float: $value");
    }

    public function addCash(Request $request)
    {
        // Handle POST request 
        if ($request->isMethod('post')) {
            $amountStr = $request->query('value');
            Log::debug('addCash: $amountStr: ' . $amountStr);
            $value = $this->convertToFloat($amountStr);
            $oldCash = $this->getCash();
            Log::debug('addCash: $oldCash: ' . $oldCash);
            $newCash = $oldCash + $value;
            Log::debug('addCash: $newCash: ' . $newCash);
            $this->saveCash($newCash);             
            return $this->returnCash($newCash);
        }

        return response()->json(['message' => 'Invalid request method.']);
    }

    public function cash(Request $request)
    {
        if ($request->isMethod('get')) {
            $cashValue = $this->getCash();            
            return $this->returnCash($cashValue);
        }

        return response()->json(['message' => 'Invalid request method.']);
    }

}
