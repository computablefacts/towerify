<?php

use function Laravel\Folio\{name};

name('ioc');
?>

<x-layouts.app>
    <iframe src="{{ route('iframes.ioc') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

