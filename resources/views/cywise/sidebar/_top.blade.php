<x-app.sidebar-link href="{{ route('dashboard') }}"
                    icon="phosphor-house"
                    :active="Request::is('dashboard')">
  {{ __('Dashboard') }}
</x-app.sidebar-link>
<x-app.sidebar-dropdown text="{{ __('Timelines') }}"
                        icon="phosphor-stack"
                        id="timelines_dropdown"
                        :active="(Request::is('vulnerabilities') || Request::is('leaks') || Request::is('ioc') || Request::is('assets') || Request::is('events') || Request::is('conversations') || Request::is('notes-and-memos') || Request::is('cyberbuddy') || Request::is('cyberscribe'))"
                        :open="(Request::is('vulnerabilities') || Request::is('leaks') || Request::is('ioc') || Request::is('assets') || Request::is('events') || Request::is('conversations') || Request::is('notes-and-memos') || Request::is('cyberbuddy') || Request::is('cyberscribe')) ? '1' : '0'">
  <x-app.sidebar-link
    href="{{ route('vulnerabilities') }}"
    icon="phosphor-warning-circle"
    :active="(Request::is('vulnerabilities'))">
    {{ __('Vulnerabilities') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('leaks') }}"
    icon="phosphor-user"
    :active="(Request::is('leaks'))">
    {{ __('Leaks') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('ioc') }}"
    icon="phosphor-magnifying-glass"
    :active="(Request::is('ioc'))">
    {{ __('Indicators of Compromise') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('events') }}"
    icon="phosphor-flow-arrow"
    :active="(Request::is('events'))">
    {{ __('Events') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('assets') }}"
    icon="phosphor-globe"
    :active="(Request::is('assets'))">
    {{ __('Assets') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('conversations') }}"
    icon="phosphor-chats"
    :active="(Request::is('conversations'))">
    {{ __('Conversations') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link
    href="{{ route('notes-and-memos') }}"
    icon="phosphor-pencil-simple"
    :active="(Request::is('notes-and-memos'))">
    {{ __('Notes & Memos') }}
  </x-app.sidebar-link>
</x-app.sidebar-dropdown>
<x-app.sidebar-link href="{{ route('cyberbuddy') }}"
                    icon="phosphor-robot"
                    :active="Request::is('cyberbuddy')">
  {{ __('CyberBuddy') }}
</x-app.sidebar-link>
<x-app.sidebar-link href="{{ route('cyberscribe') }}"
                    icon="phosphor-pencil-circle"
                    :active="Request::is('cyberscribe')">
  {{ __('CyberScribe') }}
</x-app.sidebar-link>
<x-app.sidebar-dropdown text="{{ __('Data Management') }}"
                        icon="phosphor-database"
                        id="datamanagement_dropdown"
                        :active="(Request::is('prompts'))"
                        :open="(Request::is('prompts')) ? '1' : '0'">
  <x-app.sidebar-link href="{{ route('prompts') }}"
                      icon="phosphor-files"
                      :active="Request::is('prompts')">
    {{ __('Prompts') }}
  </x-app.sidebar-link>
  <x-app.sidebar-link href="{{ route('collections') }}"
                      icon="phosphor-folders"
                      :active="Request::is('collections')">
    {{ __('Collections') }}
  </x-app.sidebar-link>
</x-app.sidebar-dropdown>
