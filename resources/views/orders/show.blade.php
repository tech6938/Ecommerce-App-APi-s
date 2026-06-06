@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Order Details</h4>
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Back to Orders</a>
                    </div>

                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Order Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Order Number</th>
                                        <td>{{ $order->order_number }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td><span class="badge badge-{{ $order->status_badge }}">{{ ucfirst($order->order_status) }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Payment Status</th>
                                        <td><span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : ($order->payment_status === 'refunded' ? 'secondary' : 'warning')) }}">{{ ucfirst($order->payment_status) }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Tracking Number</th>
                                        <td>{{ $order->tracking_number ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Carrier</th>
                                        <td>{{ $order->shipping_carrier ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Order Date</th>
                                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h5>Customer Information</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $order->user?->name ?? 'Guest' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $order->user?->email ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td>{{ $order->user?->phone ?? '—' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Shipping Address</h5>
                                <div class="border rounded p-3">
                                    <p class="mb-1"><strong>Region:</strong> {{ $order->address?->region ?? '—' }}</p>
                                    <p class="mb-1"><strong>City:</strong> {{ $order->address?->city ?? '—' }}</p>
                                    <p class="mb-0"><strong>Details:</strong> {{ $order->address?->details ?? '—' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Payment Details</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Method</th>
                                        <td>{{ $order->paymentMethod?->name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <td>{{ $order->payment_transaction_id ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Subtotal</th>
                                        <td>{{ number_format($order->subtotal, 2) }} FCFA</td>
                                    </tr>
                                    <tr>
                                        <th>Discount</th>
                                        <td>{{ number_format($order->discount_amount, 2) }} FCFA</td>
                                    </tr>
                                    <tr>
                                        <th>Shipping</th>
                                        <td>{{ number_format($order->shipping_charge, 2) }} FCFA</td>
                                    </tr>
                                    <tr>
                                        <th>COD Charge</th>
                                        <td>{{ number_format($order->cod_charge, 2) }} FCFA</td>
                                    </tr>
                                    @if($order->coupon_code)
                                        <tr>
                                            <th>Coupon</th>
                                            <td>{{ $order->coupon_code }} ({{ number_format($order->coupon_discount, 2) }} FCFA)</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>Total</th>
                                        <td><strong>{{ number_format($order->total_amount, 2) }} FCFA</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Order Notes</h5>
                                <div class="border rounded p-3">
                                    <p class="mb-1"><strong>Customer Note:</strong> {{ $order->customer_note ?? '—' }}</p>
                                    <p class="mb-0"><strong>Admin Note:</strong> {{ $order->admin_note ?? '—' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h5>Order Items</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Variant</th>
                                                <th>Options</th>
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th>Total Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($order->items as $item)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $item->product_name }}</td>
                                                    <td>{{ $item->variant_name ?? '—' }}</td>
                                                    <td>
                                                        @if(!empty($item->selected_options))
                                                            <ul class="mb-0 ps-3">
                                                                @foreach($item->selected_options as $option)
                                                                    <li>{{ $option['name'] ?? $option['label'] ?? 'Option' }}: {{ $option['value'] ?? $option['option'] ?? '—' }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>{{ number_format($item->unit_price, 2) }} FCFA</td>
                                                    <td>{{ number_format($item->total_price, 2) }} FCFA</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5>Update Status</h5>
                                <form method="POST" action="{{ route('orders.update.status', $order->id) }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Order Status</label>
                                            <select name="order_status" class="form-control">
                                                @foreach($orderStatuses as $key => $label)
                                                    @if($key !== 'all')
                                                        <option value="{{ $key }}" {{ $order->order_status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Shipping Carrier</label>
                                            <input type="text" name="shipping_carrier" list="shipping_carriers" value="{{ old('shipping_carrier', $order->shipping_carrier) }}" class="form-control" placeholder="DHL, UPS, FedEx">
                                            <datalist id="shipping_carriers">
                                                <option value="DHL"></option>
                                                <option value="UPS"></option>
                                                <option value="FedEx"></option>
                                                <option value="Local Carrier"></option>
                                            </datalist>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Admin Note</label>
                                            <input type="text" name="admin_note" value="{{ old('admin_note', $order->admin_note) }}" class="form-control" placeholder="Admin note">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('js')
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
            });
        @endif
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Oops',
                text: '{{ session('error') }}',
            });
        @endif
    </script>
@endsection
