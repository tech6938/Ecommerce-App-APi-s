@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">

        <section class="section">

            <div class="section-body">

                <div class="card">

                    <div class="card-header d-flex justify-content-between align-items-center">

                        <h4 class="mb-0">
                            Products
                        </h4>

                        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">

                            + Add Product

                        </a>

                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

                                <thead>

                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Brand</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th width="220">Actions</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    @forelse($products as $product)
                                        <tr>

                                            <td>
                                                {{ $loop->iteration }}
                                            </td>

                                            <!-- IMAGE -->
                                            <td>

                                                @if ($product->thumbnail)
                                                    <img src="{{ asset($product->thumbnail) }}" width="55"
                                                        height="55" style="object-fit:cover;border-radius:6px;">
                                                @else
                                                    <div
                                                        style="
                                                    width:55px;
                                                    height:55px;
                                                    background:#f1f1f1;
                                                    border-radius:6px;
                                                    display:flex;
                                                    align-items:center;
                                                    justify-content:center;
                                                    font-size:11px;
                                                    color:#999;
                                                ">
                                                        No Image
                                                    </div>
                                                @endif

                                            </td>

                                            <!-- TITLE -->
                                            <td>
                                                {{ $product->title }}
                                            </td>

                                            <!-- BRAND -->
                                            <td>
                                                {{ $product->brand ?? '—' }}
                                            </td>

                                            <!-- CATEGORY -->
                                            <td>
                                                {{ $product->category->title ?? '—' }}
                                            </td>

                                            <!-- STATUS -->
                                            <td>

                                                @if ($product->status == 'active')
                                                    <span class="badge badge-success">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                        Inactive
                                                    </span>
                                                @endif

                                            </td>

                                            <!-- ACTIONS -->
                                            <td>

                                                <div class="d-flex gap-2">

                                                    <!-- VIEW -->
                                                    <a href="{{ route('products.show', $product->id) }}"
                                                        class="btn btn-info btn-sm" title="View Product">
                                                        <i class="fas fa-eye"></i>
                                                    </a>

                                                    <!-- EDIT -->
                                                    <a href="{{ route('products.edit', $product->id) }}"
                                                        class="btn btn-warning btn-sm" title="Edit Product">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <!-- DELETE -->
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        title="Delete Product"
                                                        onclick="confirmDeleteProduct({{ $product->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>

                                                    <form id="delete-product-{{ $product->id }}"
                                                        action="{{ route('products.destroy', $product->id) }}"
                                                        method="POST" style="display:none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>

                                                    <!-- STATUS -->
                                                    <button type="button"
                                                        class="btn btn-sm {{ $product->status == 'active' ? 'btn-danger' : 'btn-success' }}"
                                                        onclick="changeStatus('{{ route('products.change.status', $product->id) }}')">

                                                        {{ $product->status == 'active' ? 'Deactivate' : 'Activate' }}

                                                    </button>

                                                </div>

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td colspan="7" class="text-center">

                                                No Products Found

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


@section('js')
    <script>
        function changeStatus(url) {
            Swal.fire({
                title: 'Are you sure?',
                text: "Product status will be changed",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'Cancel'
            }).then((result) => {

                if (result.isConfirmed) {
                    window.location.href = url;
                }

            });
        }

        function confirmDeleteProduct(productId) {
            Swal.fire({
                title: 'Delete Product?',
                text: "This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then((result) => {

                if (result.isConfirmed) {
                    document.getElementById(`delete-product-${productId}`).submit();
                }

            });
        }
    </script>
@endsection
