@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">

            <div class="card shadow-sm border-0">

                <div class="card-header d-flex justify-content-between">
                    <h4>Create Attribute</h4>

                    <a href="{{ route('attributes.index') }}"
                       class="btn btn-dark btn-sm">
                        Back
                    </a>
                </div>

                <form method="POST"
                      action="{{ route('attributes.store') }}">

                    @csrf

                    <div class="card-body">

                        <!-- Attribute Name -->
                        <div class="form-group mb-3">
                            <label class="fw-bold">Attribute Name</label>

                            <input type="text"
                                   name="name"
                                   class="form-control form-control-lg"
                                   placeholder="e.g. Color, Size, Storage">
                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-bold">Display Type</label>
                            <select name="display_type" id="displayType" class="form-control form-control-lg">
                                <option value="chip">Chip</option>
                                <option value="swatch">Swatch</option>
                            </select>
                            <small class="text-muted">Use swatch for color-like selectors that need a hex code.</small>
                        </div>

                        <hr>

                        <!-- OPTIONS -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Options</h5>

                            <button type="button"
                                    class="btn btn-primary btn-sm"
                                    onclick="addOption()">
                                + Add Option
                            </button>
                        </div>

                        <div id="options-area"></div>

                    </div>

                    <div class="card-footer text-right">
                        <button class="btn btn-success px-4">
                            Save Attribute
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

function addOption()
{
    let html = `
        <div class="row mb-2 option-row">

            <div class="col-md-6">
                <input type="text"
                       name="options[][value]"
                       class="form-control"
                       placeholder="Enter option (Red, XL, 128GB)">
            </div>

            <div class="col-md-4 swatch-column" style="display:none;">
                <input type="text"
                       name="options[][hex_code]"
                       class="form-control text-uppercase"
                       placeholder="#000000">
            </div>

            <div class="col-md-2">
                <button type="button"
                        class="btn btn-danger remove-option">
                    X
                </button>
            </div>

        </div>
    `;

    document.getElementById('options-area')
        .insertAdjacentHTML('beforeend', html);

    syncSwatchFields();
}

document.addEventListener('click', function(e){

    if(e.target.classList.contains('remove-option')){
        e.target.closest('.option-row').remove();
    }

});

function syncSwatchFields()
{
    const isSwatch = document.getElementById('displayType').value === 'swatch';

    document.querySelectorAll('.swatch-column').forEach((column) => {
        column.style.display = isSwatch ? '' : 'none';
    });
}

document.getElementById('displayType').addEventListener('change', syncSwatchFields);
syncSwatchFields();

</script>
@endsection
