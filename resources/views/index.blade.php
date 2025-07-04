@extends('layouts.app')

@section('content')
<div class="container">
  @if(!$tab || $tab === 'my-apps')
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
  @if($tab === 'orders')
  <x-orders/>
  @endif
  @if($tab === 'invitations')
  <x-invitations/>
  @endif
  @if($tab === 'conversations')
  <x-conversations :currentPage="request()->input('page') ? request()->input('page') : 1"/>
  @endif
  @if($tab === 'interdependencies')
  <x-interdependencies/>
  @endif
  @if($tab === 'traces')
  <div class="row mb-2">
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
  @if($tab === 'ai_writer')
  @include('modules.cyber-buddy.ai-writer')
  @endif
</div>
@endsection
