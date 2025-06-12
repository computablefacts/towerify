<?php

use function Laravel\Folio\{name};

name('conversations');
?>

<x-layouts.app>
    <iframe src="{{ route('iframes.conversations') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

