<?php

use function Laravel\Folio\{name};

name('events');
?>

<x-layouts.app>
    <iframe src="{{ route('iframes.events') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

