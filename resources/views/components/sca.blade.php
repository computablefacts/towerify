<div class="card">
  <div class="card-body">
    <div class="row">
      <div class="col">
        <div id="policies"></div>
      </div>
    </div>
    <div class="row mt-3">
      <div class="col">
        <div id="frameworks"></div>
      </div>
    </div>
    <div class="row mt-3">
      <div class="col">
        <div id="search"></div>
      </div>
      <div class="col col-auto">
        <div id="submit"></div>
      </div>
    </div>
  </div>
</div>
@foreach($checks as $check)
<div class="card mt-3">
  <div class="card-header pb-0">
    <div class="row mt-2">
      <div class="col">
        <h6>{{ $check->title }}</h6>
      </div>
      <div class="col col-auto">
        @foreach($check->frameworks() as $f)
        <span class="lozenge information">{{ $f }}</span>&nbsp;
        @endforeach
      </div>
    </div>
  </div>
  <div class="card-body pt-0">
    <div class="row mt-2">
      <div class="col col-2 text-end">
        <b>{{ __('Description') }}</b>
      </div>
      <div class="col">
        {{ $check->description }}
      </div>
    </div>
    <div class="row mt-2">
      <div class="col col-2 text-end">
        <b>{{ __('Rationale') }}</b>
      </div>
      <div class="col">
        {{ $check->rationale }}
      </div>
    </div>
    <div class="row mt-2">
      <div class="col col-2 text-end">
        <b>{{ __('Remediation') }}</b>
      </div>
      <div class="col">
        {{ $check->remediation }}
      </div>
    </div>
    @if($check->references)
    <div class="row mt-2">
      <div class="col col-2 text-end">
        <b>{{ __('References') }}</b>
      </div>
      <div class="col">
        <ul class="mb-0" style="padding-left: 1rem;">
          @foreach($check->references as $reference)
          @if(\Illuminate\Support\Str::startsWith($reference, ['http://', 'https://']))
          <li><a href="{{ $reference }}" target="_blank">{{ $reference }}</a></li>
          @else
          <li>{{ $reference }}</li>
          @endif
          @endforeach
        </ul>
      </div>
    </div>
    @endif
    @if($check->hasMitreTactics())
    <div class="row mt-2">
      <div class="col col-2 text-end">
        <b>{{ __('Mitre Tactics') }}</b>
      </div>
      <div class="col">
        <ul class="mb-0" style="padding-left: 1rem;">
          @foreach($check->mitreTactics() as $tactic)
          <li><a href="https://attack.mitre.org/tactics/{{ $tactic }}/" target="_blank">{{ $tactic }}</a></li>
          @endforeach
        </ul>
      </div>
    </div>
    @endif
    @if($check->hasMitreTechniques())
    <div class="row mt-2">
      <div class="col col-2 text-end">
        <b>{{ __('Mitre Techniques') }}</b>
      </div>
      <div class="col">
        <ul class="mb-0" style="padding-left: 1rem;">
          @foreach($check->mitreTechniques() as $technique)
          <li><a href="https://attack.mitre.org/techniques/{{ $technique }}/" target="_blank">{{ $technique }}</a></li>
          @endforeach
        </ul>
      </div>
    </div>
    @endif
    @if($check->hasMitreMitigations())
    <div class="row mt-2">
      <div class="col col-2 text-end">
        <b>{{ __('Mitre Mitigations') }}</b>
      </div>
      <div class="col">
        <ul class="mb-0" style="padding-left: 1rem;">
          @foreach($check->mitreMitigations() as $mitigation)
          <li><a href="https://attack.mitre.org/mitigations/{{ $mitigation }}/" target="_blank">{{ $mitigation }}</a>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
    @endif
  </div>
</div>
@endforeach
<script>

  let selectedPolicy = null;
  let selectedFramework = null;
  let searchText = null;

  const queryString = () => '?tab=sca' + (selectedPolicy ? '&policy=' + selectedPolicy.uid : '') + (selectedFramework
    ? '&framework=' + selectedFramework : '') + (searchText ? '&search=' + searchText : '');

  const elPolicies = new com.computablefacts.blueprintjs.MinimalSelect(document.getElementById('policies'),
    item => item.name);
  elPolicies.defaultText = "{{ __('Select policy...') }}";
  elPolicies.items = @json($policies);
  selectedPolicy = elPolicies.items.find(policy => policy.uid === "{{ $policy }}")
  elPolicies.selectedItem = selectedPolicy;
  elPolicies.onSelectionChange(item => {
    selectedPolicy = item;
    selectedFramework = null;
    searchText = null;
    elFrameworks.disabled = !selectedPolicy;
    elSearch.disabled = !selectedPolicy;
    window.location = window.location.href.split('?')[0] + queryString();
  });

  const elFrameworks = new com.computablefacts.blueprintjs.MinimalSelect(document.getElementById('frameworks'));
  elFrameworks.defaultText = "{{ __('Select framework...') }}";
  elFrameworks.items = @json($frameworks);
  selectedFramework = elFrameworks.items.find(framework => framework === "{{ $framework }}");
  elFrameworks.selectedItem = selectedFramework;
  elFrameworks.onSelectionChange(item => {
    selectedFramework = item;
    searchText = null;
    window.location = window.location.href.split('?')[0] + queryString();
  });
  elFrameworks.disabled = !selectedPolicy;

  const elSearch = new com.computablefacts.blueprintjs.MinimalTextInput(document.getElementById('search'),
    "{{ $search }}");
  elSearch.icon = 'filter';
  elSearch.placeholder = "{{ __('Filtrer...') }}";
  elSearch.disabled = !selectedPolicy;

  const elSubmit = new com.computablefacts.blueprintjs.MinimalButton(document.getElementById('submit'),
    "{{ __('Apply') }}");
  elSubmit.rightIcon = 'chevron-right';
  elSubmit.disabled = !selectedPolicy;
  elSubmit.onClick(() => {
    searchText = elSearch.value;
    window.location = window.location.href.split('?')[0] + queryString();
  });

</script>