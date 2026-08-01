@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4>Create Currency</h4>
                </div>

                <form method="POST" action="{{ route('currency.store') }}">
                    @csrf

                    <div class="card-body">

                        <!-- Currency Name -->
                        <div class="form-group">
                            <label>Currency Name <span class="text-danger">*</span></label>
                            <input type="text" name="currency_name" class="form-control" placeholder="Pakistani Rupee">
                            @error('currency_name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Currency Code -->
                        <div class="form-group">
                            <label>Currency Code <span class="text-danger">*</span></label>
                            <input type="text" name="currency_code" class="form-control" placeholder="PKR, USD">
                            @error('currency_code')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Symbol -->
                        <div class="form-group">
                            <label>Symbol</label>
                            <input type="text" name="symbol" class="form-control" placeholder="₨, $, F CFA">
                            @error('symbol')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Symbol Position -->
                        <div class="form-group">
                            <label>Symbol Position <span class="text-danger">*</span></label>
                            <select name="symbol_position" class="form-control">
                                <option value="before">Before Amount (e.g., $100)</option>
                                <option value="after">After Amount (e.g., 100 F CFA)</option>
                            </select>
                            <small class="form-text text-muted">Choose where the currency symbol should appear</small>
                            @error('symbol_position')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>

                </form>

            </div>

        </div>
    </section>
</div>
@endsection