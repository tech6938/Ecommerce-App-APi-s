<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{

    /**
     * Get COD payment method
     */
    public function getCOD()
    {
        $cod = PaymentMethod::where('code', 'cod')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'COD payment method retrieved successfully',
            'data' => [
                'is_active' => (bool) $cod->is_active,
                'name' => $cod->name,
                'type' => $cod->code,
                'description' => $cod->description,
            ]
        ]);
    }

    /**
     * Get all online payment methods
     */
    public function getOnlineMethods()
    {
        $methods = PaymentMethod::where('type', 'online')
            ->active()
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Online payment methods retrieved successfully',
            'data' => PaymentMethodResource::collection($methods)
        ]);
    }

    /**
     * Get payment method credentials (for frontend checkout)
     * Only returns non-sensitive public credentials
     */
    public function getCredentials($code)
    {
        $method = PaymentMethod::where('code', $code)
            ->active()
            ->firstOrFail();

        $credentials = [];

        switch ($code) {
            case 'stripe':
                $credentials = [
                    'publishable_key' => $method->api_key,
                    'environment' => $method->environment,
                ];
                break;
            case 'paypal':
                $credentials = [
                    'client_id' => $method->api_key,
                    'environment' => $method->environment,
                ];
                break;
            case 'paytm':
                $credentials = [
                    'merchant_id' => $method->merchant_id,
                    'environment' => $method->environment,
                ];
                break;
            case 'flutterwave':
                $credentials = [
                    'public_key' => $method->public_key,
                    'environment' => $method->environment,
                ];
                break;
            case 'razorpay':
                $credentials = [
                    'key_id' => $method->api_key,
                    'environment' => $method->environment,
                ];
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment credentials retrieved successfully',
            'data' => [
                'code' => $method->code,
                'name' => $method->name,
                'environment' => $method->environment,
                'callback_url' => $method->callback_url,
                'credentials' => $credentials,
            ]
        ]);
    }
}
