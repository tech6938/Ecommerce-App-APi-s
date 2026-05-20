    @extends('layout.dashboard-layout')

    @section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Create Volunteer</h4>
                    </div>

                    <form method="POST" action="{{ route('volunteer.store') }}">
                        @csrf
                        @method('POST')
                        <div class="card-body">

                            <!-- Name -->
                            <div class="form-group">
                                <label class="fw-bold">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control">
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label class="fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control">
                                @error('email')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="form-group">
                                <label class="fw-bold">Phone</label>
                                <input type="text" name="phone" class="form-control">
                                @error('phone')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <div class="card-footer text-right">
                            <button class="btn btn-primary mr-1" type="submit">Submit</button>
                        </div>

                    </form>
                </div>
            </div>
        </section>
    </div>
    @endsection
