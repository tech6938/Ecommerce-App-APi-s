@extends('layout.dashboard-layout')

@section('css')
<link rel="stylesheet" href="{{asset('assets/bundles/datatables/datatables.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css')}}">
@endsection

@section('content')

<x-delete-modal id="deleteModal" name-id="name" form-id="deleteForm" title="Deleted" />

<div class="main-content">
    <section class="section">

        <div class="d-flex justify-content-end pb-3">
            <a href="{{ route('category.create') }}"
               class="btn btn-primary text-white">
                + Add New
            </a>
        </div>

        <div class="section-body">

            <x-sweet-alert />

            <div class="row">
                <div class="col-12">

                    <div class="card">

                        <div class="card-header">
                            <h4>Category List</h4>
                        </div>

                        <div class="card-body">

                            <div class="table-responsive">

                                <table class="table table-striped" id="table-1">

                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Category Image</th>
                                            <th>Title</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @forelse($categories as $data)

                                        <tr>

                                            <td>{{ $loop->iteration }}</td>

                                            <td>
                                                <img src="{{ asset($data->image) }}"
                                                     width="100">
                                            </td>

                                            <td>{{ $data->title }}</td>

                                            <td>

                                                @if($data->status == 'active')

                                                    <span class="badge badge-success">
                                                        Active
                                                    </span>

                                                @else

                                                    <span class="badge badge-danger">
                                                        Inactive
                                                    </span>

                                                @endif

                                            </td>

                                            <td>

                                                <a href="{{ route('category.status', $data->id) }}"
                                                   class="btn btn-sm btn-warning">

                                                    @if($data->status == 'active')
                                                        Deactivate
                                                    @else
                                                        Activate
                                                    @endif

                                                </a>

                                                <a href="{{ route('category.edit', $data->id) }}"
                                                   class="btn btn-sm btn-primary">

                                                    <i data-feather="edit-2"
                                                       width="14"
                                                       height="14"></i>

                                                </a>

                                                <form id="deleteForm-{{ $data->id }}"
                                                      action="{{ route('category.destroy', $data->id) }}"
                                                      method="POST"
                                                      style="display:inline-block;">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="confirmDelete('deleteForm-{{ $data->id }}', '{{ $data->title }}')">

                                                        <i data-feather="trash-2"
                                                           width="14"
                                                           height="14"></i>

                                                    </button>

                                                </form>

                                            </td>

                                        </tr>

                                        @empty

                                        <tr>
                                            <td colspan="5" class="text-center">
                                                No categories found
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

<script src="{{asset('assets/bundles/datatables/datatables.min.js')}}"></script>
<script src="{{asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('assets/bundles/jquery-ui/jquery-ui.min.js')}}"></script>
<script src="{{asset('assets/js/page/datatables.js')}}"></script>

<script>
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