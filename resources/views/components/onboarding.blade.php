@if($show)
<style>

  .breadcrumbs {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    justify-content: center;
  }

  .breadcrumbs li {
    display: flex;
    align-items: center;
    margin-right: 50px;
    position: relative;
    text-align: center;
  }

  .breadcrumbs li::after {
    content: '';
    width: 50px;
    height: 2px;
    background-color: #ccc;
    position: absolute;
    top: 50%;
    left: 100%;
    transform: translateY(-50%);
  }

  .breadcrumbs li:last-child::after {
    content: none;
  }

  .breadcrumbs li.completed::before,
  .breadcrumbs li.incomplete::before {
    content: '\2713'; /* Checkmark character */
    display: inline-block;
    width: 18px;
    height: 18px;
    line-height: 18px;
    text-align: center;
    border-radius: 50%;
    margin-right: 5px;
    font-size: 1.2em;
  }

  .breadcrumbs li.completed::before {
    background-color: #94c748;
    color: white;
  }

  .breadcrumbs li.incomplete::before {
    background-color: lightgray;
    color: white;
  }

</style>
<div class="card mb-2">
  <div class="card-body">
    <div class="col mb-6">
      <h2 class="text-center">{{ __('Welcome!') }}</h2>
      <p class="text-center">{{ __('We will guide you through the process of setting up your account.') }}</p>
    </div>
    <ul class="breadcrumbs mb-6">
      <li class="{{ $hasAssets ? 'completed' : 'incomplete' }}">
        @if($hasAssets)
        <a href="{{ App\Helpers\AdversaryMeter::redirectUrl('assets') }}" target="_blank">
          {{ __('Vulnerability Scanner') }}
        </a>
        @else
        <a href="#scanner">
          {{ __('Vulnerability Scanner') }}
        </a>
        @endif
      </li>
      <li class="{{ $hasAgents ? 'completed' : 'incomplete' }}">
        @if($hasAssets)
        <a href="{{ route('home', ['tab' => 'servers', 'servers_type' => 'instrumented']) }}" target="_blank">
          {{ __('Agents') }}
        </a>
        @else
        <a href="#agents">
          {{ __('Agents') }}
        </a>
        @endif
      </li>
      <li class="{{ $hasHoneypots ? 'completed' : 'incomplete' }}">
        @if($hasHoneypots)
        <a href="{{ App\Helpers\AdversaryMeter::redirectUrl('setup_honeypots') }}" target="_blank">
          {{ __('Honeypots') }}
        </a>
        @else
        <a href="#honeypots">
          {{ __('Honeypots') }}
        </a>
        @endif
      </li>
      <li class="{{ $hasPssi ? 'completed' : 'incomplete' }}">
        @if($hasPssi)
        <a href="{{ route('home', ['tab' => 'documents']) }}" target="_blank">
          {{ __('ISSP') }}
        </a>
        @else
        <a href="#pssi">
          {{ __('ISSP') }}
        </a>
        @endif
      </li>
    </ul>
  </div>
</div>
@if(!$hasAssets)
<div id="scanner" class="mb-2">
  <x-onboarding-monitor-asset/>
</div>
@endif
@if(!$hasAgents)
<div id="agents" class="mb-2">
  <x-onboarding-agents/>
</div>
@endif
@if(!$hasHoneypots)
<div id="honeypots" class="mb-2">
  <x-onboarding-honeypots/>
</div>
@endif
@if(!$hasPssi)
<div id="pssi" class="mb-2">
  <x-onboarding-pssi/>
</div>
@endif
@endif