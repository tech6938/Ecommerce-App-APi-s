{{-- resources/views/admin/payment-methods/create.blade.php --}}
@extends('layout.dashboard-layout')

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-body">
                <div class="card">
                    <div class="card-header">
                        <h4>Add Payment Method</h4>
                        <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">Back</a>
                    </div>

                    <form method="POST" action="{{ route('admin.payment-methods.store') }}">
                        @csrf

                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Basic Information --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Method Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                            placeholder="e.g., Paytm, Stripe, PayPal" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Code <span class="text-danger">*</span></label>
                                        <input type="text" name="code" class="form-control"
                                            placeholder="e.g., paytm, stripe, paypal" required>
                                        <small class="text-muted">Unique identifier (lowercase, no spaces)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Gateway Type <span class="text-danger">*</span></label>
                                        <select name="type" id="gatewayType" class="form-control" required>
                                            <option value="online">Online Payment Gateway</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Environment</label>
                                        <select name="environment" class="form-control">
                                            <option value="sandbox">Sandbox (Testing)</option>
                                            <option value="production">Production (Live)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Payment method description"></textarea>
                            </div>

                            <div class="form-group">
                                <label>Callback URL</label>
                                <input type="url" name="callback_url" class="form-control"
                                    placeholder="https://yourdomain.com/payment/callback">
                                <small class="text-muted">URL where payment gateway will send response after payment</small>
                            </div>

                            <hr>
                            <h5>API Credentials</h5>
                            <p class="text-muted">Enter the API credentials for this payment gateway</p>

                            {{-- Dynamic Credential Fields Based on Gateway Selection --}}
                            <div id="credential-fields">
                                {{-- Default fields that appear for all gateways --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>API Key / Publishable Key / Client ID</label>
                                            <input type="text" name="api_key" class="form-control"
                                                placeholder="Enter API Key">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Secret Key / Client Secret</label>
                                            <input type="text" name="secret_key" class="form-control"
                                                placeholder="Enter Secret Key">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Additional fields for specific gateways (can be added dynamically) --}}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Merchant Key (for Paytm)</label>
                                        <input type="text" name="merchant_key" class="form-control"
                                            placeholder="Enter Merchant Key">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Merchant ID (for Paytm)</label>
                                        <input type="text" name="merchant_id" class="form-control"
                                            placeholder="Enter Merchant ID">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Public Key (for Flutterwave)</label>
                                        <input type="text" name="public_key" class="form-control"
                                            placeholder="Enter Public Key">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Private/Encryption Key</label>
                                        <input type="text" name="private_key" class="form-control"
                                            placeholder="Enter Private/Encryption Key">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Webhook Secret (for Stripe)</label>
                                <input type="text" name="webhook_secret" class="form-control"
                                    placeholder="Enter Webhook Secret">
                            </div>

                            <hr>
                            <div class="form-group">
                                <label>Status</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_active" class="custom-control-input"
                                        id="methodStatus" value="1" checked>
                                    <label class="custom-control-label" for="methodStatus">
                                        Active
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-success">Create Payment Method</button>
                            <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('js')
    <script>
        // Dynamic credential fields based on gateway selection
        document.getElementById('gatewayType').addEventListener('change', function() {
            const credentialFields = document.getElementById('credential-fields');
            const gatewayType = this.value;

            if (gatewayType === 'bank_transfer') {
                credentialFields.style.display = 'none';
            } else {
                credentialFields.style.display = 'block';
            }
        });
    </script>
@endsection
