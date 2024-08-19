<a href="#" class="outline-none" onclick="event.preventDefault();showNotifications()">
  @if(count($notifications) === 3)
  <img src="{{ asset('/images/icons/bell-on-3.png') }}" class="align-self-center" width="20">
  @elseif(count($notifications) === 2)
  <img src="{{ asset('/images/icons/bell-on-2.png') }}" class="align-self-center" width="20">
  @elseif(count($notifications) === 1)
  <img src="{{ asset('/images/icons/bell-on-1.png') }}" class="align-self-center" width="20">
  @else
  <img src="{{ asset('/images/icons/bell-on.png') }}" class="align-self-center" width="20">
  @endif
</a>
<script>
  function showNotifications() {
    const data = @json($notifications);
    drawer25.render = () => {
      return 'TODO';
    };
    drawer25.el.show = true;
  }
</script>