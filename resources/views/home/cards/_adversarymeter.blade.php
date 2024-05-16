@if(Auth::user()->canUseAdversaryMeter())
<div class="card mb-3 border">
  <div class="row no-gutters">
    <div class="col-md-2">
      <img src="{{ asset('images/adversarymeter.png') }}" class="card-img" alt="AdversaryMeter's Logo">
    </div>
    <div class="col-md">
      <div class="card-body">
        <h5 class="card-title">
          <a href="https://adversarymeter.io" target="_blank">AdversaryMeter</a>
        </h5>
        <p class="card-text">
          Protect your infrastructure and data from attackers!
          <br>
          AdversaryMeter is an intelligent surveillance service that guarantees coverage of your organization's
          external perimeter.
        </p>
        <p class="card-text">
          <small class="text-muted">
            @if(config('towerify.adversarymeter.api_key'))
            <a href="{{ App\Helpers\AdversaryMeter::redirectUrl() }}" target="_blank">
              open & see my assets
            </a>
            @else
            <a href="{{ config('towerify.adversarymeter.url') }}" target="_blank">
              open
            </a>
            @endif
          </small>
        </p>
      </div>
    </div>
  </div>
</div>
@endif