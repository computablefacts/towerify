@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">{{ __('Reset Password') }}</h5>
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
                       required
                       autofocus>
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
                       required>
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
            <div class="mb-3 row mb-0">
              <div class="col-md-6 offset-md-4">
                <button type="submit" class="btn btn-primary">
                  {{ __('Reset Password') }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
