@extends('layout.dashboard-layout')

@section('content')

<div class="main-content">

    <section class="section">
        <div class="section-body">

            <div class="d-flex justify-content-end mb-3">
                <a href="{{ route('attributes.create') }}"
                   class="btn btn-primary">
                    + Add Attribute
                </a>
            </div>

            <div class="card shadow-sm border-0">

                <div class="card-header">
                    <h4>Attributes</h4>
                </div>

                <div class="card-body table-responsive">

                    <table class="table table-striped">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Options</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($attributes as $attr)

                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td>{{ $attr->name }}</td>

                                    <td>
                                        <span class="badge bg-info text-dark">
                                            {{ ucfirst($attr->display_type ?? 'chip') }}
                                        </span>
                                    </td>

                                    <td>
                                        @foreach($attr->options as $opt)
                                            <span class="badge bg-primary">
                                                {{ $opt->value }}
                                            </span>
                                            @if($opt->hex_code)
                                                <span class="badge border text-dark" style="background-color: {{ $opt->hex_code }};">
                                                    {{ $opt->hex_code }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </td>

                                    <td>

                                        <a href="{{ route('attributes.edit', $attr->id) }}"
                                           class="btn btn-sm btn-primary">
                                            Edit
                                        </a>

                                        <form action="{{ route('attributes.destroy', $attr->id) }}"
                                              method="POST"
                                              style="display:inline-block">

                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-sm btn-danger">
                                                Delete
                                            </button>

                                        </form>

                                    </td>
                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5" class="text-center">
                                        No Attributes Found
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>
    </section>

</div>

@endsection
