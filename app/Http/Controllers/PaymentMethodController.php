<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::orderBy('sort_order')->get();
        return view('payment-methods.index', compact('methods'));
    }


    public function create()
    {
        return view('payment-methods.create');
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_methods',
            'type' => 'required|in:online,bank_transfer',
            'description' => 'nullable|string',
            'environment' => 'required|in:sandbox,production',
            'callback_url' => 'nullable|url',
            'api_key' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'merchant_key' => 'nullable|string',
            'merchant_id' => 'nullable|string',
            'public_key' => 'nullable|string',
            'private_key' => 'nullable|string',
            'webhook_secret' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        PaymentMethod::create([
            'name' => $request->name,
            'code' => strtolower($request->code),
            'type' => $request->type,
            'description' => $request->description,
            'environment' => $request->environment,
            'callback_url' => $request->callback_url,
            'api_key' => $request->api_key,
            'secret_key' => $request->secret_key,
            'merchant_key' => $request->merchant_key,
            'merchant_id' => $request->merchant_id,
            'public_key' => $request->public_key,
            'private_key' => $request->private_key,
            'webhook_secret' => $request->webhook_secret,
            'is_active' => $request->is_active ?? false,
            'sort_order' => PaymentMethod::max('sort_order') + 1,
        ]);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Payment method created successfully');
    }

    public function edit($id)
    {
        $method = PaymentMethod::findOrFail($id);
        return view('payment-methods.edit', compact('method'));
    }

    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);

        // If COD, only update status
        if ($method->code === 'cod') {
            $method->update([
                'is_active' => $request->is_active ? true : false,
            ]);
            return redirect()->route('admin.payment-methods.index')
                ->with('success', 'COD status updated successfully');
        }

        // For online payment methods
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'environment' => 'required|in:sandbox,production',
            'callback_url' => 'nullable|url',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Prepare credentials based on payment method
        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'environment' => $request->environment,
            'callback_url' => $request->callback_url,
            'is_active' => $request->is_active ?? false,
        ];

        // Add gateway-specific credentials
        switch ($method->code) {
            case 'paytm':
                $updateData['merchant_key'] = $request->merchant_key;
                $updateData['merchant_id'] = $request->merchant_id;
                break;
            case 'stripe':
                $updateData['api_key'] = $request->api_key;
                $updateData['secret_key'] = $request->secret_key;
                $updateData['webhook_secret'] = $request->webhook_secret;
                break;
            case 'paypal':
                $updateData['api_key'] = $request->client_id;
                $updateData['secret_key'] = $request->client_secret;
                break;
            case 'flutterwave':
                $updateData['public_key'] = $request->public_key;
                $updateData['secret_key'] = $request->secret_key;
                $updateData['private_key'] = $request->encryption_key;
                break;
        }

        $method->update($updateData);

        return redirect()->route('admin.payment-methods.index')
            ->with('success', 'Payment method updated successfully');
    }

    public function toggleStatus($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->update(['is_active' => !$method->is_active]);

        $status = $method->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.payment-methods.index')
            ->with('success', "Payment method {$status} successfully");
    }
}
