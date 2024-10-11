@extends('layouts.app')
@section('breadcrumbs')
<nav class="breadcrumb mb-0 pt-0 pb-0 ps-0">
  <a class="breadcrumb-item" href="/?tab=overview">{{ __('Home') }}</a>
  @if($tab === 'overview')
  <span class="breadcrumb-item active">{{ __('Overview') }}</span>
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
  @elseif($tab === 'security_rules')
  <span class="breadcrumb-item active">{{ __('Security Rules') }}</span>
  @elseif($tab === 'knowledge_base')
  <span class="breadcrumb-item active">{{ __('Knowledge Base') }}</span>
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
      <a class="nav-link {{ !$tab || $tab === 'overview' ? 'active' : '' }}"
         href="/home?tab=overview">
        {{ __('Overview') }}
      </a>
    </li>
    @if(!Auth::user()->isCywiseUser())
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
    @if(Auth::user()->canListServers() && !Auth::user()->isCywiseUser())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'backups' ? 'active' : '' }}"
         href="/home?tab=backups">
        {{ __('Backups') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListServers() && !Auth::user()->isCywiseUser())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'domains' ? 'active' : '' }}"
         href="/home?tab=domains">
        {{ __('Domains') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListServers() && !Auth::user()->isCywiseUser())
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'applications' ? 'active' : '' }}"
         href="/home?tab=applications">
        {{ __('Applications') }}
      </a>
    </li>
    @endif
    @if(Auth::user()->canListServers() && !Auth::user()->isCywiseUser())
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
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'security_rules' ? 'active' : '' }}"
         href="/home?tab=security_rules">
        {{ __('Security Rules') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ $tab === 'knowledge_base' ? 'active' : '' }}"
         href="/home?tab=knowledge_base">
        {{ __('Knowledge Base') }}
      </a>
    </li>
    @if(Auth::user()->canListOrders() && !Auth::user()->isCywiseUser())
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
  @if(!$tab || $tab === 'overview')
  @include('home.cards._overview')
  @endif
  @if($tab === 'my-apps')
  <x-my-applications/>
  @endif
  @if($tab === 'servers')
  <x-servers/>
  @endif
  @if($tab === 'backups')
  <x-backups/>
  @endif
  @if($tab === 'domains')
  <x-domains/>
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
  @if($tab === 'security_rules')
  @include('home.cards._osquery_rules', [ 'rules' => $security_rules ])
  @endif
  @if($tab === 'knowledge_base')
  @include('home.cards._knowledge_base', [ 'files' => $knowledge_base ])
  @endif
  @if($tab === 'interdependencies')
  <x-interdependencies/>
  @endif
  @if($tab === 'traces')
  <div class="row">
    <div class="col-12">
      <x-pending-actions/>
    </div>
  </div>
  <div class="row">
    <div class="col-12">
      <x-traces/>
    </div>
  </div>
  <script>
    setTimeout(() => window.location.reload(), 15000);
  </script>
  @endif
</div>
@endsection
