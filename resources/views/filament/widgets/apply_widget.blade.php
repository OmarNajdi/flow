<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <h2 class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white mb-4">
                    {{ __('Open Programs')}}
                </h2>
                <div class="grid grid-cols-3 gap-10">
                    <a href="{{ url('/programs/11') }}">
                        <div class="bg-gray-100 dark:bg-gray-800 rounded-2xl hover:scale-105 transition-all border">
                            <img src="{{ asset('images/PIEC-7.webp') }}" alt="Apply Now"
                                 class="rounded-2xl"/>
                            <h2 class="text-center pt-2 pb-4 color-primary">{{__('Level: Pre-Acceleration')}}</h2>
                            <h3 class="font-bold text-2xl text-center pb-6 color-primary">{{__('Program: Orange Corners')}}</h3>
                            <button
                                class="w-full bg-[#01786C] fi-btn grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-2xl fi-color-primary fi-btn-color-primary fi-color-primary fi-size-xl fi-btn-size-xl gap-1.5 px-4 py-3 text-sm inline-grid shadow-sm text-white fi-ac-action fi-ac-btn-action">
                                <span class="fi-btn-label">{{__('Apply')}}</span>
                            </button>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
