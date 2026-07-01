@props(['data' => [], 'color' => '#0284c7', 'label' => 'Totale'])

@if (empty($data))
    <p class="text-sm text-gray-500">Nessun dato disponibile.</p>
@else
    @php
        $config = [
            'type' => 'bar',
            'data' => [
                'labels' => array_keys($data),
                'datasets' => [[
                    'label' => $label,
                    'data' => array_values($data),
                    'backgroundColor' => $color,
                    'borderRadius' => 6,
                    'maxBarThickness' => 36,
                ]],
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => ['legend' => ['display' => false]],
                'scales' => [
                    'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
                ],
            ],
        ];
    @endphp
    <div class="h-52">
        <canvas data-chart="{{ json_encode($config) }}"></canvas>
    </div>
@endif
