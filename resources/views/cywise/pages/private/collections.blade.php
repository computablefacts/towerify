<?php

use function Laravel\Folio\{name};

name('collections');
?>

<x-layouts.app>
    <iframe src="{{ route('iframes.collections') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

