@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">

    <section class="section">

        <div class="section-body">

            <div class="card shadow-sm border-0">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h4 class="mb-0">
                        Edit Banner
                    </h4>

                    <a href="{{ route('banner.index') }}"
                       class="btn btn-dark btn-sm">
                        Back
                    </a>

                </div>

                <form method="POST"
                      action="{{ route('banner.update', $banner->id) }}"
                      enctype="multipart/form-data">

                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        <div class="row">

                            <!-- Premium Banner Upload -->
                            <div class="col-md-12">

                                <div class="form-group">

                                    <label class="fw-bold mb-2 d-block">
                                        Banner Image
                                    </label>

                                    <div class="border rounded p-4 bg-light">

                                        <div class="row align-items-center">

                                            <!-- Current Preview -->
                                            <div class="col-md-4 text-center">

                                                <img id="preview-image"
                                                     src="{{ asset($banner->image) }}"
                                                     class="img-fluid rounded shadow-sm border"
                                                     style="width: 100%; max-width: 350px; height: 180px; object-fit: cover;">

                                            </div>

                                            <!-- Upload Area -->
                                            <div class="col-md-8">

                                                <div class="mb-3">

                                                    <label class="btn btn-primary px-4 py-2 mb-0">

                                                        <i data-feather="upload-cloud"
                                                           width="16"
                                                           height="16"></i>

                                                        Upload New Banner

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
                                                    Uploading a new image will replace the current banner.
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

                                        <option value="active"
                                            {{ $banner->status == 'active' ? 'selected' : '' }}>
                                            Active
                                        </option>

                                        <option value="inactive"
                                            {{ $banner->status == 'inactive' ? 'selected' : '' }}>
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

                            Update Banner

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