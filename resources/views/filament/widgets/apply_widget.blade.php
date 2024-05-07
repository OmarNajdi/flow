<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <h2 class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white mb-4">
                    Apply Now
                </h2>

                <div class="grid grid-cols-3">
                    <a href="{{ url('/applications/create?program=1') }}">
                        <div class="bg-gray-100 rounded-2xl hover:scale-105 transition-all border">
                            <img src="{{ asset('images/PIEC.jpg') }}" alt="Apply Now" class="rounded-2xl"/>
                            <h3 class="font-bold text-2xl text-center py-6 color-primary">PIEC</h3>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
