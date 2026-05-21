@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables-1.10.16/css/dataTables.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="main-content">

        <section class="section">

            <div class="d-flex justify-content-end pb-3">

                <a href="{{ route('cod-charges.create') }}" class="btn btn-primary text-white">
                    + Add COD Charge
                </a>

            </div>

            <div class="section-body">

                <x-sweet-alert />

                <div class="row">

                    <div class="col-12">

                        <div class="card">

                            <div class="card-header">
                                <h4>COD Charges List</h4>
                            </div>

                            <div class="card-body">

                                <div class="table-responsive">

                                    <table class="table table-striped" id="table-1">

                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Min Amount (FCFA)</th>
                                                <th>Max Amount (FCFA)</th>
                                                <th>Charge</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Sort Order</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            @forelse($charges as $data)
                                                <tr>

                                                    {{-- # --}}
                                                    <td>{{ $loop->iteration }}</td>

                                                    {{-- Min Amount --}}
                                                    <td>
                                                        {{ number_format($data->min_order_amount, 2) }}
                                                    </td>

                                                    {{-- Max Amount --}}
                                                    <td>
                                                        {{ $data->max_order_amount ? number_format($data->max_order_amount, 2) : '∞' }}
                                                    </td>

                                                    {{-- Charge --}}
                                                    <td>
                                                        @if ($data->charge_type == 'percentage')
                                                            <span class="badge badge-warning">
                                                                {{ $data->charge_amount }}%
                                                            </span>
                                                        @else
                                                            <span class="badge badge-info">
                                                                {{ number_format($data->charge_amount, 2) }} FCFA
                                                            </span>
                                                        @endif
                                                    </td>

                                                    {{-- Type --}}
                                                    <td>
                                                        <span
                                                            class="badge {{ $data->charge_type == 'percentage' ? 'badge-warning' : 'badge-info' }}">
                                                            {{ ucfirst($data->charge_type) }}
                                                        </span>
                                                    </td>

                                                    {{-- Status --}}
                                                    <td>
                                                        <span
                                                            class="badge {{ $data->is_active ? 'badge-success' : 'badge-danger' }}">
                                                            {{ $data->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </td>

                                                    {{-- Sort Order --}}
                                                    <td>{{ $data->sort_order }}</td>

                                                    {{-- Action --}}
                                                    <td class="d-flex">

                                                        {{-- TOGGLE STATUS --}}
                                                        <form id="toggleForm-{{ $data->id }}"
                                                            action="{{ route('cod-charges.toggle-status', $data->id) }}"
                                                            method="POST"
                                                            style="display: inline-block;">

                                                            @csrf
                                                            <button type="button"
                                                                class="btn btn-sm {{ $data->is_active ? 'btn-danger' : 'btn-success' }} mr-1"
                                                                onclick="confirmToggle('toggleForm-{{ $data->id }}', '{{ $data->is_active ? 'deactivate' : 'activate' }}', 'Charge Rule #{{ $data->id }}')">
                                                                {{ $data->is_active ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </form>

                                                        {{-- EDIT --}}
                                                        <a href="{{ route('cod-charges.edit', $data->id) }}"
                                                            class="btn btn-sm btn-primary mr-1">
                                                            Edit
                                                        </a>

                                                        {{-- DELETE --}}
                                                        <form id="deleteForm-{{ $data->id }}"
                                                            action="{{ route('cod-charges.destroy', $data->id) }}"
                                                            method="POST">

                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                onclick="confirmDelete('deleteForm-{{ $data->id }}', 'Charge Rule #{{ $data->id }}')">

                                                                Delete
                                                            </button>

                                                        </form>

                                                    </td>

                                                </tr>

                                            @empty

                                                <tr>
                                                    <td colspan="8" class="text-center">
                                                        No COD charges found
                                                    </td>
                                                </tr>
                                            @endforelse

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>

    </div>
@endsection

@section('js')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>

    <script>
        function confirmToggle(formId, action, itemName) {
            const isDeactivate = action === 'deactivate';

            Swal.fire({
                title: `${isDeactivate ? 'Deactivate' : 'Activate'} ${itemName}?`,
                text: `Are you sure you want to ${action} this COD charge rule?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: isDeactivate ? '#dc3545' : '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: `Yes, ${action}!`,
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }

        function confirmDelete(formId, itemName) {
            Swal.fire({
                title: `Are you sure you want to delete ${itemName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
@endsection
