<div x-data="{ sidebarOpen: false }"  @open-sidebar.window="sidebarOpen = true"
    x-init="
        $watch('sidebarOpen', function(value){
            if(value){ document.body.classList.add('overflow-hidden'); } else { document.body.classList.remove('overflow-hidden'); }
        });
    "
    class="relative z-50 w-screen md:w-auto" x-cloak>
    {{-- Backdrop for mobile --}}
    <div x-show="sidebarOpen" @click="sidebarOpen=false" class="fixed top-0 right-0 z-50 w-screen h-screen duration-300 ease-out bg-black/20 dark:bg-white/10"></div>

    {{-- Sidebar --}}
    <div :class="{ '-translate-x-full': !sidebarOpen }"
        class="fixed top-0 left-0 flex items-stretch -translate-x-full overflow-hidden lg:translate-x-0 z-50 h-dvh md:h-screen transition-[width,transform] duration-150 ease-out bg-zinc-50 dark:bg-zinc-900 w-64 group @if(config('wave.dev_bar')){{ 'pb-10' }}@endif">
        <div class="flex flex-col justify-between w-full overflow-auto md:h-full h-svh pt-4 pb-2.5">
            <div class="relative flex flex-col">
                <button x-on:click="sidebarOpen=false" class="flex items-center justify-center flex-shrink-0 w-10 h-10 ml-4 rounded-md lg:hidden text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200 dark:hover:bg-zinc-700/70 hover:bg-gray-200/70">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>

                <div class="flex items-center px-5 space-x-2">
                    <a href="/" class="flex justify-center items-center py-4 pl-0.5 space-x-1 font-bold text-zinc-900">
                        <x-logo class="w-auto h-7" />
                    </a>
                </div>
                <!--
                <div class="flex items-center px-4 pt-1 pb-3">
                    <div class="relative flex items-center w-full h-full rounded-lg">
                        <x-phosphor-magnifying-glass class="absolute left-0 w-5 h-5 ml-2 text-gray-400 -translate-y-px" />
                        <input type="text" class="w-full py-2 pl-8 text-sm border rounded-lg bg-zinc-200/70 focus:bg-white duration-50 dark:bg-zinc-950 ease border-zinc-200 dark:border-zinc-700/70 dark:ring-zinc-700/70 focus:ring dark:text-zinc-200 dark:focus:ring-zinc-700/70 dark:focus:border-zinc-700 focus:ring-zinc-200 focus:border-zinc-300 dark:placeholder-zinc-400" placeholder="Search">
                    </div>
                </div>
                -->
                <div class="flex flex-col justify-start items-center px-4 space-y-1.5 w-full h-full text-slate-600 dark:text-zinc-400">
                    @include('cywise.sidebar._top')
                </div>
            </div>

            <div class="relative px-2.5 space-y-1.5 text-zinc-700 dark:text-zinc-400">
                @include('cywise.sidebar._bottom')
                @include('cywise.sidebar._tip')
                <div class="w-full h-px my-2 bg-slate-100 dark:bg-zinc-700"></div>
                <x-app.user-menu />
            </div>
        </div>
    </div>

    {{-- Freshdesk --}}
    @include('cywise.sidebar._freshdesk')
</div>
