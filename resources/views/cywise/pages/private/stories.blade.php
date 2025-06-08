<?php

use function Laravel\Folio\{name};

name('stories');
?>

<x-layouts.app>
    <iframe src="{{ route('iframes.stories') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

