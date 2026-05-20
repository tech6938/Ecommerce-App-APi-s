@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">

            <div class="card shadow-sm border-0">

                <div class="card-header d-flex justify-content-between">
                    <h4>Edit Attribute</h4>

                    <a href="{{ route('attributes.index') }}"
                       class="btn btn-dark btn-sm">
                        Back
                    </a>
                </div>

                <form method="POST"
                      action="{{ route('attributes.update', $attribute->id) }}">

                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        <!-- Attribute Name -->
                        <div class="form-group mb-3">

                            <label class="fw-bold">Attribute Name</label>

                            <input type="text"
                                   name="name"
                                   value="{{ $attribute->name }}"
                                   class="form-control form-control-lg"
                                   placeholder="e.g. Color, Size, Storage">

                        </div>

                        <div class="form-group mb-3">
                            <label class="fw-bold">Display Type</label>
                            <select name="display_type" id="displayType" class="form-control form-control-lg">
                                <option value="chip" {{ ($attribute->display_type ?? 'chip') === 'chip' ? 'selected' : '' }}>Chip</option>
                                <option value="swatch" {{ ($attribute->display_type ?? 'chip') === 'swatch' ? 'selected' : '' }}>Swatch</option>
                            </select>
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

                        <div id="options-area">

                            {{-- EXISTING OPTIONS --}}
                            @foreach($attribute->options as $option)

                                <div class="row mb-2 option-row">

                                    <div class="col-md-5">
                                        <input type="text"
                                               name="options[{{ $option->id }}][value]"
                                               value="{{ $option->value }}"
                                               class="form-control">
                                    </div>

                                    <div class="col-md-5 swatch-column" style="display:none;">
                                        <input type="text"
                                               name="options[{{ $option->id }}][hex_code]"
                                               value="{{ $option->hex_code }}"
                                               class="form-control text-uppercase"
                                               placeholder="#000000">
                                    </div>

                                    <div class="col-md-2">

                                        <button type="button"
                                                class="btn btn-danger remove-option"
                                                data-id="{{ $option->id }}">
                                            X
                                        </button>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>

                    <div class="card-footer text-right">

                        <button class="btn btn-success px-4">
                            Update Attribute
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

let index = 0;

/* ADD NEW OPTION */
function addOption()
{
    let html = `
        <div class="row mb-2 option-row new-option">

            <div class="col-md-5">
                <input type="text"
                       name="new_options[][value]"
                       class="form-control"
                       placeholder="Enter option">
            </div>

            <div class="col-md-5 swatch-column" style="display:none;">
                <input type="text"
                       name="new_options[][hex_code]"
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

/* REMOVE ROW */
document.addEventListener('click', function(e){

    if(e.target.classList.contains('remove-option')){

        let row = e.target.closest('.option-row');

        // existing DB option delete mark
        if(e.target.dataset.id){
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'delete_options[]';
            input.value = e.target.dataset.id;
            document.querySelector('form').appendChild(input);
        }

        row.remove();
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
