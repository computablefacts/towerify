<x-app.sidebar-link href="{{ route('dashboard') }}"
                    icon="phosphor-house"
                    :active="Request::is('dashboard')">
  {{ __('Dashboard') }}
</x-app.sidebar-link>
<x-app.sidebar-dropdown text="{{ __('Timelines') }}"
                        icon="phosphor-stack"
                        id="projects_dropdown"
                        :active="(Request::is('timelines'))"
                        :open="(Request::is('vulnerabilities') || Request::is('leaks') || Request::is('ioc') || Request::is('assets') || Request::is('events') || Request::is('conversations') || Request::is('notes-and-memos')) ? '1' : '0'">
  <x-app.sidebar-link
    href="{{ route('vulnerabilities') }}"
    icon="phosphor-cube"
    :active="(Request::is('vulnerabilities'))">
    {{ __('Vulnerabilities') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('leaks') }}"
    icon="phosphor-cube"
    :active="(Request::is('leaks'))">
    {{ __('Leaks') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('ioc') }}"
    icon="phosphor-cube"
    :active="(Request::is('ioc'))">
    {{ __('Indicators of Compromise') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('events') }}"
    icon="phosphor-cube"
    :active="(Request::is('events'))">
    {{ __('Events') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('assets') }}"
    icon="phosphor-cube"
    :active="(Request::is('assets'))">
    {{ __('Assets') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('conversations') }}"
    icon="phosphor-cube"
    :active="(Request::is('conversations'))">
    {{ __('Conversations') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('notes-and-memos') }}"
    icon="phosphor-cube"
    :active="(Request::is('notes-and-memos'))">
    {{ __('Notes & Memos') }}
  </x-app.sidebar-link>
</x-app.sidebar-dropdown>
