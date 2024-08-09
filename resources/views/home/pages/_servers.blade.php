@extends('layouts.app')

@section('breadcrumbs')
<nav class="breadcrumb mb-0 pt-0 pb-0 ps-0">
  <a class="breadcrumb-item" href="/">Home</a>
  <a class="breadcrumb-item" href="/home?tab=servers">{{ __('Servers') }}</a>
  <span class="breadcrumb-item active">{{ $server->name }}</span>
  <span class="breadcrumb-menu"></span>
</nav>
@endsection

@section('content')
<style>
  .nav-link.active {
    border-bottom: 2px solid #becdcf;
  }
</style>
<div class="container">
  <ul class="nav justify-content-end mb-4">
    <li class="nav-item">
      <a class="nav-link {{ !$tab || $tab === 'settings' ? 'active' : '' }}"
         href="{{ route('ynh.servers.edit', $server) }}?tab=settings">
        {{ __('Settings') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'backups' ? 'active' : '' }}"
         href="{{ route('ynh.servers.edit', $server) }}?tab=backups">
        {{ __('Backups') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'domains' ? 'active' : '' }}"
         href="{{ route('ynh.servers.edit', $server) }}?tab=domains">
        {{ __('Domains') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'applications' ? 'active' : '' }}"
         href="{{ route('ynh.servers.edit', $server) }}?tab=applications">
        {{ __('Applications') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'traces' ? 'active' : '' }}"
         href="{{ route('ynh.servers.edit', $server) }}?tab=traces">
        {{ __('Traces') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'interdependencies' ? 'active' : '' }}"
         href="{{ route('ynh.servers.edit', $server) }}?tab=interdependencies">
        {{ __('Interdependencies') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'resources_usage' ? 'active' : '' }}"
         href="{{ route('ynh.servers.edit', $server) }}?tab=resources_usage">
        {{ __('Resources Usage') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'security' ? 'active' : '' }}"
         href="{{ route('ynh.servers.edit', $server) }}?tab=security&limit=40">
        {{ __('Security') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'users' ? 'active' : '' }}"
         href="{{ route('ynh.servers.edit', $server) }}?tab=users">
        {{ __('Users') }}
      </a>
    </li>
    @if(Auth::user()->canManageServers())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'shell' ? 'active' : '' }}"
         href="{{ route('ynh.servers.edit', $server) }}?tab=shell">
        {{ __('Shell') }}
      </a>
    </li>
    @endif
  </ul>
  @if(!$tab || $tab === 'settings')
  @include('home.forms._servers', [ 'server' => $server ])
  @endif
  @if($tab === 'backups')
  @include('home.cards._backups', [ 'backups' => $server->backups ])
  @endif
  @if($tab === 'traces')
  <div class="row">
    <div class="col-12">
      @include('home.cards._pending_actions', [ 'pendingActions' => $server->pendingActions() ])
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      @include('home.cards._traces', [ 'traces' => $server->latestTraces() ])
    </div>
  </div>
  <script>
    setTimeout(() => window.location.reload(), 15000);
  </script>
  @endif
  @if(Auth::user()->canManageServers() && $tab === 'shell')
  @include('home.cards._shell', [])
  @endif
  @if($tab === 'domains')
  @include('home.cards._domains', [ 'domains' => $server->domains ])
  @endif
  @if($tab === 'applications')
  <div class="row mb-4">
    <div class="col-12">
      @include('home.cards._applications', [ 'applications' => $server->applications ])
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      @include('home.cards._applications_ready_to_be_deployed', [ 'orders' => $orders ])
    </div>
  </div>
  @endif
  @if($tab === 'interdependencies')
  @include('home.cards._interdependencies')
  @endif
  @if($tab === 'resources_usage')
  @include('home.cards._resources_usage')
  @endif
  @if($tab === 'security')
  @include('home.cards._security')
  @endif
  @if($tab === 'users')
  @include('home.cards._users', [ 'users' => $server->users ])
  @endif
</div>
@endsection