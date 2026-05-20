@extends('layout.dashboard-layout')

@section('content')

<div class="main-content">

    <section class="section">

        <div class="section-body">

            <div class="card">

                <div class="card-header">
                    <h4>Create Coupon</h4>
                </div>

                <form method="POST"
                      action="{{ route('coupon.store') }}">

                    @csrf

                    <div class="card-body">

                        {{-- Category --}}
                        <div class="form-group">

                            <label>Category</label>

                            <select name="category_id"
                                    class="form-control">

                                <option value="">

                                    Select Category

                                </option>

                                @foreach($categories as $category)

                                    <option value="{{ $category->id }}">

                                        {{ $category->title }}

                                    </option>

                                @endforeach

                            </select>

                            @error('category_id')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Title --}}
                        <div class="form-group">

                            <label>Coupon Title</label>

                            <input type="text"
                                   name="title"
                                   class="form-control">

                            @error('title')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Description --}}
                        <div class="form-group">

                            <label>Description</label>

                            <textarea name="description"
                                      class="form-control"
                                      rows="5"></textarea>

                            @error('description')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Type --}}
                        <div class="form-group">

                            <label>Coupon Type</label>

                            <select name="type"
                                    id="couponType"
                                    class="form-control">

                                <option value="fixed">

                                    Fixed

                                </option>

                                <option value="percentage">

                                    Percentage

                                </option>

                            </select>

                            @error('type')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Fixed Amount --}}
                        <div class="form-group"
                             id="amountField">

                            <label>Fixed Amount</label>

                            <input type="number"
                                   step="0.01"
                                   name="amount"
                                   class="form-control">

                            @error('amount')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Percentage --}}
                        <div class="form-group d-none"
                             id="percentageField">

                            <label>Percentage</label>

                            <input type="number"
                                   name="percentage"
                                   class="form-control">

                            @error('percentage')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Start From --}}
                        <div class="form-group">

                            <label>Start From</label>

                            <input type="date"
                                   name="start_from"
                                   class="form-control">

                            @error('start_from')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- End On --}}
                        <div class="form-group">

                            <label>End On</label>

                            <input type="date"
                                   name="end_on"
                                   class="form-control">

                            @error('end_on')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Code --}}
                        <div class="form-group">

                            <label>Coupon Code</label>

                            <input type="text"
                                   name="code"
                                   class="form-control">

                            @error('code')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Status --}}
                        <div class="form-group">

                            <label>Status</label>

                            <select name="status"
                                    class="form-control">

                                <option value="active">

                                    Active

                                </option>

                                <option value="inactive">

                                    Inactive

                                </option>

                            </select>

                            @error('status')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                    <div class="card-footer text-right">

                        <button type="submit"
                                class="btn btn-primary">

                            Create Coupon

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