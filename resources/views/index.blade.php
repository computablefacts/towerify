@extends('layouts.app')

@section('content')
<div class="container">
  @if(!$tab || $tab === 'overview')
  <x-overview/>
  @endif
  @if($tab === 'my-apps')
  <x-my-applications/>
  @endif
  @if($tab === 'servers')
  <x-servers :type="$servers_type"/>
  @endif
  @if($tab === 'backups')
  <x-backups/>
  @endif
  @if($tab === 'domains')
  <x-domains/>
  @endif
  @if($tab === 'applications')
  <x-applications/>
  @endif
  @if($tab === 'orders')
  <x-orders/>
  @endif
  @if($tab === 'users')
  <x-users/>
  @endif
  @if($tab === 'invitations')
  <x-invitations/>
  @endif
  @if($tab === 'security_rules')
  <x-osquery-rules/>
  @endif
  @if($tab === 'knowledge_base')
  <x-knowledge-base/>
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
  @if($tab === 'ama')
  @include('modules.cyber-buddy.widget')
  @endif
</div>
@endsection