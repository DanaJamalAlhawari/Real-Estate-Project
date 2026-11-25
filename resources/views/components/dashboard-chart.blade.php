@props(['series', 'label', 'decimals' => 0])

@php($peak = max([1, ...array_column($series, 'amount')]))
<div class="dashboard-chart-scroll">
    <div class="dashboard-chart" role="img"
         aria-label="{{ $label }}. {{ collect($series)->map(fn ($point) => $point['label'].' '.number_format($point['amount'], $decimals))->implode(' · ') }}">
        @foreach ($series as $point)
            <div class="dashboard-chart__column" aria-hidden="true">
                <span>{{ number_format($point['amount'], $decimals) }}</span>
                <div @class(['dashboard-chart__bar', 'is-current' => $loop->last])
                     style="height: {{ round(132 * $point['amount'] / $peak) }}px"></div>
                <span>{{ $point['label'] }}</span>
            </div>
        @endforeach
    </div>
</div>
