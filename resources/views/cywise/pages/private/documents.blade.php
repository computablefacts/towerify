<?php

use function Laravel\Folio\{name};

name('documents');
?>

<x-layouts.app>
    <iframe src="{{ route('iframes.documents') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

