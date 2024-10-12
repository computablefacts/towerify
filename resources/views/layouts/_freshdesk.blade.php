@if(Auth::check() && config('towerify.freshdesk'))
<script>
  window.fwSettings = {
    widget_id: "{{ config('towerify.freshdesk') }}",
  };
  !function () {
    if ("function" != typeof window.FreshworksWidget) {
      var n = function () {
        n.q.push(arguments)
      };
      n.q = [], window.FreshworksWidget = n
    }
  }();
  FreshworksWidget('identify', 'ticketForm', {
    name: "{{ Auth::user()->name }}", email: "{{ Auth::user()->email }}",
  });
</script>
<script src="https://widget.freshworks.com/widgets/{{ config('towerify.freshdesk') }}.js" async defer>
</script>
@endif