@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">

            <div class="card">
                <div class="card-header">
                    <h4>Edit Currency</h4>
                </div>

                <form method="POST" action="{{ route('currency.update', $currency->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">

                        <div class="form-group">
                            <label>Currency Name</label>
                            <input type="text" name="currency_name" value="{{ $currency->currency_name }}" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Currency Code</label>
                            <input type="text" name="currency_code" value="{{ $currency->currency_code }}" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Symbol</label>
                            <input type="text" name="symbol" value="{{ $currency->symbol }}" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Symbol Position</label>
                            <select name="symbol_position" class="form-control">
                                <option value="before" {{ ($currency->symbol_position ?? 'before') == 'before' ? 'selected' : '' }}>
                                    Before Amount (e.g., $100)
                                </option>
                                <option value="after" {{ ($currency->symbol_position ?? 'before') == 'after' ? 'selected' : '' }}>
                                    After Amount (e.g., 100 F CFA)
                                </option>
                            </select>
                            <small class="form-text text-muted">Choose where the currency symbol should appear</small>
                        </div>

                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>

                </form>

            </div>

        </div>
    </section>
</div>
@endsection