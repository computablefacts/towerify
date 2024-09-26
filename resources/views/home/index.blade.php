@extends('layouts.app')
@section('breadcrumbs')
<nav class="breadcrumb mb-0 pt-0 pb-0 ps-0">
  <a class="breadcrumb-item" href="/?tab=summary">Home</a>
  @if($tab === 'summary')
  <span class="breadcrumb-item active">{{ __('Summary') }}</span>
  @elseif($tab === 'my-apps')
  <span class="breadcrumb-item active">{{ __('My Apps') }}</span>
  @elseif($tab === 'servers')
  <span class="breadcrumb-item active">{{ __('Servers') }}</span>
  @elseif($tab === 'backups')
  <span class="breadcrumb-item active">{{ __('Backups') }}</span>
  @elseif($tab === 'domains')
  <span class="breadcrumb-item active">{{ __('Domains') }}</span>
  @elseif($tab === 'applications')
  <span class="breadcrumb-item active">{{ __('Applications') }}</span>
  @elseif($tab === 'traces')
  <span class="breadcrumb-item active">{{ __('Traces') }}</span>
  @elseif($tab === 'interdependencies')
  <span class="breadcrumb-item active">{{ __('Interdependencies') }}</span>
  @elseif($tab === 'resources_usage')
  <span class="breadcrumb-item active">{{ __('Resources Usage') }}</span>
  @elseif($tab === 'security')
  <span class="breadcrumb-item active">{{ __('Security') }}</span>
  @elseif($tab === 'security_rules')
  <span class="breadcrumb-item active">{{ __('Security Rules') }}</span>
  @elseif($tab === 'orders')
  <span class="breadcrumb-item active">{{ __('Orders') }}</span>
  @elseif($tab === 'users')
  <span class="breadcrumb-item active">{{ __('Users') }}</span>
  @elseif($tab === 'invitations')
  <span class="breadcrumb-item active">{{ __('Invitations') }}</span>
  @endif
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
      <a class="nav-link {{ !$tab || $tab === 'summary' ? 'active' : '' }}"
         href="/home?tab=summary">
        {{ __('Summary') }}
      </a>
    </li>
    @if(!is_cywise())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'my-apps' ? 'active' : '' }}"
         href="/home?tab=my-apps">
        {{ __('My Apps') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListServers())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'servers' ? 'active' : '' }}"
         href="/home?tab=servers">
        {{ __('Servers') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListServers() && !is_cywise())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'backups' ? 'active' : '' }}"
         href="/home?tab=backups">
        {{ __('Backups') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListServers() && !is_cywise())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'domains' ? 'active' : '' }}"
         href="/home?tab=domains">
        {{ __('Domains') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListServers() && !is_cywise())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'applications' ? 'active' : '' }}"
         href="/home?tab=applications">
        {{ __('Applications') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListServers())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'traces' ? 'active' : '' }}"
         href="/home?tab=traces">
        {{ __('Traces') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListServers())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'interdependencies' ? 'active' : '' }}"
         href="/home?tab=interdependencies">
        {{ __('Interdependencies') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListServers())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'resources_usage' ? 'active' : '' }}"
         href="/home?tab=resources_usage">
        {{ __('Resources Usage') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListServers())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'security' ? 'active' : '' }}"
         href="/home?tab=security&limit=20">
        {{ __('Security') }}
      </a>
    </li>
    @endif
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'security_rules' ? 'active' : '' }}"
         href="/home?tab=security_rules">
        {{ __('Security Rules') }}
      </a>
    </li>
    @if(Auth::user()->canListOrders() && !is_cywise())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'orders' ? 'active' : '' }}"
         href="/home?tab=orders">
        {{ __('Orders') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListUsers())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'users' ? 'active' : '' }}"
         href="/home?tab=users">
        {{ __('Users') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListUsers())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'invitations' ? 'active' : '' }}"
         href="/home?tab=invitations">
        {{ __('Invitations') }}
      </a>
    </li>
    @endif
  </ul>
  @if(!$tab || $tab === 'summary')
  @include('home.cards._summary')
  @endif
  @if($tab === 'my-apps')
  @include('home.cards._my_apps')
  @endif
  @if($tab === 'servers')
  @include('home.cards._servers')
  @endif
  @if($tab === 'backups')
  @include('home.cards._towerify_backups')
  @endif
  @if($tab === 'domains')
  @include('home.cards._towerify_domains')
  @endif
  @if($tab === 'applications')
  @include('home.cards._towerify_applications')
  @endif
  @if($tab === 'orders')
  @include('home.cards._orders')
  @endif
  @if($tab === 'users')
  @include('home.cards._towerify_users')
  @endif
  @if($tab === 'invitations')
  @include('home.cards._towerify_invitations')
  @endif
  @if($tab === 'resources_usage')
  @include('home.cards._resources_usage')
  @endif
  @if($tab === 'security')
  @include('home.cards._security')
  @endif
  @if($tab === 'security_rules')
  @include('home.cards._osquery_rules', [ 'rules' => $security_rules ])
  @endif
  @if($tab === 'interdependencies')
  @include('home.cards._interdependencies')
  @endif
  @if($tab === 'traces')
  <div class="row">
    <div class="col-12">
      @include('home.cards._pending_actions')
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      @include('home.cards._traces')
    </div>
  </div>
  <script>
    setTimeout(() => window.location.reload(), 15000);
  </script>
  @endif
</div>
@endsection
