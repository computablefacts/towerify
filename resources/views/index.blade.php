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
  @if($tab === 'collections')
  <x-collections :currentPage="request()->input('page') ? request()->input('page') : 1"/>
  @endif
  @if($tab === 'documents')
  <x-documents :collection="request()->input('collection') ? request()->input('collection') : ''"
               :currentPage="request()->input('page') ? request()->input('page') : 1"/>
  @endif
  @if($tab === 'tables')
  <x-tables/>
  @endif
  @if($tab === 'tables-add')
  <x-tables-add :step="request()->input('step') ? request()->input('step') : ''"/>
  @endif
  @if($tab === 'chunks')
  <x-chunks :collection="request()->input('collection') ? request()->input('collection') : ''"
            :file="request()->input('file') ? request()->input('file') : ''"
            :currentPage="request()->input('page') ? request()->input('page') : 1"/>
  @endif
  @if($tab === 'conversations')
  <x-conversations :currentPage="request()->input('page') ? request()->input('page') : 1"/>
  @endif
  @if($tab === 'prompts')
  <x-prompts :currentPage="request()->input('page') ? request()->input('page') : 1"/>
  @endif
  @if($tab === 'sca')
  <x-sca :policy="request()->input('policy') ? request()->input('policy') : ''"
         :framework="request()->input('framework') ? request()->input('framework') : ''"
         :search="request()->input('search') ? request()->input('search') : ''"
         :check="request()->input('check') ? request()->input('check') : ''"/>
  @endif
  @if($tab === 'frameworks')
  <x-frameworks :search="request()->input('search') ? request()->input('search') : ''"/>
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
  @if($tab === 'timeline')
  <x-timeline/>
  @endif
</div>
@endsection
