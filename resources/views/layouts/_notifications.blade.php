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

  let notifications = @json($notifications);

  function dismissNotification(notificationId) {
    axios.get(`{{ url('/notifications/${notificationId}/dismiss') }}`).then(response => {
      toaster.el.toast('The notification has been dismissed!', 'success');
    }).catch(error => {
      toaster.el.toast('An error occurred.', 'danger');
      console.error('Error:', error);
    });
    const notification = notifications.find(notif => notif.id === notificationId);
    if (notification) {
      notifications = notifications.filter(notif => notif.data.group !== notification.data.group);
    }
    drawer33.redraw();
  }

  function showNotifications() {
    drawer33.render = () => {
      const rows = notifications.map(notification => {
        let details = '';
        for (let key in notification.data.details) {
          if (notification.data.details[key]) {
            details += (`<li><b>${key}.</b> ${notification.data.details[key]}</li>`);
          }
        }
        let action = '';
        if (notification.data.action) {
          action = `<a href="${notification.data.action.url}">${notification.data.action.name} &gt;</a>`;
        }
        return `
            <div class="card border-${notification.data.level} m-1">
              <div class="card-body p-2">
                <h6 class="card-title">${notification.data.type}&nbsp;<span style="color:#f8b502">/</span>&nbsp;${notification.timestamp}</h6>
                <p class="card-text">${notification.data.message}</p>
                <h6 class="card-title">DETAILS</h6>
                <ul>
                  ${details}
                </ul>
                <div class="row">
                  <div class="col text-left">
                    <a id="${notification.id}" href="#" onclick="event.preventDefault();dismissNotification(event.target.id)">dismiss</a>
                  </div>
                  <div class="col text-right">
                    ${action}
                  </div>
                </div>
              </div>
            </div>
        `;
      });
      return `<div class="container p-0 overflow-y-scroll" style="height:100vh;">${rows.join('')}</div>`;
    };
    drawer33.el.show = true;
  }
</script>