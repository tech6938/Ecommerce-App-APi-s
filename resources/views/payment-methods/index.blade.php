@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <x-sweet-alert />
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Payment Methods</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($methods as $method)
                                        <tr>
                                            <td>{{ $method->id }}</td>
                                            <td>{{ $method->name }}</td>
                                            <td><code>{{ $method->code }}</code></td>
                                            <td>
                                                @if($method->type == 'cod')
                                                    <span class="badge badge-info">COD</span>
                                                @else
                                                    <span class="badge badge-success">Online</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $method->is_active ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $method->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.payment-methods.edit', $method->id) }}" class="btn btn-sm btn-primary">
                                                    Configure
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
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
