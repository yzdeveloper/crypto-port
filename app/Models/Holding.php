<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holding extends Model
{
    use HasFactory;

    // Define the table name (optional if it matches the default convention)
    protected $table = 'holdings';

    // Fillable fields to allow mass assignment
    protected $fillable = [
        'instrument',
        'instrument_first',
        'instrument_second',
        'purchase_quantity',
        'purchase_price',
        'sell_quantity',
        'selling_price',
    ];

    // Cast decimals properly
    protected $casts = [
        'purchase_quantity' => 'decimal:9',
        'purchase_price' => 'decimal:9',
        'sell_quantity' => 'decimal:9',
        'selling_price' => 'decimal:9',
    ];
}

