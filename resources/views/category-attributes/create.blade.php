@extends('layout.dashboard-layout')

@section('content')

<div class="main-content">

    <section class="section">

        <div class="section-body">

            <div class="card shadow-sm border-0">

                <div class="card-header">
                    <h4>Assign Attributes to Category</h4>
                </div>

                <form method="POST" action="{{ route('category.attributes.store') }}">
                    @csrf

                    <div class="card-body">

                        {{-- CATEGORY --}}
                        <div class="form-group mb-3">

                            <label>Select Category</label>

                            <select name="category_id"
                                    id="category_id"
                                    class="form-control form-control-lg">

                                <option value="">Select</option>

                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">
                                        {{ $cat->title }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <hr>

                        {{-- ATTRIBUTES --}}
                        <div id="attributesBox">

                            <h5>Select Attributes</h5>

                            @foreach($attributes as $attr)

                                <div class="border p-2 mb-2 d-flex justify-content-between">

                                    <label>{{ $attr->name }}</label>

                                    <input type="checkbox"
                                           name="attributes[]"
                                           value="{{ $attr->id }}">
                                </div>

                            @endforeach

                        </div>

                    </div>

                    <div class="card-footer text-right">
                        <button class="btn btn-success">
                            Save
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
const categoryData = @json($categories);

document.getElementById('category_id').addEventListener('change', function () {

    let categoryId = this.value;

    let selectedCategory = categoryData.find(cat => cat.id == categoryId);

    let assigned = selectedCategory ? selectedCategory.attributes.map(a => a.id) : [];

    document.querySelectorAll('input[name="attributes[]"]').forEach((checkbox) => {

        checkbox.checked = assigned.includes(parseInt(checkbox.value));

    });

});
</script>

@endsection