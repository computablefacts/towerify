@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">{{ __('Register') }}</h5>
          <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="row mb-3">
              <label for="name" class="col-md-4 col-form-label text-end">
                {{ __('Name') }}
              </label>
              <div class="col-md-6">
                <input id="name"
                       placeholder="John Doe"
                       type="text"
                       class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                       name="name"
                       value="{{ old('name') }}"
                       required
                       autofocus>
                @if ($errors->has('name'))
                <div class="form-text" role="alert">
                  {{ $errors->first('name') }}
                </div>
                @endif
              </div>
            </div>
            <div class="row mb-3">
              <label for="email" class="col-md-4 col-form-label text-end">
                {{ __('E-Mail Address') }}
              </label>
              <div class="col-md-6">
                <input id="email"
                       placeholder="j.doe@example.com"
                       type="email"
                       class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       name="email"
                       value="{{ old('email') }}"
                       required>
                @if ($errors->has('email'))
                <div class="form-text" role="alert">
                  {{ $errors->first('email') }}
                </div>
                @endif
              </div>
            </div>
            <div class="row mb-3">
              <label for="password" class="col-md-4 col-form-label text-end">
                {{ __('Password') }}
              </label>
              <div class="col-md-6">
                <input id="password"
                       placeholder="{{ __('uppercase/lowercase letters and digits only') }}"
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
            <div class="row mb-3">
              <label for="password-confirm" class="col-md-4 col-form-label text-end">
                {{ __('Confirm Password') }}
              </label>
              <div class="col-md-6">
                <input id="password-confirm"
                       placeholder="{{ __('uppercase/lowercase letters and digits only') }}"
                       type="password"
                       class="form-control"
                       name="password_confirmation"
                       required>
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6 offset-md-4">
                <div class="form-check">
                  <input class="form-control {{ $errors->has('terms') ? 'is-invalid' : '' }} form-check-input"
                         type="checkbox"
                         name="terms"
                         id="terms"
                         required>
                  <label class="form-check-label" for="terms">
                    <a href="/terms" target="_blank">
                      {{ __('I agree with the terms and conditions') }}
                    </a>
                  </label>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 offset-md-4">
                <button type="submit" class="btn btn-primary">
                  {{ __('Register') }}
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
