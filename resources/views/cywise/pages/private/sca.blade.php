<?php

use function Laravel\Folio\{name};

name('sca');
?>

<x-layouts.app>
  <iframe src="{{ route('iframes.sca') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

