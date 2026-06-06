<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private const ORDER_STATUSES = [
        'all' => 'All',
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned',
    ];

    private const PAYMENT_STATUSES = [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
    ];

    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $orderNumber = $request->query('order_number');
        $customerName = $request->query('customer_name');
        $phone = $request->query('phone');
        $paymentStatus = $request->query('payment_status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $orders = Order::with(['user', 'address', 'paymentMethod', 'items.product', 'items.variant'])
            ->withCount('items')
            ->when($status !== 'all', fn($query) => $query->where('order_status', $status))
            ->when($orderNumber, fn($query) => $query->where('order_number', 'like', "%{$orderNumber}%"))
            ->when($customerName, fn($query) => $query->whereHas('user', fn($query) => $query->where('name', 'like', "%{$customerName}%")))
            ->when($phone, fn($query) => $query->whereHas('user', fn($query) => $query->where('phone', 'like', "%{$phone}%")))
            ->when($paymentStatus, fn($query) => $query->where('payment_status', $paymentStatus))
            ->when($dateFrom, fn($query) => $query->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($query) => $query->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Order::selectRaw('order_status, count(*) as count')
            ->groupBy('order_status')
            ->pluck('count', 'order_status')
            ->all();

        return view('orders.index', [
            'orders' => $orders,
            'statusCounts' => $statusCounts,
            'orderStatuses' => self::ORDER_STATUSES,
            'paymentStatuses' => self::PAYMENT_STATUSES,
            'filters' => [
                'status' => $status,
                'order_number' => $orderNumber,
                'customer_name' => $customerName,
                'phone' => $phone,
                'payment_status' => $paymentStatus,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['user', 'address', 'paymentMethod', 'items.product', 'items.variant']);

        return view('orders.show', [
            'order' => $order,
            'orderStatuses' => self::ORDER_STATUSES,
            'paymentStatuses' => self::PAYMENT_STATUSES,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'order_status' => ['required', 'in:pending,confirmed,processing,shipped,delivered,cancelled,returned'],
            'shipping_carrier' => ['nullable', 'string', 'max:255'],
            'admin_note' => ['nullable', 'string'],
        ]);

        if ($validated['order_status'] === 'shipped' && empty($validated['shipping_carrier']) && empty($order->shipping_carrier)) {
            return back()->withInput()->with('error', 'Shipping carrier is required when marking an order as shipped.');
        }

        if ($validated['order_status'] === 'shipped' && empty($order->tracking_number)) {
            $order->tracking_number = Order::generateTrackingNumber();
        }

        $order->order_status = $validated['order_status'];

        if (!empty($validated['shipping_carrier'])) {
            $order->shipping_carrier = $validated['shipping_carrier'];
        }

        if (isset($validated['admin_note'])) {
            $order->admin_note = $validated['admin_note'];
        }

        $order->save();

        return back()->with('success', 'Order status updated successfully.');
    }
}
