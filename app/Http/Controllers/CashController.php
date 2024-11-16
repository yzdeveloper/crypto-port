<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CashController extends Controller
{
    private function returnCash(float $value) {
        return response(strVal($value), 200)
            ->header('Content-Type', 'text/plain');            
    } 

    public function cash(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->returnCash(100);
        }

        // Handle POST request 
        if ($request->isMethod('post')) {
            $amountStr = $request->query('cash'); 
            return $this->returnCash((float)$amountStr);
        }

        return response()->json(['message' => 'Invalid request method.']);
    }
}
