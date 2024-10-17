@extends('layouts.app')

@section('content')
<style>
  .product-image {
    max-width: 100%;
    display: block;
    margin-bottom: 2em;
  }
</style>
@php
@endphp
<div class="container">
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          @unless ($checkout)
          <div class="alert alert-warning">
            <p>{{ __('Hey, nothing to check out here!') }}</p>
          </div>
          @endunless
          @if ($checkout)
          <form x-data="checkout" action="{{ route('checkout.submit') }}" method="post">
            {{ csrf_field() }}
            @include('checkout._billpayer', ['billpayer' => $checkout->getBillPayer()])
            <div class="mb-4">
              <input type="hidden" name="ship_to_billing_address" value="0"/>
              <div class="form-check">
                <input class="form-check-input" id="chk_ship_to_billing_address" type="checkbox"
                       name="ship_to_billing_address" value="1" x-model="shipToBillingAddress">
                <label class="form-check-label" for="chk_ship_to_billing_address">{{ __('Ship to the same address')
                  }}</label>
              </div>
            </div>
            @include('checkout._shipping_address', ['address' => $checkout->getShippingAddress()])
            @include('checkout._payment')
            <div class="mb-3">
              <label>{{ __('Order Notes') }}</label>
              {{ Form::textarea('notes', $checkout->getCustomAttribute('notes'), [
              'class' => 'form-control' . ($errors->has('notes') ? ' is-invalid' : ''),
              'rows' => 3
              ]) }}
              @if ($errors->has('notes'))
              <div class="invalid-feedback">{{ $errors->first('notes') }}</div>
              @endif
            </div>
            <hr>
            <div>
              <button class="btn btn-lg btn-primary text-white">{{ __('Submit Order') }}</button>
            </div>
          </form>
          @endif
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card bg-white">
        <div class="card-header">{{ __('Summary') }}</div>
        <div class="card-body">
          @include('cart._summary')
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('alpine')
@if ($checkout)
<script>
  document.addEventListener("alpine:init", () => {
    Alpine.data('checkout', () => ({
      isOrganization: {{ (old('billpayer.is_organization') ? : true) ? 'true' : 'false' }},
      shipToBillingAddress: {{ (old('ship_to_billing_address') ?? true) ? 'true' : 'false' }}
  }))
  });
</script>
@endif
@endpush
