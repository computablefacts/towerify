<?php

use function Laravel\Folio\{name};

name('tpe-pme');
?>

<x-layouts.marketing>
  <x-container class="py-0">
    <div class="relative isolate overflow-visible bg-white">
      <svg class="absolute inset-0 -z-10 size-full stroke-gray-200 [mask-image:radial-gradient(100%_100%_at_top_right,white,transparent)]" aria-hidden="true">
        <defs>
          <pattern id="0787a7c5-978c-4f66-83c7-11c213f99cb7" width="200" height="200" x="50%" y="-1" patternUnits="userSpaceOnUse">
            <path d="M.5 200V.5H200" fill="none" />
          </pattern>
        </defs>
        <rect width="100%" height="100%" stroke-width="0" fill="url(#0787a7c5-978c-4f66-83c7-11c213f99cb7)" />
      </svg>
      <div class="mx-auto max-w-7xl px-6 pb-24 pt-10 sm:pb-32 lg:flex lg:px-8 lg:py-4">
        <div class="mx-auto max-w-2xl lg:mx-0 lg:shrink-0 lg:pt-8">
          <div class="mt-0 sm:mt-0 lg:mt-0">
            <a href="{{ route('changelogs') }}" class="inline-flex space-x-6">
              <span class="rounded-full bg-indigo-600/10 px-3 py-1 text-sm/6 font-semibold text-indigo-600 ring-1 ring-inset ring-indigo-600/10">
                {{ __('What\'s new') }}
              </span>
            </a>
          </div>
          <h1 class="mt-10 text-pretty text-5xl font-semibold tracking-tight text-gray-900 sm:text-7xl">
            {{ __('TPE_PME_TITLE') }}
          </h1>
          <p class="mt-8 text-pretty text-lg font-medium text-gray-500 sm:text-xl/8">
            {{ __('TPE_PME_SUBTITLE') }}
          </p>
          <div class="mt-10 flex items-center gap-x-6">
            <a href="{{ route('tools.cybercheck.init') }}" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
              {{ __('TPE_PME_CTA') }}
            </a>
            <a href="{{ route('register') }}" class="text-sm/6 font-semibold text-gray-900">
              {{ __('TPE_PME_REGISTER') }} <span aria-hidden="true">→</span>
            </a>
          </div>
        </div>
        <div class="mx-auto mt-16 flex max-w-2xl sm:mt-24 lg:ml-10 lg:mr-0 lg:mt-0 lg:max-w-none lg:flex-none xl:ml-3">
          <div class="max-w-3xl flex-none sm:max-w-5xl lg:max-w-none">
            <div class="-m-2 rounded-xl bg-gray-900/5 p-2 ring-1 ring-inset ring-gray-900/10 lg:-m-4 lg:rounded-2xl lg:p-4">
              <img src="/cywise/img/screenshot.png" alt="App Screenshot" width="2432" height="1442" class="w-[76rem] rounded-md shadow-2xl ring-1 ring-gray-900/10" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </x-container>
</x-layouts.marketing>
