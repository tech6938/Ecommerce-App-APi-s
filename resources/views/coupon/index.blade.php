@extends('layout.dashboard-layout')

@section('css')
<link rel="stylesheet" href="{{asset('assets/bundles/datatables/datatables.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/bundles/datatables-1.10.16/css/dataTables.bootstrap4.min.css')}}">
@endsection

@section('content')

<div class="main-content">

    <section class="section">

        <div class="d-flex justify-content-end pb-3">

            <a href="{{ route('coupon.create') }}" class="btn btn-primary text-white">
                + Add Coupon
            </a>

        </div>

        <div class="section-body">

            <x-sweet-alert />

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-header">
                            <h4>Coupons List</h4>
                        </div>

                        <div class="card-body">

                            <div class="table-responsive">

                                <table class="table table-striped" id="table-1">

                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Category</th>
                                            <th>Title</th>
                                            <th>Discount</th>
                                            <th>Code</th>
                                            <th>Start</th>
                                            <th>End</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @forelse($coupons as $data)

                                        <tr>

                                            {{-- # --}}
                                            <td>{{ $loop->iteration }}</td>

                                            {{-- Category --}}
                                            <td>
                                                {{ $data->category->title ?? '--' }}
                                            </td>

                                            {{-- Title --}}
                                            <td>
                                                {{ $data->title }}
                                            </td>

                                            {{-- Discount (FIXED / PERCENTAGE) --}}
                                            <td>

                                                @if($data->type == 'fixed')

                                                    <span class="badge badge-success">
                                                        Fixed: {{ $data->amount }}
                                                    </span>

                                                @elseif($data->type == 'percentage')

                                                    <span class="badge badge-warning">
                                                        {{ $data->percentage }}%
                                                    </span>

                                                @else

                                                    <span class="text-muted">--</span>

                                                @endif

                                            </td>

                                            {{-- Code --}}
                                            <td>
                                                <span class="badge badge-dark">
                                                    {{ $data->code }}
                                                </span>
                                            </td>

                                            {{-- Start --}}
                                            <td>{{ $data->start_from }}</td>

                                            {{-- End --}}
                                            <td>{{ $data->end_on }}</td>

                                            {{-- Status --}}
                                            <td>

                                                <span class="badge {{ $data->status == 'active' ? 'badge-success' : 'badge-danger' }}">
                                                    {{ ucfirst($data->status) }}
                                                </span>

                                            </td>

                                            {{-- Action --}}
                                            <td class="d-flex">

                                                {{-- STATUS TOGGLE --}}
                                                <a href="{{ route('coupon.change.status', $data->id) }}"
                                                   class="btn btn-sm {{ $data->status == 'active' ? 'btn-danger' : 'btn-success' }} mr-1">

                                                    {{ $data->status == 'active' ? 'Inactive' : 'Active' }}

                                                </a>

                                                {{-- EDIT --}}
                                                <a href="{{ route('coupon.edit', $data->id) }}"
                                                   class="btn btn-sm btn-primary mr-1">

                                                    Edit

                                                </a>

                                                {{-- DELETE --}}
                                                <form id="deleteForm-{{ $data->id }}"
                                                      action="{{ route('coupon.destroy', $data->id) }}"
                                                      method="POST">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="button"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="confirmDelete('deleteForm-{{ $data->id }}', '{{ $data->title }}')">

                                                        Delete
                                                    </button>

                                                </form>

                                            </td>

                                        </tr>

                                        @empty

                                        <tr>
                                            <td colspan="9" class="text-center">
                                                No coupons found
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
<script src="{{asset('assets/bundles/datatables-1.10.16/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('assets/bundles/jquery-ui/jquery-ui.min.js')}}"></script>
<script src="{{asset('assets/js/page/datatables.js')}}"></script>

<script>
function confirmDelete(formId, itemName)
{
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