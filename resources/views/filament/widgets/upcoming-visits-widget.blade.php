<!-- resources/views/filament/widgets/upcoming-visits-widget.blade.php -->
<x-filament::widget class="col-span-full">
    <x-filament::card>
        <h2 class="text-xl font-bold">Upcoming Visits Next 7 Days</h2>
        <ul>
            @forelse ($upcomingVisits as $visit)
                <li>
                    <strong>{{ $visit->customer->name }}</strong>:
                    <span>{{ $visit->customer->phone }}</span>
                    Next visit on
                    <span style="color:red">
                     {{ $visit->next_visit_date->format('l, F j, Y') }}
                    </span>
                </li>
            @empty
                <li>No upcoming visits this week.</li>
            @endforelse
        </ul>
    </x-filament::card>
</x-filament::widget>
