@props(['data' => [], 'labels' => [], 'height' => 220, 'id' => null, 'color' => '#245B91', 'fill' => true, 'type' => 'line'])
@php $chartId = $id ?? 'trend-' . uniqid(); @endphp
<div class="relative" style="height: {{ $height }}px;">
    <canvas id="{{ $chartId }}"
        data-values='@json($data)'
        data-labels='@json($labels)'
        data-color="{{ $color }}"
        data-fill="{{ $fill ? '1' : '0' }}"
        data-type="{{ $type }}"
    ></canvas>
</div>
