<?php

use function Laravel\Folio\{name};

name('chunks');
?>

<x-layouts.app>
    <iframe src="{{ route('iframes.chunks') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

