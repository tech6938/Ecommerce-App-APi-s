<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Category;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::with('category')
            ->latest()
            ->get();

        return view('coupon.index', compact('coupons'));
    }

    public function create()
    {
        $categories = Category::where('status', 'active')
            ->latest()
            ->get();

        return view('coupon.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([

            'category_id' => 'required|exists:categories,id',

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'type' => 'required|in:fixed,percentage',

            'amount' =>
                'nullable|required_if:type,fixed|numeric',

            'percentage' =>
                'nullable|required_if:type,percentage|numeric|min:1|max:100',

            'start_from' => 'required|date',

            'end_on' =>
                'required|date|after_or_equal:start_from',

            'code' => 'required|unique:coupons,code',

            'status' => 'required|in:active,inactive',

        ]);

        $amount = $request->amount;

        $percentage = $request->percentage;

        // ✅ Fixed coupon
        if($request->type == 'fixed')
        {
            $percentage = 0;
        }

        // ✅ Percentage coupon
        if($request->type == 'percentage')
        {
            $amount = 0;
        }

        Coupon::create([

            'category_id' => $request->category_id,

            'title' => $request->title,

            'description' => $request->description,

            'type' => $request->type,

            'amount' => $amount,

            'percentage' => $percentage,

            'start_from' => $request->start_from,

            'end_on' => $request->end_on,

            'code' => $request->code,

            'status' => $request->status,

        ]);

        return redirect()
            ->route('coupon.index')
            ->with('success', 'Coupon created successfully');
    }

    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);

        $categories = Category::where('status', 'active')
            ->latest()
            ->get();

        return view('coupon.edit', compact('coupon', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([

            'category_id' => 'required|exists:categories,id',

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'type' => 'required|in:fixed,percentage',

            'amount' =>
                'nullable|required_if:type,fixed|numeric',

            'percentage' =>
                'nullable|required_if:type,percentage|numeric|min:1|max:100',

            'start_from' => 'required|date',

            'end_on' =>
                'required|date|after_or_equal:start_from',

            'code' =>
                'required|unique:coupons,code,' . $coupon->id,

            'status' => 'required|in:active,inactive',

        ]);

        $amount = $request->amount;

        $percentage = $request->percentage;

        // ✅ Fixed coupon
        if($request->type == 'fixed')
        {
            $percentage = 0;
        }

        // ✅ Percentage coupon
        if($request->type == 'percentage')
        {
            $amount = 0;
        }

        $coupon->update([

            'category_id' => $request->category_id,

            'title' => $request->title,

            'description' => $request->description,

            'type' => $request->type,

            'amount' => $amount,

            'percentage' => $percentage,

            'start_from' => $request->start_from,

            'end_on' => $request->end_on,

            'code' => $request->code,

            'status' => $request->status,

        ]);

        return redirect()
            ->route('coupon.index')
            ->with('success', 'Coupon updated successfully');
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);

        $coupon->delete();

        return redirect()
            ->back()
            ->with('success', 'Coupon deleted successfully');
    }

    public function changeStatus($id)
    {
        $coupon = Coupon::findOrFail($id);

        $coupon->status =
            $coupon->status == 'active'
            ? 'inactive'
            : 'active';

        $coupon->save();

        return redirect()
            ->back()
            ->with('success', 'Coupon status updated successfully');
    }
}