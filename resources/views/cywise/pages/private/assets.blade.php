<?php

use function Laravel\Folio\{name};

name('assets');
?>

<x-layouts.app>
    <iframe src="{{ route('iframes.assets') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

