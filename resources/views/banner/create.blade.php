@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">

    <section class="section">

        <div class="section-body">

            <div class="card shadow-sm border-0">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h4 class="mb-0">
                        Create Banner
                    </h4>

                    <a href="{{ route('banner.index') }}"
                       class="btn btn-dark btn-sm">
                        Back
                    </a>

                </div>

                <form method="POST"
                      action="{{ route('banner.store') }}"
                      enctype="multipart/form-data">

                    @csrf

                    <div class="card-body">

                        <div class="row">

                            <!-- Premium Banner Upload -->
                            <div class="col-md-12">

                                <div class="form-group">

                                    <label class="fw-bold mb-2 d-block">
                                        Banner Image
                                        <span class="text-danger">*</span>
                                    </label>

                                    <div class="border rounded p-4 bg-light">

                                        <div class="row align-items-center">

                                            <!-- Preview -->
                                            <div class="col-md-4 text-center">

                                                <img id="preview-image"
                                                     src="https://via.placeholder.com/350x180"
                                                     class="img-fluid rounded shadow-sm border"
                                                     style="width: 100%; max-width: 350px; height: 180px; object-fit: cover;">

                                            </div>

                                            <!-- Upload Content -->
                                            <div class="col-md-8">

                                                <div class="mb-3">

                                                    <label class="btn btn-primary px-4 py-2 mb-0">

                                                        <i data-feather="upload-cloud"
                                                           width="16"
                                                           height="16"></i>

                                                        Upload Banner

                                                        <input type="file"
                                                               name="image"
                                                               class="d-none"
                                                               id="image-input"
                                                               accept="image/*">

                                                    </label>

                                                </div>

                                                <small class="text-muted d-block mb-2">
                                                    Recommended Banner Size:
                                                    1920 × 700px
                                                </small>

                                                <small class="text-muted d-block mb-2">
                                                    Allowed Formats:
                                                    JPG, PNG, WEBP
                                                </small>

                                                <small class="text-muted d-block">
                                                    High-quality banners improve homepage appearance.
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

                            Create Banner

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