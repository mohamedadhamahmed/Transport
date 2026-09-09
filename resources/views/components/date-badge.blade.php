@props(['date'])

@php
    $color = 'bg-gray-100 text-gray-400';
    $label = '-';

    if ($date) {
        $label = $date->format('Y-m-d');
        $daysLeft = now()->startOfDay()->diffInDays($date, false);

        if ($daysLeft <= 15) {
            $color = 'bg-red-100 text-red-700';
        } elseif ($daysLeft <= 45) {
            $color = 'bg-amber-100 text-amber-700';
        } else {
            $color = 'bg-emerald-100 text-emerald-700';
        }
    }
@endphp

<span class="inline-block px-2.5 py-1 rounded-md text-xs font-semibold {{ $color }}">{{ $label }}</span>