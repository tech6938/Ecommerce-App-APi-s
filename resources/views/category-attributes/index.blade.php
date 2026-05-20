@extends('layout.dashboard-layout')

@section('content')

<div class="main-content">

<section class="section">

<div class="section-body">

<div class="d-flex justify-content-between mb-3">

    <h4>Category Attributes List</h4>

    <a href="{{ route('category.attributes.create') }}"
       class="btn btn-primary">
        + Assign Attributes
    </a>

</div>

<div class="card">

<div class="card-body">

@foreach($categories as $cat)

    <div class="border p-2 mb-2">

        <strong>{{ $cat->title }}</strong>

        <div class="mt-1">

            @forelse($cat->attributes as $attr)
                <span class="badge bg-primary">
                    {{ $attr->name }}
                </span>
            @empty
                <span class="text-muted">No attributes</span>
            @endforelse

        </div>

    </div>

@endforeach

</div>

</div>

</div>

</section>

</div>

@endsection