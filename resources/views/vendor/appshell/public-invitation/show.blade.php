@extends('appshell::layouts.public')

@section('title'){{ __('Invitation to join :appname', ['appname' => $appname]) }}@stop

@section('content')
<style>

  :root {
    --bs-heading-color: #00264b;
    --bs-secondary-bg: rgb(255 228 176 / 97%);
  }

  .bg-dark {
    background-color: #00264b !important;
  }

</style>
<div class="col-md-8 col-lg-6">
  @if($invitation->isStillValid())
  <x-appshell::card tag="form" method="POST" action="{{ route('appshell.public.invitation.accept') }}">
    @csrf
    <h2 class="text-center">{{ __('Join :appname', ['appname' => $appname]) }}!</h2>
    <p class="text-center">
      {{ __('You’ve been invited to create an account and join :appname.', ['appname' => $appname]) }}<br>
      {{ __('To proceed, enter your details below.') }}
    </p>
    <!-- <p class="text-center" style="color:red">
      {{ __('Please, only use uppercase/lowercase letters and digits for your password.') }}
    </p> -->
    <hr>
    <input type="hidden" name="hash" value="{{ $invitation->hash }}"/>
    @if ($errors->has('hash'))
    <x-appshell::alert variant="danger">
      {{ __('The invitation code you\'re trying to use is wrong! Looks like a forged one.') }}
    </x-appshell::alert>
    @endif
    <div class="mb-4">
      <input id="name" type="text"
             class="form-control form-control-lg{{ $errors->has('name') ? ' is-invalid' : '' }}"
             name="name" value="{{ $invitation->name }}"
             placeholder="{{ __('Your name (eg. John Smith)') }}" required autofocus/>
      @if ($errors->has('name'))
      <span class="invalid-feedback">
          <strong>{{ $errors->first('name') }}</strong>
      </span>
      @endif
    </div>
    <div class="mb-4">
      <input id="email" type="email" disabled="disabled"
             class="form-control form-control-sm{{ $errors->has('email') ? ' is-invalid' : '' }}"
             name="email" value="{{ $invitation->email }}"
             placeholder="{{ __('E-Mail Address') }}"/>
      @if ($errors->has('email'))
      <span class="invalid-feedback">
          <strong>{{ $errors->first('email') }}</strong>
      </span>
      @endif
    </div>
    <div style="display: none">
      {{-- This block is to trick browser's autocompletion detection which is extremely pushy --}}
        <?php $fakeElementId = 'ooh' . uniqid(); ?>
      <input type="text" name="{{ $fakeElementId }}" id="{{ $fakeElementId }}" value="{{ uniqid() }}"/>
    </div>
    <div class="mb-4 row">
      <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>
      <div class="col-md-6">
        <input id="password" placeholder="{{ __('uppercase/lowercase letters and digits only') }}" type="password"
               class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
        @if ($errors->has('password'))
        <span class="invalid-feedback">
            <strong>{{ $errors->first('password') }}</strong>
        </span>
        @endif
      </div>
    </div>
    <div class="mb-4 row">
      <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>
      <div class="col-md-6">
        <input id="password-confirm" placeholder="{{ __('uppercase/lowercase letters and digits only') }}"
               type="password" class="form-control" name="password_confirmation" required>
      </div>
    </div>
    <x-slot:footer>
      <div class="d-grid">
        <x-appshell::button variant="primary">
          {{ __('Create account and join') }}
        </x-appshell::button>
      </div>
    </x-slot:footer>
  </x-appshell::card>
  @else
  @include('appshell::public-invitation._invalid')
  @endif
</div>
@endsection

@push('scripts')
<script>
  @if ($invitation->isStillValid()) {
    document.addEventListener("DOMContentLoaded", function () {
      setTimeout(function () {
        document.getElementById('{{ $fakeElementId }}').remove();
      }, 470);
    });
  }
  @endif
</script>
@endpush
