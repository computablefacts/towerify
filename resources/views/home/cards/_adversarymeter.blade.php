@if(Auth::user()->canUseAdversaryMeter())
<div class="card mb-3 border" style="background-color:#ff4d4d;">
  <div class="row">
    <div class="col text-center p-3">
      <a href="{{ App\Helpers\AdversaryMeter::redirectUrl() }}" target="_blank" class="text-white">
        Open AdversaryMeter...
      </a>
    </div>
  </div>
</div>
@endif