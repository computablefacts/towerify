@extends('layouts.app')

@section('content')
<style>
  .switch {
    position: relative;
    display: inline-block;
    width: 30px;
    height: 17px;
  }

  .switch input {
    opacity: 0;
    width: 0;
    height: 0;
  }

  .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
  }

  .slider:before {
    position: absolute;
    content: "";
    height: 13px;
    width: 13px;
    left: 2px;
    bottom: 2px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
  }

  input:checked + .slider {
    background-color: #2196F3;
  }

  input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
  }

  input:checked + .slider:before {
    -webkit-transform: translateX(13px);
    -ms-transform: translateX(13px);
    transform: translateX(13px);
  }

  .slider.round {
    border-radius: 17px;
  }

  .slider.round:before {
    border-radius: 50%;
  }
</style>
<div id="the-cypher-brief" class="container">
  <div class="row justify-content-center">
    <div class="col-md-10">
      @if($briefes->isEmpty())
      {{ __('All clear—no briefs today!') }}
      @else
      <div class="d-flex flex-row justify-content-end mb-3">
        <span class="align-self-center">COMPACT</span>&nbsp;&nbsp;
        <label class="switch align-self-center">
          <input id="toggle-view" type="checkbox" checked>
          <span class="slider round"></span>
        </label>
      </div>
      @foreach ($briefes as $brief)
      <div class="card mb-4">
        <div class="card-body">
          <h5 id="toggle-{{ $brief->id }}" class="card-title cursor-pointer">
            <span style="color:#f8b502">&gt;</span>&nbsp;{{ strtoupper($brief->brief()['teaser']) }}
          </h5>
          <h6 class="card-subtitle text-muted">
            {{ $brief->brief()['opener'] }}
          </h6>
          @if($brief->brief()['why_it_matters'])
          <div id="why-it-matters-{{ $brief->id }}" class="mt-3 d-none">
              <?php $whyItMatters = preg_split("/\r\n|\n|\r/", $brief->brief()['why_it_matters']) ?>
            @foreach($whyItMatters as $index => $text)
            @if($index === 0)
            <div class="card-text">
              <b style="color:#f8b502">WHY IT MATTERS</b>&nbsp;
            </div>
            @endif
            <div class="card-text">
              {{ trim($text) }}
            </div>
            @endforeach
          </div>
          @endif
          @if($brief->brief()['go_deeper'])
          <div id="go-deeper-{{ $brief->id }}" class="mt-3 d-none">
              <?php $goDeeper = preg_split("/\r\n|\n|\r/", $brief->brief()['go_deeper']) ?>
            @foreach($goDeeper as $index => $text)
            @if($index === 0)
            <div class="card-text" style="color:#f8b502">
              <b style="color:#f8b502">GO DEEPER</b>&nbsp;
            </div>
            @endif
            <div class="card-text">
              {{ trim($text) }}
            </div>
            @endforeach
          </div>
          @endif
          @if($brief->brief()['website'])
          <div id="website-{{ $brief->id }}" class="mt-3 d-none">
            <a href="{{ $brief->brief()['link'] }}" class="card-link" target="_blank">
              {{ $brief->brief()['website'] }}
            </a>
          </div>
          @endif
        </div>
      </div>
      @endforeach
      @endif
    </div>
  </div>
</div>
<script>

  function compactOrExpandOne(briefId) {

    const whyItMatters = document.getElementById('why-it-matters-' + briefId);
    const goDeeper = document.getElementById('go-deeper-' + briefId);
    const website = document.getElementById('website-' + briefId);

    // console.log(briefId, whyItMatters, goDeeper, website);

    if (whyItMatters) {
      whyItMatters.classList.toggle('d-none');
    }
    if (goDeeper) {
      goDeeper.classList.toggle('d-none');
    }
    if (website) {
      website.classList.toggle('d-none');
    }
  }

  function compactOrExpandAll() {

    const isCompactView = document.getElementById('toggle-view').checked;
    const whyItMatters = Array.from(document.querySelectorAll('[id^=why-it-matters-]'));
    const goDeeper = Array.from(document.querySelectorAll('[id^=go-deeper-]'));
    const website = Array.from(document.querySelectorAll('[id^=website-]'));

    // console.log(whyItMatters, goDeeper, website);

    if (isCompactView) {
      whyItMatters.forEach(el => el.classList.add('d-none'));
      goDeeper.forEach(el => el.classList.add('d-none'));
      website.forEach(el => el.classList.add('d-none'));
    } else {
      whyItMatters.forEach(el => el.classList.remove('d-none'));
      goDeeper.forEach(el => el.classList.remove('d-none'));
      website.forEach(el => el.classList.remove('d-none'));
    }
  }

  const root = document.getElementById('the-cypher-brief');
  root.addEventListener('click', event => {
    if (event.target.tagName === 'H5') {
      compactOrExpandOne(event.target.id.substring(event.target.id.lastIndexOf('-') + 1));
      event.preventDefault();
      event.stopPropagation();
    }
    if (event.target.tagName === 'INPUT' && event.target.type === 'checkbox') {
      compactOrExpandAll();
      event.stopPropagation();
    }
  });

  compactOrExpandAll();
</script>
@endsection
