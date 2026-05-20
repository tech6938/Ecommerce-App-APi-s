@extends('layout.dashboard-layout')

@section('css')
<link rel="stylesheet" href="{{asset('assets/bundles/datatables/datatables.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/bundles/datatables-1.10.16/css/dataTables.bootstrap4.min.css')}}">
@endsection

@section('content')

<x-delete-modal id="deleteModal" name-id="name" form-id="deleteForm" title="Deleted" />

<div class="main-content">
    <section class="section">

        <div class="d-flex justify-content-end pb-3">
            <a href="{{ route('currency.create') }}" class="btn btn-primary text-white">
                + Add Currency
            </a>
        </div>

        <div class="section-body">

            <x-sweet-alert />

            <div class="card">

                <div class="card-header">
                    <h4>Currency List</h4>
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-striped" id="table-1">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Symbol</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($currencies as $data)

                                <tr>

                                    <td>{{ $loop->iteration }}</td>

                                    <td>{{ $data->currency_name }}</td>

                                    <td>
                                        <span class="badge badge-light">
                                            {{ $data->currency_code }}
                                        </span>
                                    </td>

                                    <td>{{ $data->symbol ?? '--' }}</td>

                                    {{-- STATUS (LIKE YOUR IMAGE) --}}
                                    <td>
                                        @if($data->status == 1)
                                            <span class="badge badge-success" style="padding:8px 12px;">
                                                Active
                                            </span>
                                        @else
                                            <span class="badge badge-secondary" style="padding:8px 12px;">
                                                Inactive
                                            </span>
                                        @endif
                                    </td>

                                    {{-- ACTION --}}
                                    <td>

                                        {{-- ACTIVATE / DEACTIVATE BUTTON --}}
                                        @if($data->status == 1)
                                            <button class="btn btn-warning btn-sm currency-status"
                                                    data-id="{{ $data->id }}"
                                                    data-status="0">
                                                Deactivate
                                            </button>
                                        @else
                                            <button class="btn btn-success btn-sm currency-status"
                                                    data-id="{{ $data->id }}"
                                                    data-status="1">
                                                Activate
                                            </button>
                                        @endif

                                        {{-- EDIT --}}
                                        <a href="{{ route('currency.edit', $data->id) }}"
                                           class="btn btn-primary btn-sm">
                                            <i data-feather="edit-2" width="14"></i>
                                        </a>

                                        {{-- DELETE --}}
                                        <form id="deleteForm-{{ $data->id }}"
                                              action="{{ route('currency.destroy', $data->id) }}"
                                              method="POST"
                                              style="display:inline-block;">

                                            @csrf
                                            @method('DELETE')

                                            <button type="button"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete('deleteForm-{{ $data->id }}', '{{ $data->currency_name }}')">

                                                <i data-feather="trash-2" width="14"></i>

                                            </button>

                                        </form>

                                    </td>

                                </tr>

                                @empty

                                <tr>
                                    <td colspan="6" class="text-center">
                                        No Currency Found
                                    </td>
                                </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </section>
</div>
@endsection

{{-- JS --}}
@section('js')

<script src="{{asset('assets/bundles/datatables/datatables.min.js')}}"></script>
<script src="{{asset('assets/bundles/datatables-1.10.16/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('assets/bundles/jquery-ui/jquery-ui.min.js')}}"></script>
<script src="{{asset('assets/js/page/datatables.js')}}"></script>

{{-- DELETE --}}
<script>
function confirmDelete(formId, name) {

    Swal.fire({
        title: `Delete ${name}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {

        if(result.isConfirmed){
            document.getElementById(formId).submit();
        }

    });
}
</script>

{{-- STATUS AJAX --}}
<script>
$(document).on('click', '.currency-status', function () {

    let id = $(this).data('id');
    let status = $(this).data('status');

    $.ajax({
        url: "{{ route('currency.status') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            id: id,
            status: status
        },
        success: function (response) {

            if (response.success) {

                Swal.fire({
                    icon: 'success',
                    title: response.message,
                    timer: 1200,
                    showConfirmButton: false
                });

                setTimeout(() => {
                    location.reload();
                }, 400);
            }

        },
        error: function () {
            Swal.fire({
                icon: 'error',
                title: 'Something went wrong'
            });
        }
    });

});
</script>

@endsection