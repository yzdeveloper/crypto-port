<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pnl extends Model
{
    use HasFactory;
    protected $fillable = ['value'];
    public $timestamps = true;
    protected $table = 'pnl'; 
}

