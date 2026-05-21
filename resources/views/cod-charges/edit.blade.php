@extends('layout.dashboard-layout')

@section('content')

    <div class="main-content">

        <section class="section">

            <div class="section-body">

                <div class="card">

                    <div class="card-header">
                        <h4>Update COD Charge</h4>
                    </div>

                    <form method="POST" action="{{ route('cod-charges.update', $codCharge->id) }}">

                        @csrf
                        @method('PUT')

                        <div class="card-body">

                            {{-- Display validation errors --}}
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Min Order Amount --}}
                            <div class="form-group">

                                <label>Minimum Order Amount (FCFA) <span class="text-danger">*</span></label>

                                <input type="number" step="0.01" name="min_order_amount"
                                    value="{{ old('min_order_amount', $codCharge->min_order_amount) }}"
                                    class="form-control @error('min_order_amount') is-invalid @enderror" placeholder="0.00"
                                    required>

                                @error('min_order_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <small class="text-muted">Minimum order amount to apply this charge</small>

                            </div>

                            {{-- Max Order Amount --}}
                            <div class="form-group">

                                <label>Maximum Order Amount (FCFA)</label>

                                <input type="number" step="0.01" name="max_order_amount"
                                    value="{{ old('max_order_amount', $codCharge->max_order_amount) }}"
                                    class="form-control @error('max_order_amount') is-invalid @enderror"
                                    placeholder="Leave empty for unlimited">

                                @error('max_order_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <small class="text-muted">Maximum order amount (leave empty for no upper limit)</small>

                            </div>

                            {{-- Charge Type --}}
                            <div class="form-group">

                                <label>Charge Type <span class="text-danger">*</span></label>

                                <select name="charge_type" id="chargeType"
                                    class="form-control @error('charge_type') is-invalid @enderror" required>

                                    <option value="fixed"
                                        {{ old('charge_type', $codCharge->charge_type) == 'fixed' ? 'selected' : '' }}>

                                        Fixed Amount

                                    </option>

                                    <option value="percentage"
                                        {{ old('charge_type', $codCharge->charge_type) == 'percentage' ? 'selected' : '' }}>

                                        Percentage (%)

                                    </option>

                                </select>

                                @error('charge_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>

                            {{-- Charge Amount --}}
                            <div class="form-group" id="chargeAmountField">

                                <label id="chargeAmountLabel">Charge Amount <span class="text-danger">*</span></label>

                                <input type="number" step="0.01" name="charge_amount"
                                    value="{{ old('charge_amount', $codCharge->charge_amount) }}"
                                    class="form-control @error('charge_amount') is-invalid @enderror" placeholder="0.00"
                                    required>

                                @error('charge_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <small class="text-muted" id="chargeAmountHint">
                                    {{ $codCharge->charge_type == 'fixed' ? 'Enter the fixed amount in FCFA' : 'Enter the percentage value (e.g., 5 for 5%)' }}
                                </small>

                            </div>

                            {{-- Status --}}
                            <div class="form-group">

                                <label>Status</label>

                                <select name="is_active" class="form-control @error('is_active') is-invalid @enderror">

                                    <option value="1" {{ old('is_active', $codCharge->is_active) ? 'selected' : '' }}>
                                        Active
                                    </option>

                                    <option value="0" {{ old('is_active', $codCharge->is_active) ? '' : 'selected' }}>
                                        Inactive
                                    </option>

                                </select>

                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                            </div>

                            {{-- Sort Order --}}
                            <div class="form-group">

                                <label>Sort Order</label>

                                <input type="number" name="sort_order"
                                    value="{{ old('sort_order', $codCharge->sort_order) }}"
                                    class="form-control @error('sort_order') is-invalid @enderror" placeholder="0">

                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <small class="text-muted">Lower number = Higher priority</small>

                            </div>

                        </div>

                        <div class="card-footer text-right">

                            <a href="{{ route('cod-charges.index') }}" class="btn btn-secondary">

                                Cancel

                            </a>

                            <button type="submit" class="btn btn-primary">

                                Update COD Charge

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
        $(document).ready(function() {
            updateChargeFields();

            $('#chargeType').change(function() {
                updateChargeFields();
            });

            function updateChargeFields() {
                let type = $('#chargeType').val();

                if (type == 'fixed') {
                    $('#chargeAmountLabel').text('Charge Amount (FCFA)');
                    $('#chargeAmountHint').text('Enter the fixed amount in FCFA (e.g., 500)');
                } else {
                    $('#chargeAmountLabel').text('Charge Percentage (%)');
                    $('#chargeAmountHint').text('Enter the percentage value (e.g., 5 for 5%)');
                }
            }
        });
    </script>
@endsection
