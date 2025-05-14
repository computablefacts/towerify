@extends('layouts.app')

@section('content')
<style>
  .requirement_met {
    color: green;
  }

  .requirement_not_met {
    color: red;
  }
</style>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">{{ $reason ?? __('Reset Password') }}</h5>
          <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3 row">
              <label for="email" class="col-md-4 col-form-label text-end">
                {{ __('E-Mail Address') }}
              </label>
              <div class="col-md-6">
                <input id="email"
                       type="email"
                       class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       name="email"
                       value="{{ $email ?? old('email') }}"
                       tabindex="-1"
                       readonly>
                @if ($errors->has('email'))
                <div class="form-text" role="alert">
                  {{ $errors->first('email') }}
                </div>
                @endif
              </div>
            </div>
            <div class="mb-3 row">
              <label for="password" class="col-md-4 col-form-label text-end">
                {{ __('Password') }}
              </label>
              <div class="col-md-6">
                <input id="password"
                       type="password"
                       class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                       name="password"
                       required
                       autofocus>
                @if ($errors->has('password'))
                <div class="form-text" role="alert">
                  {{ $errors->first('password') }}
                </div>
                @endif
              </div>
            </div>
            <div class="mb-3 row">
              <label for="password-confirm" class="col-md-4 col-form-label text-end">
                {{ __('Confirm Password') }}
              </label>
              <div class="col-md-6">
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
              </div>
            </div>
            <div class="mb-3 row">
              <label class="col-md-4 text-end">
                {{ __('Password requirements') }}
              </label>
              <div class="col-md-6">
                <ul>
                  @foreach ($passwordRequirements as $rule => $definition)
                    <li id="{{ $rule }}">{{ $definition['text'] }}</li>
                  @endforeach
                </ul>
              </div>
            </div>
            <div class="mb-3 row mb-0">
              <div class="col-md-6 offset-md-4">
                <button type="submit" class="btn btn-primary">
                  {{ $action ?? __('Reset Password') }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  const passwordField = document.getElementById("password");
  let requirementElement = null;

  passwordField.onkeyup = function () {
    @foreach ($passwordRequirements as $rule => $definition)
        @if(key_exists('condition', $definition))
        requirementElement = document.getElementById("{{ $rule }}");
    if ({!! $definition['condition'] !!}) {
      requirementElement.classList.remove("requirement_not_met");
      requirementElement.classList.add("requirement_met");
    } else {
      requirementElement.classList.remove("requirement_met");
      requirementElement.classList.add("requirement_not_met");
    }
    @endif
    @endforeach
  }
</script>
@endsection
