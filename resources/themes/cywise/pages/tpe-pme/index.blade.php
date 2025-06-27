<?php

use function Laravel\Folio\{name};

name('tpe-pme');
?>

<x-layouts.marketing>
  <x-container class="py-0">
    <!-- HERO : BEGIN -->
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
              {{ __('TPE_PME_CTA_1') }}
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
    <!-- HERO : END -->
    <!-- SOCIAL PROOF : BEGIN -->
    <div class="bg-white py-24 sm:py-32">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <h2 class="text-center text-lg/8 font-semibold text-gray-900">
          {{ __('TPE_PME_SOCIAL_PROOF_1') }}
        </h2>
        <div class="mx-auto mt-10 grid max-w-lg grid-cols-4 items-center gap-x-8 gap-y-10 sm:max-w-xl sm:grid-cols-6 sm:gap-x-10 lg:mx-0 lg:max-w-none lg:grid-cols-5">
          <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1" src="/cywise/img/logo-elephantastic.jpg" alt="Elephantastic" width="158" height="48" />
          <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1" src="/cywise/img/logo-oppscience.svg" alt="Oppscience" width="158" height="48" />
          <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1" src="/cywise/img/logo-netemedia.png" alt="Netemedia" width="158" height="48" />
          <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1" src="/cywise/img/logo-ista.png" alt="ISTA" width="158" height="48" />
          <img class="col-span-2 max-h-12 w-full object-contain lg:col-span-1" src="/cywise/img/logo-hermes.png" alt="Hermès" width="158" height="48" />
        </div>
      </div>
    </div>
    <!-- SOCIAL PROOF : END -->
    <!-- PROBLEMS : BEGIN -->
    <div class="bg-white py-3 sm:py-3">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-2xl lg:text-center">
          <h2 class="text-base/7 font-semibold text-indigo-600">
            {{ __('TPE_PME_PROBLEM_TITLE') }}
          </h2>
          <p class="mt-2 text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl lg:text-balance">
            {{ __('TPE_PME_PROBLEM_SUBTITLE') }}
          </p>
          <p class="mt-6 text-lg/8 text-gray-600">
            {{ __('TPE_PME_PROBLEM_TEXT') }}
          </p>
        </div>
        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
          <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
            <div class="flex flex-col">
              <dt class="flex items-center gap-x-3 text-base/7 font-semibold text-gray-900">
                <svg class="size-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                  <path fill-rule="evenodd" d="M5.5 17a4.5 4.5 0 0 1-1.44-8.765 4.5 4.5 0 0 1 8.302-3.046 3.5 3.5 0 0 1 4.504 4.272A4 4 0 0 1 15 17H5.5Zm3.75-2.75a.75.75 0 0 0 1.5 0V9.66l1.95 2.1a.75.75 0 1 0 1.1-1.02l-3.25-3.5a.75.75 0 0 0-1.1 0l-3.25 3.5a.75.75 0 1 0 1.1 1.02l1.95-2.1v4.59Z" clip-rule="evenodd" />
                </svg>
                {{ __('TPE_PME_PROBLEM_TITLE_1') }}
              </dt>
              <dd class="mt-4 flex flex-auto flex-col text-base/7 text-gray-600">
                <p class="flex-auto">
                  {!! __('TPE_PME_PROBLEM_TEXT_1') !!}
                </p>
              </dd>
            </div>
            <div class="flex flex-col">
              <dt class="flex items-center gap-x-3 text-base/7 font-semibold text-gray-900">
                <svg class="size-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                  <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 0 0-4.5 4.5V9H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2h-.5V5.5A4.5 4.5 0 0 0 10 1Zm3 8V5.5a3 3 0 1 0-6 0V9h6Z" clip-rule="evenodd" />
                </svg>
                {{ __('TPE_PME_PROBLEM_TITLE_2') }}
              </dt>
              <dd class="mt-4 flex flex-auto flex-col text-base/7 text-gray-600">
                <p class="flex-auto">
                  {!! __('TPE_PME_PROBLEM_TEXT_2') !!}
                </p>
              </dd>
            </div>
            <div class="flex flex-col">
              <dt class="flex items-center gap-x-3 text-base/7 font-semibold text-gray-900">
                <svg class="size-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                  <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.989a.75.75 0 0 0-.75.75v4.242a.75.75 0 0 0 1.5 0v-2.43l.31.31a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm1.23-3.723a.75.75 0 0 0 .219-.53V2.929a.75.75 0 0 0-1.5 0V5.36l-.31-.31A7 7 0 0 0 3.239 8.188a.75.75 0 1 0 1.448.389A5.5 5.5 0 0 1 13.89 6.11l.311.31h-2.432a.75.75 0 0 0 0 1.5h4.243a.75.75 0 0 0 .53-.219Z" clip-rule="evenodd" />
                </svg>
                {{ __('TPE_PME_PROBLEM_TITLE_3') }}
              </dt>
              <dd class="mt-4 flex flex-auto flex-col text-base/7 text-gray-600">
                <p class="flex-auto">
                  {!! __('TPE_PME_PROBLEM_TEXT_3') !!}
                </p>
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
    <!-- PROBLEMS : END -->
    <!-- SOLUTION : BEGIN -->
    <div class="overflow-hidden bg-white py-24 sm:py-32">
      <div class="mx-auto max-w-2xl px-6 lg:max-w-7xl lg:px-8">
        <div class="max-w-4xl">
          <p class="text-base/7 font-semibold text-indigo-600">
            {{ __('TPE_PME_SOLUTION_SECTION') }}
          </p>
          <h1 class="mt-2 text-pretty text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">
            {{ __('TPE_PME_SOLUTION_TITLE') }}
          </h1>
          <p class="mt-6 text-balance text-xl/8 text-gray-700">
            {{ __('TPE_PME_SOLUTION_SUBTITLE') }}
          </p>
        </div>
        <section class="mt-20 grid grid-cols-1 lg:grid-cols-2 lg:gap-x-8 lg:gap-y-16">
          <div class="lg:pr-8">
            <h2 class="text-pretty text-2xl font-semibold tracking-tight text-gray-900">
              {{ __('TPE_PME_SOLUTION_PARAGRAPH_TITLE_1') }}
            </h2>
            <p class="mt-6 text-base/7 text-gray-600">
              {{ __('TPE_PME_SOLUTION_PARAGRAPH_TEXT_1') }}
            </p>
            <h2 class="mt-6 text-pretty text-2xl font-semibold tracking-tight text-gray-900">
              {{ __('TPE_PME_SOLUTION_PARAGRAPH_TITLE_2') }}
            </h2>
            <p class="mt-6 text-base/7 text-gray-600">
              {{ __('TPE_PME_SOLUTION_PARAGRAPH_TEXT_2') }}
            </p>
            <h2 class="mt-6 text-pretty text-2xl font-semibold tracking-tight text-gray-900">
              {{ __('TPE_PME_SOLUTION_PARAGRAPH_TITLE_3') }}
            </h2>
            <p class="mt-6 text-base/7 text-gray-600">
              {{ __('TPE_PME_SOLUTION_PARAGRAPH_TEXT_3') }}
            </p>
          </div>
          <div class="pt-16 lg:row-span-2 lg:-mr-16 xl:mr-auto">
            <div class="-mx-8 grid grid-cols-2 gap-4 sm:-mx-16 sm:grid-cols-4 lg:mx-0 lg:grid-cols-2 lg:gap-4 xl:gap-8">
              <div class="aspect-square overflow-hidden rounded-xl shadow-xl outline outline-1 -outline-offset-1 outline-black/10">
                <img alt="" src="https://images.unsplash.com/photo-1590650516494-0c8e4a4dd67e?&auto=format&fit=crop&crop=center&w=560&h=560&q=90" class="block size-full object-cover" />
              </div>
              <div class="-mt-8 aspect-square overflow-hidden rounded-xl shadow-xl outline outline-1 -outline-offset-1 outline-black/10 lg:-mt-40">
                <img alt="" src="https://images.unsplash.com/photo-1557804506-669a67965ba0?&auto=format&fit=crop&crop=left&w=560&h=560&q=90" class="block size-full object-cover" />
              </div>
              <div class="aspect-square overflow-hidden rounded-xl shadow-xl outline outline-1 -outline-offset-1 outline-black/10">
                <img alt="" src="https://images.unsplash.com/photo-1559136555-9303baea8ebd?&auto=format&fit=crop&crop=left&w=560&h=560&q=90" class="block size-full object-cover" />
              </div>
              <div class="-mt-8 aspect-square overflow-hidden rounded-xl shadow-xl outline outline-1 -outline-offset-1 outline-black/10 lg:-mt-40">
                <img alt="" src="https://images.unsplash.com/photo-1598257006458-087169a1f08d?&auto=format&fit=crop&crop=center&w=560&h=560&q=90" class="block size-full object-cover" />
              </div>
            </div>
          </div>
          @php
          $user = \App\Models\User::where('email', config('towerify.admin.email'))->firstOrFail();
          $nbUsers = format_number(\App\Models\User::count());
          $nbAssets = format_number(\App\Models\Asset::count() + \App\Models\YnhServer::count());
          $nbHoneypots = format_number(\App\Models\Honeypot::count());
          $nbLeaks = format_number(\App\Helpers\JosianneClient::numberOfRows('dumps_login_email_domain'));
          @endphp
          <div class="max-lg:mt-16 lg:col-span-1">
            <p class="text-base/7 font-semibold text-gray-500">
              {{ __('TPE_PME_SOLUTION_NUMBERS') }}
            </p>
            <hr class="mt-6 border-t border-gray-200" />
            <dl class="mt-6 grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2">
              <div class="flex flex-col gap-y-2 border-b border-dotted border-gray-200 pb-4">
                <dt class="text-sm/6 text-gray-600">
                  {{ __('Users') }}
                </dt>
                <dd class="order-first text-6xl font-semibold tracking-tight">
                  <span>{{ $nbUsers[0] }}</span> {{ $nbUsers[1] }}
                </dd>
              </div>
              <div class="flex flex-col gap-y-2 border-b border-dotted border-gray-200 pb-4">
                <dt class="text-sm/6 text-gray-600">
                  {{ __('Monitored Servers') }}
                </dt>
                <dd class="order-first text-6xl font-semibold tracking-tight">
                  <span>{{ $nbAssets[0] }}</span> {{ $nbAssets[1] }}
                </dd>
              </div>
              <div class="flex flex-col gap-y-2 max-sm:border-b max-sm:border-dotted max-sm:border-gray-200 max-sm:pb-4">
                <dt class="text-sm/6 text-gray-600">
                  {{ __('Compromised Credentials') }}
                </dt>
                <dd class="order-first text-6xl font-semibold tracking-tight">
                  <span>{{ $nbLeaks[0] }}</span> {{ $nbLeaks[1] }}
                </dd>
              </div>
              <div class="flex flex-col gap-y-2">
                <dt class="text-sm/6 text-gray-600">
                  {{ __('Honeypots') }}
                </dt>
                <dd class="order-first text-6xl font-semibold tracking-tight">
                  <span>{{ $nbHoneypots[0] }}</span> {{ $nbHoneypots[1] }}
                </dd>
              </div>
            </dl>
          </div>
        </section>
      </div>
    </div>
    <!-- SOLUTION : END -->
    <!-- FEATURES : BEGIN-->
    <div class="bg-white py-24 sm:py-32">
      <div class="mx-auto max-w-2xl px-6 lg:max-w-7xl lg:px-8">
        <h2 class="text-base/7 font-semibold text-indigo-600">
          {{ __('TPE_PME_FEATURE_SECTION') }}
        </h2>
        <p class="mt-2 max-w-lg text-pretty text-4xl font-semibold tracking-tight text-gray-950 sm:text-5xl">
          {{ __('TPE_PME_FEATURE_TITLE') }}
        </p>
        <div class="mt-10 grid grid-cols-1 gap-4 sm:mt-16 lg:grid-cols-6 lg:grid-rows-2">
          <div class="relative lg:col-span-3">
            <div class="absolute inset-0 rounded-lg bg-white max-lg:rounded-t-[2rem] lg:rounded-tl-[2rem]"></div>
            <div class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)] max-lg:rounded-t-[calc(2rem+1px)] lg:rounded-tl-[calc(2rem+1px)]">
              <img class="h-80 object-cover object-left" src="/cywise/img/cyberbuddy.png" alt="" />
              <div class="p-10 pt-4">
                <h3 class="text-sm/4 font-semibold text-indigo-600">
                  {{ __('TPE_PME_FEATURE_SECTION_1') }}
                </h3>
                <p class="mt-2 text-lg font-medium tracking-tight text-gray-950">
                  {{ __('TPE_PME_FEATURE_TITLE_1') }}
                </p>
                <p class="mt-2 max-w-lg text-sm/6 text-gray-600">
                  {{ __('TPE_PME_FEATURE_TEXT_1') }}
                </p>
              </div>
            </div>
            <div class="pointer-events-none absolute inset-0 rounded-lg shadow outline outline-black/5 max-lg:rounded-t-[2rem] lg:rounded-tl-[2rem]"></div>
          </div>
          <div class="relative lg:col-span-3">
            <div class="absolute inset-0 rounded-lg bg-white lg:rounded-tr-[2rem]"></div>
            <div class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)] lg:rounded-tr-[calc(2rem+1px)]">
              <img class="h-80 object-cover object-left lg:object-right" src="/cywise/img/cybertodo.png" alt="" />
              <div class="p-10 pt-4">
                <h3 class="text-sm/4 font-semibold text-indigo-600">
                  {{ __('TPE_PME_FEATURE_SECTION_2') }}
                </h3>
                <p class="mt-2 text-lg font-medium tracking-tight text-gray-950">
                  {{ __('TPE_PME_FEATURE_TITLE_2') }}
                </p>
                <p class="mt-2 max-w-lg text-sm/6 text-gray-600">
                  {{ __('TPE_PME_FEATURE_TEXT_2') }}
                </p>
              </div>
            </div>
            <div class="pointer-events-none absolute inset-0 rounded-lg shadow outline outline-black/5 lg:rounded-tr-[2rem]"></div>
          </div>
          <div class="relative lg:col-span-2">
            <div class="absolute inset-0 rounded-lg bg-white lg:rounded-bl-[2rem]"></div>
            <div class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)] lg:rounded-bl-[calc(2rem+1px)]">
              <img class="h-80 object-cover object-left" src="/cywise/img/vulnerability.png" alt="" />
              <div class="p-10 pt-4">
                <h3 class="text-sm/4 font-semibold text-indigo-600">
                  {{ __('TPE_PME_FEATURE_SECTION_3') }}
                </h3>
                <p class="mt-2 text-lg font-medium tracking-tight text-gray-950">
                  {{ __('TPE_PME_FEATURE_TITLE_3') }}
                </p>
                <p class="mt-2 max-w-lg text-sm/6 text-gray-600">
                  {{ __('TPE_PME_FEATURE_TEXT_3') }}
                </p>
              </div>
            </div>
            <div class="pointer-events-none absolute inset-0 rounded-lg shadow outline outline-black/5 lg:rounded-bl-[2rem]"></div>
          </div>
          <div class="relative lg:col-span-2">
            <div class="absolute inset-0 rounded-lg bg-white"></div>
            <div class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)]">
              <img class="h-80 object-cover" src="/cywise/img/events.png" alt="" />
              <div class="p-10 pt-4">
                <h3 class="text-sm/4 font-semibold text-indigo-600">
                  {{ __('TPE_PME_FEATURE_SECTION_4') }}
                </h3>
                <p class="mt-2 text-lg font-medium tracking-tight text-gray-950">
                  {{ __('TPE_PME_FEATURE_TITLE_4') }}
                </p>
                <p class="mt-2 max-w-lg text-sm/6 text-gray-600">
                  {{ __('TPE_PME_FEATURE_TEXT_4') }}
                </p>
              </div>
            </div>
            <div class="pointer-events-none absolute inset-0 rounded-lg shadow outline outline-black/5"></div>
          </div>
          <div class="relative lg:col-span-2">
            <div class="absolute inset-0 rounded-lg bg-white max-lg:rounded-b-[2rem] lg:rounded-br-[2rem]"></div>
            <div class="relative flex h-full flex-col overflow-hidden rounded-[calc(theme(borderRadius.lg)+1px)] max-lg:rounded-b-[calc(2rem+1px)] lg:rounded-br-[calc(2rem+1px)]">
              <img class="h-80 object-cover" src="/cywise/img/honeypots.png" alt="" />
              <div class="p-10 pt-4">
                <h3 class="text-sm/4 font-semibold text-indigo-600">
                  {{ __('TPE_PME_FEATURE_SECTION_5') }}
                </h3>
                <p class="mt-2 text-lg font-medium tracking-tight text-gray-950">
                  {{ __('TPE_PME_FEATURE_TITLE_5') }}
                </p>
                <p class="mt-2 max-w-lg text-sm/6 text-gray-600">
                  {{ __('TPE_PME_FEATURE_TEXT_5') }}
                </p>
              </div>
            </div>
            <div class="pointer-events-none absolute inset-0 rounded-lg shadow outline outline-black/5 max-lg:rounded-b-[2rem] lg:rounded-br-[2rem]"></div>
          </div>
        </div>
      </div>
    </div>
    <!-- FEATURES : END -->
    <!-- TESTIMONIALS : BEGIN -->
    <section class="bg-white py-24 sm:py-32">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto grid max-w-2xl grid-cols-1 lg:mx-0 lg:max-w-none lg:grid-cols-2">
          <div class="flex flex-col pb-10 sm:pb-16 lg:pb-0 lg:pr-8 xl:pr-20">
            <img class="h-12 self-start" src="/cywise/img/logo-oppscience.svg" alt="" />
            <figure class="mt-10 flex flex-auto flex-col justify-between">
              <blockquote class="text-lg/8 text-gray-900">
                <p>
                  “Amet amet eget scelerisque tellus sit neque faucibus non eleifend. Integer eu praesent at a. Ornare arcu gravida natoque erat et cursus tortor consequat at. Vulputate gravida sociis enim nullam ultricies habitant malesuada lorem ac. Tincidunt urna dui pellentesque sagittis.”
                </p>
              </blockquote>
              <figcaption class="mt-10 flex items-center gap-x-6">
                <div class="text-base">
                  <div class="font-semibold text-gray-900">
                    John Doe
                  </div>
                  <div class="mt-1 text-gray-500">
                    RSSI d'Oppscience
                  </div>
                </div>
              </figcaption>
            </figure>
          </div>
          <div class="flex flex-col pt-10 sm:pt-16 lg:pl-8 lg:pt-0 xl:pl-20">
            <img class="h-12 self-start" src="/cywise/img/logo-elephantastic.jpg" alt="" />
            <figure class="mt-10 flex flex-auto flex-col justify-between">
              <blockquote class="text-lg/8 text-gray-900">
                <p>
                  “Excepteur veniam labore ullamco eiusmod. Pariatur consequat proident duis dolore nulla veniam reprehenderit nisi officia voluptate incididunt exercitation exercitation elit. Nostrud veniam sint dolor nisi ullamco.”
                </p>
              </blockquote>
              <figcaption class="mt-10 flex items-center gap-x-6">
                <div class="text-base">
                  <div class="font-semibold text-gray-900">
                    John Doe
                  </div>
                  <div class="mt-1 text-gray-500">
                    CEO d'Elephantastic
                  </div>
                </div>
              </figcaption>
            </figure>
          </div>
        </div>
      </div>
    </section>
    <!-- TESTIMONIALS : END -->
    <!-- PRICING : BEGIN -->
    <x-marketing.sections.pricing />
    <!-- PRICING : END -->
    <!-- CTA : BEGIN -->
    <div class="bg-white">
      <div class="mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:flex lg:items-center lg:justify-between lg:px-8">
        <h2 class="max-w-2xl text-4xl font-semibold tracking-tight text-gray-900 sm:text-5xl">
          {!! __('TPE_PME_CTA_2') !!}
        </h2>
        <div class="mt-10 flex items-center gap-x-6 lg:mt-0 lg:shrink-0">
          <a href="{{ route('tools.cybercheck.init') }}" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            {{ __('TPE_PME_CTA_1') }}
          </a>
        </div>
      </div>
    </div>
    <!-- CTA : END -->
  </x-container>
</x-layouts.marketing>
