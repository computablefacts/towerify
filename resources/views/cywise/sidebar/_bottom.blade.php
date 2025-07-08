<x-app.sidebar-link href="{{ route('documentation') }}"
                    icon="phosphor-book-bookmark-duotone"
                    :active="Request::is('private/documentation')">
    {{ __('Documentation') }}
</x-app.sidebar-link>
<x-app.sidebar-link :href="route('changelogs')"
                    icon="phosphor-book-open-text-duotone"
                    :active="Request::is('changelog') || Request::is('changelog/*')">
    {{ __('Changelog') }}
</x-app.sidebar-link>
