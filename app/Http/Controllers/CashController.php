<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
            // Optionally, create an empty database file if it doesn't exist
            File::put($databasePath, '');    
            echo "Database created and migrations ran successfully.";
        }
    }

    private function getCash() {
        $tableName = 'cash';
        $cashValue = 0;

        $this->createDatabaseIfNotExists();
        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, 
                function ($table) {
                    $table->decimal('value', 25, 2);  
                    $table->timestamps();  
                });
        }
        
        $cash = Cash::first();
        if ($cash === null) {
            $cash = new Cash();
            $cash->value = $cashValue;
            $cash->save();
        } else {
            $cashValue = $cash->value;
        }

        return $cashValue;
    }

    private function saveCash($newCashvalue) {
        $cash = Cash::first();
        $cashToSave = $cash ?? new Cash();
        $cashToSave->value = $newCashvalue;
        $cashToSave->save();
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
            $value = $this->convertToFloat($amountStr);
            $oldCash = $this->getCash();
            $newCash = $oldCash + $value;
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
