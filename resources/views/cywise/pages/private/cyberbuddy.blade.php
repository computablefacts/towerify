<?php

use function Laravel\Folio\{name};

name('cyberbuddy');
?>

<x-layouts.app>
    <iframe src="{{ route('iframes.cyberbuddy') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

