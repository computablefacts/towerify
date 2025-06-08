<x-app.sidebar-link href="{{ route('v2.private.rpc.docs') }}"
                    target="_blank"
                    icon="phosphor-book-bookmark-duotone"
                    active="false">
    Documentation
</x-app.sidebar-link>
<!--
<x-app.sidebar-link href="https://devdojo.com/questions"
                    target="_blank"
                    icon="phosphor-chat-duotone"
                    active="false">
    Questions
</x-app.sidebar-link>
-->
<x-app.sidebar-link :href="route('changelogs')"
                    icon="phosphor-book-open-text-duotone"
                    :active="Request::is('changelog') || Request::is('changelog/*')">
    Changelog
</x-app.sidebar-link>
