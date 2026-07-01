@props(['used' => 0, 'free' => 0, 'colorUsed' => '#f59e0b', 'colorFree' => '#e2e8f0'])

@php
    $config = [
        'type' => 'doughnut',
        'data' => [
            'labels' => ['Usato', 'Libero'],
            'datasets' => [[
                'data' => [$used, $free],
                'backgroundColor' => [$colorUsed, $colorFree],
                'borderWidth' => 0,
            ]],
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'cutout' => '70%',
            'plugins' => ['legend' => ['display' => true, 'position' => 'bottom']],
        ],
    ];
@endphp
<div class="h-52">
    <canvas data-chart="{{ json_encode($config) }}"></canvas>
</div>
