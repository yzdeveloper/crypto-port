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
        'quantity',
        'price',
    ];

    // Cast decimals properly
    protected $casts = [
        'quantity' => 'decimal:9',
        'price' => 'decimal:9',
    ];
}

