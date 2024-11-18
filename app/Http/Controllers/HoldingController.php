<?php

namespace App\Http\Controllers;

use App\Models\Holding;
use Illuminate\Http\Request;


class HoldingController extends Controller
{
    public function get(Request $request)
    {
        $query = Holding::query();

        // Filtering by instrument (optional)
        if ($request->has('instrument')) {
            $query->where('instrument', $request->input('instrument'));
        }

        // Sorting by instrument (optional)
        if ($request->has('sort_by')) {
            $sortDirection = $request->input('sort_direction', 'asc'); // Default sorting direction is ascending
            $query->orderBy($request->input('sort_by'), $sortDirection);
        }

        // Paginate the results (you can adjust the per page count)
        $holdings = $query->paginate(15);

        return response()->json($holdings);
    }

    // Update purchase quantity and price (bought)
    public function bought(Request $request)
    {
        $request->validate([
            'purchase_quantity' => 'nullable|numeric',
            'purchase_price' => 'nullable|numeric',
        ]);

        $holding = Holding::findOrFail($id);
        
        // Update purchase fields
        if ($request->has('purchase_quantity')) {
            $holding->purchase_quantity = $request->input('purchase_quantity');
        }
        if ($request->has('purchase_price')) {
            $holding->purchase_price = $request->input('purchase_price');
        }

        $holding->save();

        return response()->json($holding);
    }

    // Update sell quantity and price (sold)
    public function sold(Request $request)
    {
        $request->validate([
            'instrument' => '|string',
            'sell_quantity' => 'nullable|numeric',
            'selling_price' => 'nullable|numeric',
        ]);

        $holding = new Holding();

        // Update sell fields
        if ($request->has('sell_quantity')) {
            $holding->sell_quantity = $request->input('sell_quantity');
        }
        if ($request->has('selling_price')) {
            $holding->selling_price = $request->input('selling_price');
        }

        $holding->save();

        return response()->json($holding);
    }
}
