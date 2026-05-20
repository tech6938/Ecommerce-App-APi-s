@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">

        <section class="section">

            <button style="margin-left:90%; margin-bottom:10px;" class="btn btn-secondary" onclick="window.history.back()">
                &larr; Back
            </button>

            <div class="section-body">

                <div class="card">

                    <div class="card-header">
                        <h4>Product Detail</h4>
                    </div>

                    <div class="card-body">

                        <!-- ── PRODUCT INFO ──────────────────────────────────── -->
                        <div class="row">

                            {{-- LEFT: Images --}}
                            <div class="col-md-4">

                                {{-- Main (pehli) image bari --}}
                                @if ($product->thumbnail)

                                    <img id="mainImage" src="{{ asset($product->thumbnail) }}" class="img-fluid w-100 mb-2"
                                        style="height:280px;object-fit:cover;
                                                border-radius:10px;border:1px solid #eee;">

                                    {{-- Baqi images thumbnail row --}}
                                    @if ($product->images->count() > 1)
                                        <div class="d-flex flex-wrap" style="gap:8px;">

                                            @foreach ($product->images as $img)
                                                <img src="{{ asset($img->image) }}" width="68" height="68"
                                                    style="object-fit:cover;border-radius:6px;
                                                            cursor:pointer;border:2px solid transparent;"
                                                    onclick="document.getElementById('mainImage').src='{{ asset($img->image) }}'"
                                                    onmouseover="this.style.borderColor='#007bff'"
                                                    onmouseout="this.style.borderColor='transparent'">
                                            @endforeach

                                        </div>
                                    @endif
                                @else
                                    <div
                                        style="height:280px;background:#f5f5f5;border-radius:10px;
                                                display:flex;align-items:center;justify-content:center;
                                                color:#bbb;">
                                        No Image
                                    </div>
                                @endif

                            </div>

                            {{-- RIGHT: Info --}}
                            <div class="col-md-8">

                                <h3>{{ $product->title }}</h3>

                                <p><b>Brand:</b> {{ $product->brand ?? '—' }}</p>

                                <p><b>Category:</b> {{ $product->category->title ?? '—' }}</p>

                                <p><b>Description:</b> {{ $product->description ?? '—' }}</p>

                                <p>
                                    <b>Status:</b>
                                    <span class="badge bg-success">{{ $product->status }}</span>
                                </p>

                                {{-- Image count badge --}}
                                @if ($product->images->isNotEmpty())
                                    <p>
                                        <b>Total Images:</b>
                                        <span class="badge bg-secondary">
                                            {{ $product->images->count() }} photos
                                        </span>
                                    </p>
                                @endif

                            </div>

                        </div>

                        <hr>

                        <!-- ── VARIANTS ──────────────────────────────────────── -->
                        <h5>Variants
                            <span class="badge bg-secondary">
                                {{ $product->variants->count() }}
                            </span>
                        </h5>

                        <table class="table table-bordered align-middle">

                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>SKU</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Images</th>
                                    <th>Attributes</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($product->variants as $variant)
                                    <tr>

                                        <td>{{ $loop->iteration }}</td>

                                        <td>{{ $variant->sku ?? '—' }}</td>

                                        <td>Rs. {{ number_format($variant->price) }}</td>

                                        <td>
                                            <span class="badge {{ $variant->stock > 0 ? 'bg-success' : 'bg-danger' }}">
                                                {{ $variant->stock }}
                                            </span>
                                        </td>

                                        {{-- Variant ki multiple images --}}
                                        <td>
                                            @if ($variant->images->isNotEmpty())
                                                <div class="d-flex flex-wrap" style="gap:6px;">

                                                    @foreach ($variant->images as $img)
                                                        <img src="{{ asset($img->image) }}" width="55" height="55"
                                                            style="object-fit:cover;border-radius:6px;
                                                                    border:1px solid #ddd;cursor:pointer;"
                                                            data-bs-toggle="tooltip" title="{{ $img->image }}">
                                                    @endforeach

                                                </div>
                                            @else
                                                <span class="text-muted" style="font-size:12px;">No image</span>
                                            @endif
                                        </td>

                                        {{-- Attribute options --}}
                                        <td>
                                            @foreach ($variant->options as $opt)
                                                <span class="badge bg-primary me-1">
                                                    {{ $opt->attributeOption->attribute->name ?? '' }}:
                                                    {{ $opt->attributeOption->value ?? '' }}
                                                </span>
                                            @endforeach
                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </section>

    </div>
@endsection
