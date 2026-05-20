@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">

    <section class="section">

        <div class="section-body">

            <div class="card shadow-sm border-0">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h4 class="mb-0">
                        Create Category
                    </h4>

                    <a href="{{ route('category.index') }}"
                       class="btn btn-dark btn-sm">
                        Back
                    </a>

                </div>

                <form method="POST"
                      action="{{ route('category.store') }}"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="card-body">

                        <div class="row">

                            <!-- Title -->
                            <div class="col-md-12">

                                <div class="form-group">

                                    <label class="fw-bold mb-2">
                                        Category Title
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input type="text"
                                           name="title"
                                           class="form-control form-control-lg"
                                           placeholder="Enter category title"
                                           value="{{ old('title') }}">

                                    @error('title')
                                        <div class="text-danger mt-1">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                </div>

                            </div>

                            <!-- Premium Image Upload -->
                            <div class="col-md-12">

                                <div class="form-group">

                                    <label class="fw-bold mb-2 d-block">
                                        Category Image
                                        <span class="text-danger">*</span>
                                    </label>

                                    <div class="border rounded p-4 bg-light">

                                        <div class="row align-items-center">

                                            <!-- Image Preview -->
                                            <div class="col-md-3 text-center">

                                                <img id="preview-image"
                                                     src="https://via.placeholder.com/170"
                                                     class="img-fluid rounded shadow-sm border"
                                                     style="width: 170px; height: 170px; object-fit: cover;">

                                            </div>

                                            <!-- Upload Area -->
                                            <div class="col-md-9">

                                                <div class="mb-3">

                                                    <label class="btn btn-primary px-4 py-2 mb-0">

                                                        <i data-feather="upload-cloud"
                                                           width="16"
                                                           height="16"></i>

                                                        Upload Image

                                                        <input type="file"
                                                               name="image"
                                                               class="d-none"
                                                               id="image-input"
                                                               accept="image/*">

                                                    </label>

                                                </div>

                                                <small class="text-muted d-block mb-2">
                                                    Recommended Size:
                                                    1200 × 600px
                                                </small>

                                                <small class="text-muted d-block">
                                                    Allowed Formats:
                                                    JPG, PNG, WEBP
                                                </small>

                                                @error('image')
                                                    <div class="text-danger mt-2">
                                                        {{ $message }}
                                                    </div>
                                                @enderror

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <!-- Status -->
                            <div class="col-md-12">

                                <div class="form-group">

                                    <label class="fw-bold mb-2">
                                        Status
                                    </label>

                                    <select name="status"
                                            class="form-control form-control-lg">

                                        <option value="active">
                                            Active
                                        </option>

                                        <option value="inactive">
                                            Inactive
                                        </option>

                                    </select>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="card-footer text-right bg-white border-0 pb-4 pr-4">

                        <button class="btn btn-primary px-5"
                                type="submit">

                            <i data-feather="save"
                               width="16"
                               height="16"></i>

                            Create Category

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </section>

</div>
@endsection

@section('js')

<script>

document
    .getElementById('image-input')
    .addEventListener('change', function(e) {

        const file = e.target.files[0];

        if (file) {

            const reader = new FileReader();

            reader.onload = function(event) {

                document
                    .getElementById('preview-image')
                    .src = event.target.result;

            }

            reader.readAsDataURL(file);

        }

    });

</script>

@endsection