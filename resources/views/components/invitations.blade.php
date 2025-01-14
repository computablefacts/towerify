@if(Auth::user()->canManageUsers())
<div class="card mb-3">
  <div class="card-body p-3">
    <h6 class="card-title">{{ __('Import a CSV file of your users') }}</h6>
    <div class="card mb-3" style="background-color:#fff3cd;">
      <div class="card-body p-2">
        <div class="row">
          <div class="col">
            {{ __('Your CSV file should contain the following columns: NAME and EMAIL') }}
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div id="files"></div>
      </div>
      <div class="col-3">
        <div id="submit"></div>
      </div>
    </div>
  </div>
</div>
@endif
@if(Auth::user()->canListUsers())
<div class="card">
  @if(Auth::user()->canManageUsers())
  <div class="card-header d-flex flex-row">
    <div class="align-items-end">
      <h6 class="m-0">
        <a onclick="toggleForm()">
          {{ __('+ new') }}
        </a>
      </h6>
    </div>
  </div>
  @endif
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
                onclick="createInvitation(event)"
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
    <table class="table table-hover no-bottom-margin">
      <thead>
      <tr>
        <th>{{ __('Username') }}</th>
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
            {{ $invitation->email }}
          </a>
        </td>
        <td>
          <button class="btn btn-link p-0" onclick="copyInvitationToClipboard('{{ $invitation->id }}', event)">
            {{ __('copy invitation') }}
            <input type="text" class="invisible"
                   style="height: 0.5rem; width: 2rem; padding: 0;"
                   id="__invitation_link_{{ $invitation->id }}"
                   value="{{ route('appshell.public.invitation.show', $invitation->hash) }}"
          </button>
          <button class="btn btn-link p-0" onclick="sendInvitation('{{ $invitation->id }}', event)">
            {{ __('send invitation') }}
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

  let files = null;

  const elSubmit = new com.computablefacts.blueprintjs.MinimalButton(document.getElementById('submit'),
    "{{ __('Submit') }}");
  elSubmit.disabled = true;
  elSubmit.onClick(() => {

    elSubmit.loading = true;
    elSubmit.disabled = true;

    for (let i = 0; i < files.length; i++) {

      const file = files[i];
      const reader = new FileReader();

      reader.onload = (event) => {

        const content = event.target.result;
        const lines = content.split('\n');
        const rows = lines.map(line => line.trim().split(';'));
        const header = rows.shift();
        const nameIndex = header.findIndex(
          column => column.trim().toUpperCase() === 'NOM' || column.trim().toUpperCase() === 'NAME');
        const emailIndex = header.findIndex(column => column.trim().toUpperCase() === 'EMAIL');

        if (nameIndex === -1) {
          toaster.toastError("{{ __('NAME column not found in CSV file.') }}");
          return;
        }
        if (emailIndex === -1) {
          toaster.toastError("{{ __('EMAIL column not found in CSV file.') }}");
          return;
        }

        const users = [];

        rows.filter((line) => line.length === header.length).forEach((line) => {

          const emailRegex = /(?:\s*|\b)[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}(?:\s*|\b)/;
          const name = line[nameIndex];
          let email = line[emailIndex].match(emailRegex);

          if (!email) {
            toaster.toastError(line[emailIndex] + " is an invalid email address.");
          } else {
            email = email[0];
            users.push({name: name, email: email});
          }
        });

        axios.post("{{ route('ynh.invitations.create') }}", {
          users: users,
        }).then(function (response) {
          if (response.data.success) {
            toaster.toastSuccess(response.data.success);
          } else if (response.data.error) {
            toaster.toastError(response.data.error);
          } else {
            console.log(response.data);
          }
        })
        .catch(error => toaster.toastAxiosError(error))
        .finally(() => {
          elSubmit.loading = false;
          elSubmit.disabled = false;
        });
      };
      reader.onerror = (event) => {
        toaster.toastError("Error reading file: " + event.target.error);
      };
      reader.readAsText(file);
    }
  });

  const elFile = new com.computablefacts.blueprintjs.MinimalFileInput(document.getElementById('files'), true);
  elFile.onSelectionChange(items => {
    files = items;
    elSubmit.disabled = !files;
  });
  elFile.buttonText = "{{ __('Browse') }}";

  function toggleForm() {
    document.getElementById('form-invitation').classList.toggle('d-none');
  }

  function copyInvitationToClipboard(id, event) {

    event.preventDefault();
    event.stopPropagation();

    const element = document.getElementById("__invitation_link_" + id);
    element.classList.remove('invisible');
    element.select();
    element.setSelectionRange(0, 99999); /* For mobile devices */
    document.execCommand('copy');
    element.classList.add('invisible');
    toaster.toastSuccess("{{ __('Text successfully copied to clipboard.') }}");
  }

  function createInvitation(event) {

    event.preventDefault();
    event.stopPropagation();

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

  function sendInvitation(id, event) {

    event.preventDefault();
    event.stopPropagation();

    axios.post("{{ route('ynh.invitations.send') }}", {
      id: id,
    }).then(function (response) {
      if (response.data.success) {
        toaster.toastSuccess(response.data.success);
      } else if (response.data.error) {
        toaster.toastError(response.data.error);
      } else {
        console.log(response.data);
      }
    }).catch(error => toaster.toastAxiosError(error));
  }

</script>
@endif