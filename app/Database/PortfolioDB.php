<?php

namespace App\Database;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PortfolioDB 
{
    public static function ensureDB() {
        self::createDatabaseIfNotExists();
        self::createSchemas();
    }

    public static function column_exist(string $tableName, string $columnName): bool {
        return Schema::hasColumn($tableName, $columnName);
    }

    private static function createSchemas() {
        $cashTableName = 'cash';

        if (! Schema::hasTable($cashTableName)) {
            Schema::create($cashTableName, 
                function ($table) {
                    $table->id();
                    $table->decimal('value', 25, 2);  
                    $table->timestamps();  
                });
        }

        if (! Schema::hasTable('holdings')) {
            Schema::create('holdings', function ($table) {
                $table->id();
                $table->string('instrument')->unique(); 
                $table->string('instrument_first'); 
                $table->string('instrument_second'); 
                $table->decimal('quantity', 60, 9); 
                $table->decimal('price', 60, 9); 
                $table->timestamps(); // created_at, updated_at
                });
        }    
    } 

    private static function createDatabaseIfNotExists() {
        $databasePath = env('DB_DATABASE', database_path('database.sqlite'));
   
        if (!File::exists($databasePath)) {
            File::put($databasePath, '');    
            Log::debug('Database created: ' . $databasePath);
        }
    }
}
