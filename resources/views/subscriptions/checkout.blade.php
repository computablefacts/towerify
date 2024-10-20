@extends('layouts.app')

@section('content')
<div class="container">
  @if($is_subscribed)
  <div class="row">
    <div class="col">
      <div class="alert alert-warning">
        {{ __('You are already subscribed to this plan.') }}
      </div>
    </div>
  </div>
  @else
  <form x-data="checkout" action="" method="post" onsubmit="return subscribe(this)">
    <input type="hidden" name="plan" value="{{ $plan }}"/>
    {{ csrf_field() }}
    <div class="container">
      <div class="row">
        <div class="col-6">
            <?php $billpayer = $checkout->getBillPayer() ?>
          <h3>{{ __('Bill To') }}</h3>
          <hr>
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <input class="form-control"
                       placeholder="E-mail"
                       name="billpayer[email]"
                       type="text"
                       value="{{ $billpayer->getEmail() }}">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <input class="form-control"
                       placeholder="Telephone"
                       name="billpayer[phone]"
                       type="text"
                       value="{{ $billpayer->getPhone() }}">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <input class="form-control"
                       placeholder="{{ __('First name') }}"
                       name="billpayer[firstname]"
                       type="text"
                       value="{{ $billpayer->getFirstName() }}">
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3">
                <input class="form-control"
                       placeholder="{{ __('Last name') }}"
                       name="billpayer[lastname]"
                       type="text"
                       value="{{ $billpayer->getLastName() }}">
              </div>
            </div>
            <div class="mb-3">
              <div class="form-check">
                <input class="form-check-input"
                       id="chk_is_organization"
                       type="checkbox"
                       name="billpayer[is_organization]"
                       value="1"
                       x-model="isOrganization">
                <label class="form-check-label" for="chk_is_organization">
                  {{ __('Bill to Company') }}
                </label>
              </div>
            </div>
            <div id="billpayer-organization" x-show="isOrganization">
              <div class="mb-3">
                <input class="form-control"
                       placeholder="{{ __('Company name') }}"
                       name="billpayer[company_name]"
                       type="text"
                       value="{{ $billpayer->getCompanyName() }}">
              </div>
              <div class="mb-3">
                <input class="form-control"
                       placeholder="{{ __('Tax no.') }}"
                       name="billpayer[tax_nr]"
                       type="text"
                       value="{{ $billpayer->getTaxNumber() }}">
              </div>
            </div>
          </div>
          <h3>{{ __('Billing Address') }}</h3>
          <hr>
          <div class="row">
            <div class="mb-3 row">
              <label class="col-form-label col-md-2">{{ __('Country') }}</label>
              <div class="col-md-10">
                <select class="form-control" name="billpayer[address][country_id]">
                  @foreach($countries as $country)
                  <option value="{{ $country->id }}"
                          {{ $billpayer->address->country_id === $country->id || $country->id === 'FR' ? 'selected' : ''
                    }}>
                    {{ $country->name }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="mb-3 row">
              <label class="col-form-label col-md-2">{{ __('Address') }}</label>
              <div class="col-md-10">
                <input class="form-control"
                       name="billpayer[address][address]"
                       type="text"
                       value="{{ $billpayer->address->address }}">
              </div>
            </div>
            <div class="mb-3 row">
              <label class="col-form-label col-md-2">{{ __('Zip code') }}</label>
              <div class="col-md-4">
                <input class="form-control"
                       name="billpayer[address][postalcode]"
                       type="text"
                       value="{{ $billpayer->address->postalcode }}">
              </div>
              <label class="col-form-label col-md-2">{{ __('City') }}</label>
              <div class="col-md-4">
                <input class="form-control"
                       name="billpayer[address][city]"
                       type="text"
                       value="{{ $billpayer->address->city }}">
              </div>
            </div>
          </div>
          <h3>{{ __('Credit Card Number') }}</h3>
          <hr>
          <div class="row">
            <div class="col">
              <div id="stripe-elements-container">
                <!-- Stripe Elements will create form elements here -->
              </div>
            </div>
          </div>
          <div class="row mt-6 mb-6">
            <input type="submit" class="btn btn-primary text-white" value="{{ __('Subscribe') }}">
          </div>
        </div>
        <div class="col">
          <h3>{{ __('Summary') }}</h3>
          <hr>
          <div class="card bg-white">
            <div class="card-body">
              @include('cart._summary', ['euVat' => null])
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>
  @endif
</div>
@endsection

@push('alpine')
@if ($checkout)
<script>
  document.addEventListener("alpine:init", () => {
    Alpine.data('checkout', () => ({
      // isOrganization: {{ (old('billpayer.is_organization') ? : true) ? 'true' : 'false' }},
      isOrganization: true, shipToBillingAddress: true,
    }))
  });
</script>
@endif
@endpush

@if(!$is_subscribed)
@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>

  // Initialize Stripe Elements
  const stripe = Stripe("{{ config('towerify.stripe.key') }}");
  const card = stripe.elements().create('card');
  card.mount('#stripe-elements-container');

  // Handle subscription when user clicks subscribe button
  function subscribe(formData) {

    stripe.createPaymentMethod({
      type: 'card', card: card,
    }).then(function (result) {
      console.log(result)
      if (result.error) {
        toaster.toastError(result.error.message);
      } else {

        const fdata = new FormData(formData);
        fdata.append('payment_method', result.paymentMethod.id);
        /*
          for (let pair of fdata.entries()) {
            console.log(pair[0] + ' -> ' + pair[1]);
          }
        */
        axios.post("{{ route('subscribe.process') }}", fdata, {
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Content-Type': 'application/json',
          }
        }).then(function (response) {
          toaster.toastSuccess('Thank you for subscribing!');
        }).catch((error) => toaster.toastAxiosError(error));
      }
    });

    return false;
  }

</script>
@endpush
@endif