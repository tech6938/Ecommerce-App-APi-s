@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Update Volunteer</h4>
                </div>

                <form method="POST"
                      action="{{ route('volunteer.update', $volunteer->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        <!-- Name -->
                        <div class="form-group">
                            <label class="fw-bold">
                                Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $volunteer->name) }}"
                                   required>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label class="fw-bold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="{{ old('email', $volunteer->email) }}"
                                   required>
                        </div>

                        <!-- Phone -->
                        <div class="form-group">
                            <label class="fw-bold">Phone</label>
                            <input type="text"
                                   name="phone"
                                   class="form-control"
                                   value="{{ old('phone', $volunteer->phone) }}">
                        </div>

                    </div>

                    <div class="card-footer text-right">
                        <button class="btn btn-primary" type="submit">
                            Update
                        </button>
                        <a href="{{ route('volunteer.index') }}"
                           class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>
@endsection
