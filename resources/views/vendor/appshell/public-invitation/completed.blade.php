@extends('appshell::layouts.public')

@section('title'){{ __('Just joined :appname', ['appname' => config('app.name')]) }}@stop

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
<div class="col-md-8 col-lg-6" xmlns:x-appshell="http://www.w3.org/1999/html">
  <x-appshell::card>
    <h2 class="text-center">{{ __('Welcome to :appname', ['appname' => config('app.name')]) }}</h2>
    <p class="text-center">
      {{ __('Congrats :name, you have successfully joined :appname.', ['name' => $user->name, 'appname' => config('app.name')]) }}
    </p>
    <x-slot:footer>
      <div class="d-grid">
        <x-appshell::button href="{{ route($appshell->routes['login']) }}" variant="primary">
          {{ __('Go to login') }}
        </x-appshell::button>
      </div>
    </x-slot:footer>
  </x-appshell::card>
</div>
@endsection
