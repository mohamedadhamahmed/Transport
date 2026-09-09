<x-app-layout>
    <div class="p-6" dir="rtl">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-lg font-bold text-[#0F1B4C]">{{ __('notifications.title') }}</h1>
            <span class="text-xs text-gray-400">{{ __('notifications.range_note', ['days' => $days]) }}</span>
        </div>

        <div class="bg-white rounded-xl shadow divide-y divide-gray-100">
            @forelse ($alerts as $alert)
                <div class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full {{ $alert['days_left'] <= 15 ? 'bg-red-500' : 'bg-amber-500' }}"></span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $alert['employee']->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500">{{ $alert['type'] }}</p>
                        </div>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-medium text-gray-700">{{ $alert['date']->format('Y-m-d') }}</p>
                        <p class="text-xs {{ $alert['days_left'] <= 15 ? 'text-red-500' : 'text-amber-500' }}">
                            {{ $alert['days_left'] }} {{ __('notifications.days') }}
                        </p>
                    </div>
                    <a href="{{ route('contracts.edit', $alert['contract']) }}" class="text-blue-600 hover:underline text-xs font-medium">
                        {{ __('contracts.edit') }}
                    </a>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-gray-400 text-sm">{{ __('notifications.empty') }}</div>
            @endforelse
        </div>
    </div>
</x-app-layout>