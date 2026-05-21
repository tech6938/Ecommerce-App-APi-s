@extends('layout.dashboard-layout')

@section('content')

<div class="main-content">

    <section class="section">

        <div class="section-body">

            <div class="card">

                <div class="card-header">
                    <h4>Create COD Charge</h4>
                </div>

                <form method="POST"
                      action="{{ route('cod-charges.store') }}">

                    @csrf

                    <div class="card-body">

                        {{-- Minimum Order Amount --}}
                        <div class="form-group">

                            <label>Minimum Order Amount (FCFA) <span class="text-danger">*</span></label>

                            <input type="number"
                                   step="0.01"
                                   name="min_order_amount"
                                   class="form-control"
                                   placeholder="0.00">

                            @error('min_order_amount')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                            <small class="text-muted">Minimum order amount to apply this charge</small>

                        </div>

                        {{-- Maximum Order Amount --}}
                        <div class="form-group">

                            <label>Maximum Order Amount (FCFA)</label>

                            <input type="number"
                                   step="0.01"
                                   name="max_order_amount"
                                   class="form-control"
                                   placeholder="Leave empty for unlimited">

                            @error('max_order_amount')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                            <small class="text-muted">Maximum order amount (leave empty for no upper limit)</small>

                        </div>

                        {{-- Charge Type --}}
                        <div class="form-group">

                            <label>Charge Type <span class="text-danger">*</span></label>

                            <select name="charge_type"
                                    id="chargeType"
                                    class="form-control">

                                <option value="fixed">

                                    Fixed Amount

                                </option>

                                <option value="percentage">

                                    Percentage (%)

                                </option>

                            </select>

                            @error('charge_type')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Charge Amount Field (dynamic) --}}
                        <div class="form-group"
                             id="chargeAmountField">

                            <label id="chargeAmountLabel">Charge Amount (FCFA) <span class="text-danger">*</span></label>

                            <input type="number"
                                   step="0.01"
                                   name="charge_amount"
                                   class="form-control"
                                   placeholder="0.00">

                            @error('charge_amount')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                            <small id="chargeAmountHint" class="text-muted">Enter the fixed amount in FCFA</small>

                        </div>

                        {{-- Status --}}
                        <div class="form-group">

                            <label>Status</label>

                            <select name="is_active"
                                    class="form-control">

                                <option value="1">

                                    Active

                                </option>

                                <option value="0">

                                    Inactive

                                </option>

                            </select>

                            @error('is_active')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Sort Order --}}
                        <div class="form-group">

                            <label>Sort Order</label>

                            <input type="number"
                                   name="sort_order"
                                   class="form-control"
                                   placeholder="0"
                                   value="0">

                            @error('sort_order')
                                <div class="text-danger">
                                    {{ $message }}
                                </div>
                            @enderror

                            <small class="text-muted">Lower number = Higher priority (applies first)</small>

                        </div>

                    </div>

                    <div class="card-footer text-right">

                        <a href="{{ route('cod-charges.index') }}"
                           class="btn btn-secondary">

                            Cancel

                        </a>

                        <button type="submit"
                                class="btn btn-primary">

                            Create COD Charge

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

    updateChargeFields();

    $('#chargeType').change(function () {

        updateChargeFields();

    });

    function updateChargeFields()
    {
        let type = $('#chargeType').val();

        if(type == 'fixed')
        {
            $('#chargeAmountLabel').text('Charge Amount (FCFA)');
            $('#chargeAmountHint').text('Enter the fixed amount in FCFA (e.g., 500)');
        }
        else
        {
            $('#chargeAmountLabel').text('Charge Percentage (%)');
            $('#chargeAmountHint').text('Enter the percentage value (e.g., 5 for 5%)');
        }
    }

});

</script>

@endsection
