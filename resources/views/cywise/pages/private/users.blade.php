<?php

use function Laravel\Folio\{name};

name('users');
?>

<x-layouts.app>
    <iframe src="{{ route('iframes.users') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

