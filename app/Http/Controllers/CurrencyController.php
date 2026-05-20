<?php
namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::latest()->get();
        return view('currency.index', compact('currencies'));
    }

    public function create()
    {
        return view('currency.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'currency_name' => 'required',
            'currency_code' => 'required|unique:currencies,currency_code',
        ]);

        Currency::create($request->all());

        return redirect()->route('currency.index')
            ->with('success', 'Currency created successfully');
    }

    public function edit($id)
    {
        $currency = Currency::findOrFail($id);
        return view('currency.edit', compact('currency'));
    }

    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $request->validate([
            'currency_name' => 'required',
            'currency_code' => 'required|unique:currencies,currency_code,' . $id,
        ]);

        $currency->update($request->all());

        return redirect()->route('currency.index')
            ->with('success', 'Currency updated successfully');
    }

    public function destroy($id)
    {
        Currency::destroy($id);

        return redirect()->route('currency.index')
            ->with('success', 'Currency deleted successfully');
    }

    public function changeStatus(Request $request)
    {
        $currency = Currency::findOrFail($request->id);

        if ($request->status == 1) {
            // Pehle sab currencies ko inactive karo
            Currency::where('id', '!=', $currency->id)->update(['status' => 0]);
        }

        // Ab selected currency ka status update karo
        $currency->status = $request->status;
        $currency->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }
}