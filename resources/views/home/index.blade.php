@extends('layouts.app')
@section('breadcrumbs')
<nav class="breadcrumb mb-0 pt-0 pb-0 ps-0">
  <a class="breadcrumb-item" href="/">Home</a>
  @if($tab === 'my-apps')
  <span class="breadcrumb-item active">{{ __('My Apps') }}</span>
  @elseif($tab === 'servers')
  <span class="breadcrumb-item active">{{ __('Servers') }}</span>
  @elseif($tab === 'interdependencies')
  <span class="breadcrumb-item active">{{ __('Interdependencies') }}</span>
  @elseif($tab === 'resources_usage')
  <span class="breadcrumb-item active">{{ __('Resources Usage') }}</span>
  @elseif($tab === 'security')
  <span class="breadcrumb-item active">{{ __('Security') }}</span>
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
      <a class="nav-link {{ !$tab || $tab === 'my-apps' ? 'active' : '' }}"
         href="/home?tab=my-apps">
        {{ __('My Apps') }}
      </a>
    </li>
    @if(Auth::user()->canListServers())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'servers' ? 'active' : '' }}"
         href="/home?tab=servers">
        {{ __('Servers') }}
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
         href="/home?tab=security">
        {{ __('Security') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListOrders())
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
  @if(!$tab || $tab === 'my-apps')
  @include('home.cards._my_apps')
  @endif
  @if($tab === 'servers')
  @include('home.cards._servers')
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
  @if($tab === 'interdependencies')
  @include('home.cards._interdependencies')
  @endif
</div>
@endsection
