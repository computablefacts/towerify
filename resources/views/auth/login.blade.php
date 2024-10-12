@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">{{ __('Login') }}</h5>
          <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="row mb-3">
              <label for="email" class="col-md-4 col-form-label text-end">
                {{ __('E-Mail Address') }}
              </label>
              <div class="col-md-6">
                <input id="email"
                       type="email"
                       class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       autofocus>
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
              <div class="col-md-6 offset-md-4">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    name="remember"
                    id="remember"
                    {{ old('remember') ? 'checked' : '' }}>
                  <label class="form-check-label" for="remember">
                    {{ __('Remember Me') }}
                  </label>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-8 offset-md-4">
                <button type="submit" class="btn btn-primary">
                  {{ __('Login') }}
                </button>
                <a class="btn btn-subtle" href="{{ route('password.request') }}">
                  {{ __('Forgot Your Password?') }}
                </a>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
