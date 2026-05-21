<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CodCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CodChargeController extends Controller
{
    /**
     * Display list of COD charges
     */
    public function index()
    {
        $charges = CodCharge::orderBy('sort_order')
            ->orderBy('min_order_amount')
            ->get();

        return view('cod-charges.index', compact('charges'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('cod-charges.create');
    }

    /**
     * Store new COD charge
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'min_order_amount' => 'required|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|gt:min_order_amount',
            'charge_amount' => 'required|numeric|min:0',
            'charge_type' => 'required|in:fixed,percentage',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check for overlapping ranges
        $overlap = CodCharge::where(function($q) use ($request) {
            $q->whereBetween('min_order_amount', [$request->min_order_amount, $request->max_order_amount ?? PHP_FLOAT_MAX])
              ->orWhereBetween('max_order_amount', [$request->min_order_amount, $request->max_order_amount ?? PHP_FLOAT_MAX]);
        })->exists();

        if ($overlap) {
            return back()->with('error', 'Amount range overlaps with existing rule')->withInput();
        }

        CodCharge::create([
            'min_order_amount' => $request->min_order_amount,
            'max_order_amount' => $request->max_order_amount,
            'charge_amount' => $request->charge_amount,
            'charge_type' => $request->charge_type,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('cod-charges.index')
            ->with('success', 'COD charge created successfully');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $codCharge = CodCharge::findOrFail($id);
        return view('cod-charges.edit', compact('codCharge'));
    }

    /**
     * Update COD charge
     */
    public function update(Request $request, $id)
    {
        $charge = CodCharge::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'min_order_amount' => 'required|numeric|min:0',
            'max_order_amount' => 'nullable|numeric',
            'charge_amount' => 'required|numeric|min:0',
            'charge_type' => 'required|in:fixed,percentage',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check for overlapping ranges (excluding current)
        $overlap = CodCharge::where('id', '!=', $id)
            ->where(function($q) use ($request) {
                $q->whereBetween('min_order_amount', [$request->min_order_amount, $request->max_order_amount ?? PHP_FLOAT_MAX])
                  ->orWhereBetween('max_order_amount', [$request->min_order_amount, $request->max_order_amount ?? PHP_FLOAT_MAX]);
            })->exists();

        if ($overlap) {
            return back()->with('error', 'Amount range overlaps with existing rule')->withInput();
        }

        $charge->update([
            'min_order_amount' => $request->min_order_amount,
            'max_order_amount' => $request->max_order_amount,
            'charge_amount' => $request->charge_amount,
            'charge_type' => $request->charge_type,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('cod-charges.index')
            ->with('success', 'COD charge updated successfully');
    }

    /**
     * Delete COD charge
     */
    public function destroy($id)
    {
        $charge = CodCharge::findOrFail($id);
        $charge->delete();

        return redirect()->route('cod-charges.index')
            ->with('success', 'COD charge deleted successfully');
    }

    /**
     * Toggle status
     */
    public function toggleStatus($id)
    {
        $charge = CodCharge::findOrFail($id);
        $charge->update(['is_active' => !$charge->is_active]);

        $status = $charge->is_active ? 'activated' : 'deactivated';
        return redirect()->route('cod-charges.index')
            ->with('success', "COD charge {$status} successfully");
    }
}
