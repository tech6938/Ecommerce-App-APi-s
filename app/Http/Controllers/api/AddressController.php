<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    /**
     * Get all addresses
     */
    public function index(): JsonResponse
    {
        $addresses = Address::where('user_id', auth()->id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Addresses retrieved successfully',
            'data' => AddressResource::collection($addresses)
        ]);
    }

    /**
     * Store a new address
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'region' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'details' => 'required|string|min:5',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = auth()->id();
        $isFirstAddress = Address::where('user_id', $userId)->count() === 0;

        $address = Address::create([
            'user_id' => $userId,
            'region' => $request->region,
            'city' => $request->city,
            'details' => $request->details,
            'is_default' => $isFirstAddress ? true : ($request->is_default ?? false),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'data' => new AddressResource($address)
        ], 201);
    }

    /**
     * Get single address
     */
    public function show(int $id): JsonResponse
    {
        $address = Address::where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Address retrieved successfully',
            'data' => new AddressResource($address)
        ]);
    }

    /**
     * Update address
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'region' => 'sometimes|required|string|max:100',
            'city' => 'sometimes|required|string|max:100',
            'details' => 'sometimes|required|string|min:5',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $address = Address::where('user_id', auth()->id())
            ->findOrFail($id);

        $address->update($request->only(['region', 'city', 'details', 'is_default']));

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => new AddressResource($address->fresh())
        ]);
    }

    /**
     * Delete address
     */
    public function destroy(int $id): JsonResponse
    {
        $address = Address::where('user_id', auth()->id())
            ->findOrFail($id);

        // $addressCount = Address::where('user_id', auth()->id())->count();

        // if ($addressCount === 1) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Cannot delete the only address. Please add another address first.'
        //     ], 422);
        // }

        if ($address->is_default) {
            $newDefault = Address::where('user_id', auth()->id())
                ->where('id', '!=', $id)
                ->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    /**
     * Set address as default
     */
    public function setDefault(int $id): JsonResponse
    {
        $address = Address::where('user_id', auth()->id())
            ->findOrFail($id);

        Address::where('user_id', auth()->id())
            ->where('id', '!=', $id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully',
            'data' => new AddressResource($address)
        ]);
    }

    /**
     * Get default address
     */
    public function getDefault(): JsonResponse
    {
        $address = Address::where('user_id', auth()->id())
            ->where('is_default', true)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => true,
                'message' => 'No default address found',
                'data' => null
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Default address retrieved successfully',
            'data' => new AddressResource($address)
        ]);
    }
}
