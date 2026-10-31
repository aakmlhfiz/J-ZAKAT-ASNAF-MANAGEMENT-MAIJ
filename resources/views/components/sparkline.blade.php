@props(['data' => [], 'color' => '#245B91', 'height' => 36, 'fill' => true, 'id' => null])
@php $sparkId = $id ?? 'spark-' . uniqid(); @endphp
<div style="height: {{ $height }}px;" class="relative">
    <canvas id="{{ $sparkId }}" data-spark='@json($data)' data-spark-color="{{ $color }}" data-spark-fill="{{ $fill ? '1' : '0' }}"></canvas>
</div>

@once
@push('scripts')
<script type="module">
    const initSparklines = () => {
        document.querySelectorAll('canvas[data-spark]').forEach((canvas) => {
            if (canvas.dataset.init === '1' || !window.Chart) return;
            canvas.dataset.init = '1';
            const data = JSON.parse(canvas.dataset.spark || '[]');
            const color = canvas.dataset.sparkColor || '#245B91';
            const fill = canvas.dataset.sparkFill === '1';
            const ctx = canvas.getContext('2d');
            const h = canvas.clientHeight || 36;
            const grad = ctx.createLinearGradient(0, 0, 0, h);
            grad.addColorStop(0, color + '40');
            grad.addColorStop(1, color + '00');
            new Chart(ctx, {
                type: 'line',
                data: { labels: data.map((_, i) => i), datasets: [{ data, borderColor: color, backgroundColor: fill ? grad : 'transparent', borderWidth: 2, fill, tension: 0.35, pointRadius: 0, pointHoverRadius: 0 }] },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                    scales: { x: { display: false }, y: { display: false } },
                    elements: { line: { borderJoinStyle: 'round' } },
                },
            });
        });
    };
    if (window.Chart) initSparklines();
    else document.addEventListener('DOMContentLoaded', initSparklines);
    new MutationObserver(() => initSparklines()).observe(document.body, { childList: true, subtree: true });
</script>
@endpush
@endonce
