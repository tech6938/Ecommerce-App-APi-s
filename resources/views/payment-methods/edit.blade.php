@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Configure Payment Method: {{ $method->name }}</h4>
                    <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">Back</a>
                </div>

                <form method="POST" action="{{ route('admin.payment-methods.update', $method->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- COD Method - Simple Toggle --}}
                        @if($method->code == 'cod')
                            <div class="alert alert-info">
                                <strong>Cash on Delivery</strong> - Customer pays when they receive the order
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_active" class="custom-control-input" id="codStatus" value="1" {{ $method->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="codStatus">
                                        {{ $method->is_active ? 'Active' : 'Inactive' }}
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Status</button>

                        {{-- Online Payment Methods --}}
                        @else
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Payment Method Name</label>
                                        <input type="text" name="name" class="form-control" value="{{ $method->name }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Environment</label>
                                        <select name="environment" class="form-control">
                                            <option value="sandbox" {{ $method->environment == 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                                            <option value="production" {{ $method->environment == 'production' ? 'selected' : '' }}>Production (Live)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ $method->description }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>Callback URL</label>
                                <input type="url" name="callback_url" class="form-control" value="{{ $method->callback_url }}" placeholder="https://yourdomain.com/payment/callback">
                                <small class="text-muted">URL where payment gateway will send response after payment</small>
                            </div>

                            <hr>
                            <h5>API Credentials</h5>

                            {{-- Paytm Fields --}}
                            @if($method->code == 'paytm')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Merchant Key</label>
                                            <input type="text" name="merchant_key" class="form-control" value="{{ $method->merchant_key }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Merchant ID</label>
                                            <input type="text" name="merchant_id" class="form-control" value="{{ $method->merchant_id }}">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Stripe Fields --}}
                            @if($method->code == 'stripe')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Publishable Key (API Key)</label>
                                            <input type="text" name="api_key" class="form-control" value="{{ $method->api_key }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Secret Key</label>
                                            <input type="text" name="secret_key" class="form-control" value="{{ $method->secret_key }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Webhook Secret</label>
                                    <input type="text" name="webhook_secret" class="form-control" value="{{ $method->webhook_secret }}">
                                </div>
                            @endif

                            {{-- PayPal Fields --}}
                            @if($method->code == 'paypal')
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Client ID (API Key)</label>
                                            <input type="text" name="api_key" class="form-control" value="{{ $method->api_key }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Client Secret (Secret Key)</label>
                                            <input type="text" name="secret_key" class="form-control" value="{{ $method->secret_key }}">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Flutterwave Fields --}}
                            @if($method->code == 'flutterwave')
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Public Key</label>
                                            <input type="text" name="public_key" class="form-control" value="{{ $method->public_key }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Secret Key</label>
                                            <input type="text" name="secret_key" class="form-control" value="{{ $method->secret_key }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Encryption Key</label>
                                            <input type="text" name="private_key" class="form-control" value="{{ $method->private_key }}">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <hr>
                            <div class="form-group">
                                <label>Status</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" name="is_active" class="custom-control-input" id="methodStatus" value="1" {{ $method->is_active ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="methodStatus">
                                        {{ $method->is_active ? 'Active' : 'Inactive' }}
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Configuration</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
