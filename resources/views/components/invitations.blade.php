@if(Auth::user()->canListUsers())
<div class="card card-accent-secondary tw-card">
  <div class="card-header d-flex flex-row">
    <div class="align-items-start">
      <h3 class="m-0">
        {{ __('Invitations') }}
      </h3>
    </div>
    @if(Auth::user()->canManageUsers())
    <div class="align-items-end">
      <h3 class="m-0 cursor-pointer">
        <a onclick="toggleForm()">
          {{ __('+ new') }}
        </a>
      </h3>
    </div>
    @endif
  </div>
  <div id="form-invitation" class="container-fluid mt-2 d-none">
    <div class="row">
      <div class="mb-3 col-5 col-md-5">
        <label for="fullname" class="control-label">{{ __('User\'s name') }}</label>
        <input type="text" value='' class="form-control" id="fullname" placeholder="John Doe">
      </div>
      <div class="mb-3 col-5 col-md-5">
        <label for="email" class="control-label">{{ __('Email') }}</label>
        <input type="email" value='' class="form-control" id="email" placeholder="john.doe@cywise.io">
      </div>
      <div class="mb-3 col-2 col-md-2 d-flex align-items-end">
        <button type="button"
                onclick="createInvitation()"
                class="form-control btn btn-xs btn-outline-success float-end">
          {{ __('Create Invitation') }}
        </button>
      </div>
    </div>
  </div>
  @if($invitations->isEmpty())
  <div class="card-body">
    <div class="row">
      <div class="col">
        {{ __('None.') }}
      </div>
    </div>
  </div>
  @else
  <div class="card-body p-0">
    <table class="table table-hover">
      <thead>
      <tr>
        <th>
          <i class="zmdi zmdi-long-arrow-down"></i>&nbsp;{{ __('Username') }}
        </th>
        <th>{{ __('Email') }}</th>
        <th></th>
      </tr>
      </thead>
      <tbody>
      @foreach($invitations->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE) as $invitation)
      <tr>
        <td>
          {{ $invitation->name }}
        </td>
        <td>
          <a href="mailto:{{ $invitation->email }}" target="_blank">
            {{ $invitation->email }}&nbsp;&nbsp;<i class="zmdi zmdi-open-in-new"></i>
          </a>
        </td>
        <td>
          <button class="btn btn-link p-0" onclick="copyInvitationToClipboard('{{ $invitation->id }}')">
            {{ __('copy invitation') }}
            <input type="text" class="invisible"
                   style="height: 0.5rem; width: 2rem; padding: 0;"
                   id="__invitation_link_{{ $invitation->id }}"
                   value="{{ route('appshell.public.invitation.show', $invitation->hash) }}"
          </button>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
<script>

  function toggleForm() {
    document.getElementById('form-invitation').classList.toggle('d-none');
  }

  function copyInvitationToClipboard(id) {
    const element = document.getElementById("__invitation_link_" + id);
    element.classList.remove('invisible');
    element.select();
    element.setSelectionRange(0, 99999); /* For mobile devices */
    document.execCommand('copy');
    element.classList.add('invisible');
    toaster.toastSuccess("{{ __('Text successfully copied to clipboard.') }}");
  }

  function createInvitation() {

    const fullname = document.querySelector('#fullname').value;
    const email = document.querySelector('#email').value;

    axios.post("{{ route('ynh.invitations.create') }}", {
      fullname: fullname, email: email,
    }).then(function (response) {
      if (response.data.success) {
        toaster.toastSuccess(response.data.success);
        toggleForm();
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    }).catch(error => toaster.toastAxiosError(error));
  }

</script>
@endif