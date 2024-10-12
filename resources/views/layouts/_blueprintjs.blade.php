<!-- Reactjs -->
<script src="{{ asset('adversary_meter/src/blueprintjs/reactjs/react.production.min.js') }}"></script>
<script src="{{ asset('adversary_meter/src/blueprintjs/reactjs/react-dom.production.min.js') }}"></script>
<script src="{{ asset('adversary_meter/src/blueprintjs/reactjs/react-is.production.min.js') }}"></script>

<!-- Blueprintjs -->
<link href="{{ asset('adversary_meter/src/blueprintjs/normalize/normalize.css') }}" rel="stylesheet"/>
<link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/blueprint-icons.css') }}" rel="stylesheet"/>
<link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/blueprint.css') }}" rel="stylesheet"/>
<link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/blueprint-popover2.css') }}" rel="stylesheet"/>
<link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/table.css') }}" rel="stylesheet"/>
<link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/blueprint-select.css') }}" rel="stylesheet"/>
<link href="{{ asset('adversary_meter/src/blueprintjs/blueprintjs/blueprint-datetime.css') }}" rel="stylesheet"/>

<script>
  /*
   * Fix Blueprintjs issue.
   *
   * https://adambien.blog/roller/abien/entry/uncaught_referenceerror_process_is_not
   */
  window.process = {
    env: {
      NODE_ENV: 'production'
    }
  }
</script>

<!-- Javascript SDK -->
<script src="{{ asset('adversary_meter/src/blueprintjs/main.min.js') }}"></script>
