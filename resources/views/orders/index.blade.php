@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Orders</h4>
                    </div>

                    <div class="card-body">
                        <div class="mb-4">
                            <ul class="nav nav-tabs">
                                @foreach ($orderStatuses as $key => $label)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $filters['status'] === $key ? 'active' : '' }}"
                                            href="{{ route('orders.index', array_merge(request()->except('page'), ['status' => $key])) }}">
                                            {{ $label }}
                                            @if ($key !== 'all')
                                                <span class="badge badge-primary ml-2">{{ $statusCounts[$key] ?? 0 }}</span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="card mb-4">
                            <div class="card-body">
                                <form method="GET" action="{{ route('orders.index') }}">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label>Order Number</label>
                                            <input type="text" name="order_number" value="{{ $filters['order_number'] }}"
                                                class="form-control" placeholder="Order Number">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label>Customer Name</label>
                                            <input type="text" name="customer_name"
                                                value="{{ $filters['customer_name'] }}" class="form-control"
                                                placeholder="Customer Name">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label>Phone Number</label>
                                            <input type="text" name="phone" value="{{ $filters['phone'] }}"
                                                class="form-control" placeholder="Phone Number">
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label>Payment Status</label>
                                            <select name="payment_status" class="form-control">
                                                <option value="">All</option>
                                                @foreach ($paymentStatuses as $key => $label)
                                                    <option value="{{ $key }}"
                                                        {{ $filters['payment_status'] === $key ? 'selected' : '' }}>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label>Order Status</label>
                                            <select name="status" class="form-control">
                                                @foreach ($orderStatuses as $key => $label)
                                                    <option value="{{ $key }}"
                                                        {{ $filters['status'] === $key ? 'selected' : '' }}>
                                                        {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label>Date From</label>
                                            <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label>Date To</label>
                                            <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-6 d-flex align-items-end mb-3 gap-2">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                            <a href="{{ route('orders.index') }}" class="btn btn-secondary">Reset</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Order Number</th>
                                        <th>Customer</th>
                                        <th>Phone</th>
                                        <th>Total Amount</th>
                                        <th>Payment Status</th>
                                        <th>Order Status</th>
                                        <th>Tracking No.</th>
                                        <th>Total Items</th>
                                        <th>Order Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                        <tr>
                                            <td>{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}
                                            </td>
                                            <td>{{ $order->order_number }}</td>
                                            <td>{{ $order->user?->name ?? 'Guest' }}</td>
                                            <td>{{ $order->user?->phone ?? '—' }}</td>
                                            <td>{{ number_format($order->total_amount, 2) }} FCFA</td>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : ($order->payment_status === 'refunded' ? 'secondary' : 'warning')) }}">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $order->status_badge }}">
                                                    {{ ucfirst($order->order_status) }}
                                                </span>
                                            </td>
                                            <td>{{ $order->tracking_number ?? '—' }}</td>
                                            <td>{{ $order->items_count }}</td>
                                            <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('orders.show', $order->id) }}"
                                                        class="btn btn-info btn-sm" title="View Order">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-warning btn-sm"
                                                        title="Change Status" data-order-id="{{ $order->id }}"
                                                        data-order-status="{{ $order->order_status }}"
                                                        data-shipping-carrier="{{ $order->shipping_carrier ?? '' }}"
                                                        onclick="openStatusModal(this)">
                                                        <i class="fas fa-exchange-alt"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center">No Orders Found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="statusUpdateForm" method="POST" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusModalLabel">Update Order Status</h5>
                        <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Order Status</label>
                            <select name="order_status" id="statusSelect" class="form-control">
                                @foreach ($orderStatuses as $key => $label)
                                    @if ($key !== 'all')
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shipping Carrier</label>
                            <input type="text" name="shipping_carrier" id="carrierInput" list="shipping_carriers"
                                class="form-control" placeholder="Enter or select carrier">
                            <datalist id="shipping_carriers">
                                <option value="DHL"></option>
                                <option value="UPS"></option>
                                <option value="FedEx"></option>
                                <option value="Local Carrier"></option>
                            </datalist>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function openStatusModal(button) {
            const orderId = button.dataset.orderId;
            const currentStatus = button.dataset.orderStatus;
            const shippingCarrier = button.dataset.shippingCarrier;

            const form = document.getElementById('statusUpdateForm');
            form.action = `{{ url('orders') }}/${orderId}/status`;
            document.getElementById('statusSelect').value = currentStatus;
            document.getElementById('carrierInput').value = shippingCarrier || '';
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: `{!! implode('<br>', $errors->all()) !!}`,
            });
        @endif
    </script>
@endsection
