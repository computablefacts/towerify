<?php

use function Laravel\Folio\{name};

name('table');
?>

<x-layouts.app>
  <iframe src="{{ route('iframes.table') }}" class="w-full h-screen border-0"></iframe>
</x-layouts.app>

