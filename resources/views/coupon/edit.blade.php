@extends('layout.dashboard-layout')

@section('content')

<div class="main-content">

    <section class="section">

        <div class="section-body">

            <div class="card">

                <div class="card-header">
                    <h4>Update Coupon</h4>
                </div>

                <form method="POST"
                      action="{{ route('coupon.update', $coupon->id) }}">

                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        {{-- Category --}}
                        <div class="form-group">

                            <label>Category</label>

                            <select name="category_id"
                                    class="form-control">

                                @foreach($categories as $category)

                                    <option value="{{ $category->id }}"
                                        {{ $coupon->category_id == $category->id ? 'selected' : '' }}>

                                        {{ $category->title }}

                                    </option>

                                @endforeach

                            </select>

                        </div>

                        {{-- Title --}}
                        <div class="form-group">

                            <label>Coupon Title</label>

                            <input type="text"
                                   name="title"
                                   value="{{ $coupon->title }}"
                                   class="form-control">

                        </div>

                        {{-- Description --}}
                        <div class="form-group">

                            <label>Description</label>

                            <textarea name="description"
                                      class="form-control"
                                      rows="5">{{ $coupon->description }}</textarea>

                        </div>

                        {{-- Type --}}
                        <div class="form-group">

                            <label>Coupon Type</label>

                            <select name="type"
                                    id="couponType"
                                    class="form-control">

                                <option value="fixed"
                                    {{ $coupon->type == 'fixed' ? 'selected' : '' }}>

                                    Fixed

                                </option>

                                <option value="percentage"
                                    {{ $coupon->type == 'percentage' ? 'selected' : '' }}>

                                    Percentage

                                </option>

                            </select>

                        </div>

                        {{-- Amount --}}
                        <div class="form-group
                            {{ $coupon->type == 'percentage' ? 'd-none' : '' }}"
                            id="amountField">

                            <label>Fixed Amount</label>

                            <input type="number"
                                   step="0.01"
                                   name="amount"
                                   value="{{ $coupon->amount }}"
                                   class="form-control">

                        </div>

                        {{-- Percentage --}}
                        <div class="form-group
                            {{ $coupon->type == 'fixed' ? 'd-none' : '' }}"
                            id="percentageField">

                            <label>Percentage</label>

                            <input type="number"
                                   name="percentage"
                                   value="{{ $coupon->percentage }}"
                                   class="form-control">

                        </div>

                        {{-- Start --}}
                        <div class="form-group">

                            <label>Start From</label>

                            <input type="date"
                                   name="start_from"
                                   value="{{ $coupon->start_from }}"
                                   class="form-control">

                        </div>

                        {{-- End --}}
                        <div class="form-group">

                            <label>End On</label>

                            <input type="date"
                                   name="end_on"
                                   value="{{ $coupon->end_on }}"
                                   class="form-control">

                        </div>

                        {{-- Code --}}
                        <div class="form-group">

                            <label>Coupon Code</label>

                            <input type="text"
                                   name="code"
                                   value="{{ $coupon->code }}"
                                   class="form-control">

                        </div>

                        {{-- Status --}}
                        <div class="form-group">

                            <label>Status</label>

                            <select name="status"
                                    class="form-control">

                                <option value="active"
                                    {{ $coupon->status == 'active' ? 'selected' : '' }}>

                                    Active

                                </option>

                                <option value="inactive"
                                    {{ $coupon->status == 'inactive' ? 'selected' : '' }}>

                                    Inactive

                                </option>

                            </select>

                        </div>

                    </div>

                    <div class="card-footer text-right">

                        <button type="submit"
                                class="btn btn-primary">

                            Update Coupon

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

$(document).ready(function () {

    toggleFields();

    $('#couponType').change(function () {

        toggleFields();

    });

    function toggleFields()
    {
        let type = $('#couponType').val();

        if(type == 'fixed')
        {
            $('#amountField').removeClass('d-none');
            $('#percentageField').addClass('d-none');
        }
        else
        {
            $('#amountField').addClass('d-none');
            $('#percentageField').removeClass('d-none');
        }
    }

});

</script>

@endsection