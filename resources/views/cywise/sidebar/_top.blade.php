<x-app.sidebar-link href="{{ route('dashboard') }}"
                    icon="phosphor-house"
                    :active="Request::is('dashboard')">
    Dashboard
</x-app.sidebar-link>
<x-app.sidebar-dropdown text="Projects"
                        icon="phosphor-stack"
                        id="projects_dropdown"
                        :active="(Request::is('projects'))"
                        :open="(Request::is('project_a') || Request::is('project_b') || Request::is('project_c')) ? '1' : '0'">
    <x-app.sidebar-link
        onclick="event.preventDefault(); new FilamentNotification().title('Modify this button inside of sidebar.blade.php').send()"
        icon="phosphor-cube"
        :active="(Request::is('project_a'))">
        Project A
    </x-app.sidebar-link>
    <x-app.sidebar-link
        onclick="event.preventDefault(); new FilamentNotification().title('Modify this button inside of sidebar.blade.php').send()"
        icon="phosphor-cube"
        :active="(Request::is('project_b'))">
        Project B
    </x-app.sidebar-link>
    <x-app.sidebar-link
        onclick="event.preventDefault(); new FilamentNotification().title('Modify this button inside of sidebar.blade.php').send()"
        icon="phosphor-cube"
        :active="(Request::is('project_c'))">
        Project C
    </x-app.sidebar-link>
</x-app.sidebar-dropdown>
<x-app.sidebar-link href="{{ route('stories') }}"
                    icon="phosphor-pencil-line"
                    :active="Request::is('private/stories')">
    Stories
</x-app.sidebar-link>
<x-app.sidebar-link href="{{ route('users') }}"
                    icon="phosphor-users"
                    :active="Request::is('private/users')">
    Users
</x-app.sidebar-link>
