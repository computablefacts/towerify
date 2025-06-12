<?php

use function Laravel\Folio\{name};

name('vulnerabilities');
?>

<x-layouts.app>
    <iframe src="{{ route('iframes.vulnerabilities') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

