@extends('layout.dashboard-layout')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/bundles/datatables/datatables.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('assets/bundles/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">

    <style>
        .upload-box {
            border: 2px dashed #dcdcdc;
            padding: 30px;
            border-radius: 10px;
            cursor: pointer;
            position: relative;
            transition: 0.3s;
        }

        .upload-box:hover {
            background: #f8f9fa;
        }

        .upload-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,0.6);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            opacity: 0;
            border-radius: 10px;
            transition: 0.3s;
        }

        .upload-box:hover .upload-overlay {
            opacity: 1;
        }

        .remove-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff4d4f;
            color: #fff;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            text-align: center;
            line-height: 28px;
            font-size: 14px;
            cursor: pointer;
            z-index: 5;
        }

        .preview-img {
            max-width: 150px;
        }

        .preview-favicon {
            max-width: 80px;
        }
    </style>
@endsection

@section('content')
<div class="main-content">
    <section class="section">

        <div class="section-body">
            <x-sweet-alert />

            <form action="{{ route('system.setting.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">

                    {{-- Logo --}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Logo (Light Background)</h4>
                            </div>

                            <div class="card-body text-center">
                                <label class="upload-box w-100">
                                    <input type="file" name="logo" id="logoUpload" class="d-none"
                                           accept="image/png,image/jpeg,image/jpg"
                                           onchange="previewImage(this, 'logoPreview')">

                                    <img id="logoPreview"
                                         src="{{ isset($setting) && $setting->logo ? asset('systemsetting/'.$setting->logo) : asset('assets/img/placeholder.png') }}"
                                         class="preview-img">

                                    <div class="upload-overlay">
                                        Click or Drop Image
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Favicon --}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Favicon</h4>
                            </div>

                            <div class="card-body text-center">
                                <label class="upload-box w-100">
                                    <input type="file" name="favicon" id="faviconUpload" class="d-none"
                                           accept="image/png,image/jpeg,image/jpg"
                                           onchange="previewImage(this, 'faviconPreview')">

                                    <img id="faviconPreview"
                                         src="{{ isset($setting) && $setting->favicon ? asset('systemsetting/'.$setting->favicon) : asset('assets/img/placeholder.png') }}"
                                         class="preview-favicon">

                                    <div class="upload-overlay">
                                        Click or Drop Image
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Fields --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Company Information</h4>
                            </div>

                            <div class="card-body">

                                <div class="form-group">
                                    <label>Company Name</label>
                                    <input type="text" name="company_name" class="form-control"
                                           value="{{ $setting->company_name ?? '' }}">
                                </div>

                                <div class="form-group">
                                    <label>Company Number</label>
                                    <input type="text" name="company_number" class="form-control"
                                           value="{{ $setting->company_number ?? '' }}">
                                </div>

                                <div class="form-group">
                                    <label>Company Address</label>
                                    <textarea name="company_address" class="form-control">{{ $setting->company_address ?? '' }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Admin Name</label>
                                    <input type="text" name="admin_name" class="form-control"
                                           value="{{ $setting->admin_name ?? optional(auth()->user())->name }}">
                                </div>
                            </div>

                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary">
                                    Update Settings
                                </button>
                            </div>

                        </div>
                    </div>
                </div>

            </form>

        </div>
    </section>
</div>
@endsection

@section('js')
    <script src="{{ asset('assets/bundles/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/bundles/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/page/datatables.js') }}"></script>

    <script>
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection
