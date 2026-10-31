import './bootstrap.js';
import Alpine from 'alpinejs';
import { Chart, registerables } from 'chart.js';
import '@fortawesome/fontawesome-free/css/all.min.css';
import './echo.js';
import './profile.js';
import './import-pdf.js';

Chart.register(...registerables);
Chart.defaults.font.family = "'Inter', 'Segoe UI', system-ui, sans-serif";
Chart.defaults.color = '#64748B';
window.Chart = Chart;

document.addEventListener('alpine:init', () => {
    Alpine.data('appSidebar', () => ({
        collapsed: localStorage.getItem('sidebar-collapsed') === '1',
        mobileOpen: false,
        openGroups: (() => { try { return JSON.parse(localStorage.getItem('sidebar-groups') || '["household","asnaf","ai","data","system","permohonan","alat","akaun"]'); } catch { return ['household','asnaf','ai','data','system','permohonan','alat','akaun']; } })(),
        toggle() {
            if (window.innerWidth < 768) {
                this.mobileOpen = !this.mobileOpen;
            } else {
                this.collapsed = !this.collapsed;
                localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0');
            }
        },
        closeMobile() { this.mobileOpen = false; },
        toggleGroup(key) {
            const idx = this.openGroups.indexOf(key);
            if (idx > -1) this.openGroups.splice(idx, 1); else this.openGroups.push(key);
            localStorage.setItem('sidebar-groups', JSON.stringify(this.openGroups));
        },
        init() {
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 768) this.mobileOpen = false;
            });
        },
    }));

    // ----- AI Chat Assistant -----
    Alpine.data('aiChatWidget', () => ({
        open: false,
        loading: false,
        fullscreen: false,
        messages: [],
        draft: '',
        provider: 'mock',
        history: [],

        init() {
            try {
                const saved = sessionStorage.getItem('ai-chat-history');
                if (saved) {
                    const parsed = JSON.parse(saved);
                    if (Array.isArray(parsed)) this.messages = parsed;
                }
                this.$root.addEventListener('ai-send', () => this.send());
            } catch (e) { console.warn('AI chat init failed', e); }
            // Switch to fullscreen drawer on mobile when opened.
            this.$watch('open', (val) => { if (val) this.fullscreen = window.innerWidth < 640; });
        },

        close() { this.open = false; },
        toggleFullscreen() { this.fullscreen = !this.fullscreen; },

        saveHistory() {
            try { sessionStorage.setItem('ai-chat-history', JSON.stringify(this.messages.slice(-50))); } catch (e) {}
        },

        async send() {
            const text = this.draft.trim();
            if (!text || this.loading) return;

            this.messages.push({ id: Date.now(), role: 'user', text, time: this.now() });
            this.draft = '';
            this.loading = true;
            this.scrollToBottom();

            try {
                const r = await fetch('/ai/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message: text,
                        history: this.messages.slice(0, -1).map((m) => ({ role: m.role === 'user' ? 'user' : 'assistant', content: m.text })),
                    }),
                });
                const data = await r.json();

                this.messages.push({
                    id: Date.now() + 1,
                    role: 'ai',
                    text: data.reply || this.t('no_reply', 'Maaf, saya tidak dapat menjana jawapan buat masa ini.'),
                    data: data.data || { type: 'text' },
                    provider: data.provider,
                    time: this.now(),
                });
                if (data.provider) this.provider = data.provider;
                this.saveHistory();
            } catch (e) {
                this.messages.push({
                    id: Date.now() + 1, role: 'ai',
                    text: this.t('error', 'Maaf, berlaku ralat sambungan. Sila cuba lagi.'),
                    data: { type: 'text', error: true }, time: this.now(),
                });
            } finally {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        clear() {
            this.messages = [];
            sessionStorage.removeItem('ai-chat-history');
            try { fetch('/ai/clear', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' } }); } catch (e) {}
        },

        scrollToBottom() {
            const el = this.$refs.messages;
            if (el) { setTimeout(() => { el.scrollTop = el.scrollHeight; }, 30); }
        },

        now() {
            const locale = (window.aiChatStrings && window.aiChatStrings.locale) === 'en' ? 'en-MY' : 'ms-MY';
            return new Date().toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit' });
        },

        // Localized UI copy is injected by the ai-chat-widget Blade component.
        aiStrings() { return window.aiChatStrings || {}; },
        t(key, fallback) {
            const s = this.aiStrings();
            return s[key] !== undefined ? s[key] : fallback;
        },
        statusLabel(status) {
            const s = this.aiStrings();
            const map = {
                'Layak': s.status_layak || 'Layak',
                'Tidak Layak': s.status_tidak_layak || 'Tidak Layak',
                'Dalam Semakan': s.status_dalam_semakan || 'Dalam Semakan',
            };
            return map[status] || status || '';
        },
        providerLabel() {
            const providers = this.aiStrings().providers || {};
            return providers[this.provider] || this.provider;
        },
        escapeHtml(s) {
            return (s ?? '').toString()
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        },
        renderMarkdown(s) {
            const escaped = this.escapeHtml(s);
            return escaped
                .replace(/\*\*(.+?)\*\*/g, '<span class="ai-reply-strong">$1</span>')
                .replace(/`([^`]+)`/g, '<code class="ai-reply-code">$1</code>')
                .replace(/\n/g, '<br>');
        },

        renderData(d) {
            if (!d || !d.type) return '';
            const esc = (s) => this.escapeHtml(s);
            const label = (key, fallback) => this.t(key, fallback);
            const statusLabel = (status) => this.statusLabel(status);
            if (d.type === 'stats') {
                const items = (d.stats || []).map((s) => {
                    const colorMap = { primary: 'bg-primary/10 text-primary', success: 'bg-success/10 text-success', danger: 'bg-danger/10 text-danger', warning: 'bg-warning/10 text-warning' };
                    const cls = colorMap[s.color] || 'bg-muted text-text';
                    return `<div class="p-2.5 rounded-[12px] ${cls}">
                        <p class="text-[10px] uppercase tracking-wider opacity-80">${esc(s.label)}</p>
                        <p class="text-[20px] font-bold leading-none mt-1">${esc(s.value)}</p>
                        ${s.pct ? `<p class="text-[10px] opacity-70 mt-1">${esc(s.pct)}</p>` : ''}
                    </div>`;
                }).join('');
                return `<h4 class="text-small font-semibold mb-2">${esc(d.title || label('card_stats', 'Statistik'))}</h4>
                        <div class="grid grid-cols-2 gap-2">${items}</div>`;
            }
            if (d.type === 'summary') {
                const rows = Object.entries(d.fields || {}).map(([k, v]) => `<div class="flex items-baseline justify-between gap-3"><span class="text-secondary text-[11px] uppercase tracking-wider">${esc(k)}</span><span class="font-medium text-right">${esc(v)}</span></div>`).join('');
                const reason = d.reason ? `<div class="mt-3 pt-3 border-t border-divider text-small"><i class="fa-solid fa-lightbulb text-warning mr-1.5"></i>${esc(d.reason)}</div>` : '';
                return `<h4 class="text-small font-semibold mb-2">${esc(label('card_profile', 'Profil Pemohon'))}</h4>
                        <div class="space-y-1.5">${rows}</div>${reason}`;
            }
            if (d.type === 'checklist') {
                const statusMap = { 'Layak': 'bg-success/10 text-success', 'Tidak Layak': 'bg-danger/10 text-danger', 'Dalam Semakan': 'bg-warning/10 text-warning' };
                const scls = statusMap[d.status] || 'bg-muted text-text';
                const items = (d.items || []).map((it) => {
                    const ok = it.startsWith('✓');
                    const bad = it.startsWith('✗');
                    const icon = ok ? 'fa-check text-success' : (bad ? 'fa-xmark text-danger' : 'fa-circle text-secondary');
                    return `<li class="flex items-start gap-2"><i class="fa-solid ${icon} text-[11px] mt-1 shrink-0"></i><span>${esc(it.replace(/^[✓✗○]\s*/, ''))}</span></li>`;
                }).join('');
                return `<h4 class="text-small font-semibold mb-2">${esc(d.title || label('card_explanation', 'Penjelasan'))}</h4>
                        ${d.status ? `<div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-pill text-[11px] font-semibold mb-2 ${scls}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>${esc(statusLabel(d.status))}
                            ${d.confidence ? `<span class="text-secondary">· ${esc(d.confidence)}%</span>` : ''}
                        </div>` : ''}
                        <ul class="space-y-1.5">${items}</ul>`;
            }
            if (d.type === 'eligibility') {
                const statusMap = { 'Layak': 'bg-success/10 text-success', 'Tidak Layak': 'bg-danger/10 text-danger', 'Dalam Semakan': 'bg-warning/10 text-warning' };
                const scls = statusMap[d.status] || 'bg-muted text-text';
                const items = (d.items || []).map((it) => {
                    const [k, ...rest] = it.split(':');
                    return `<li class="flex items-baseline justify-between gap-2"><span class="text-secondary text-[11px]">${esc(k)}</span><span class="font-medium">${esc(rest.join(':').trim())}</span></li>`;
                }).join('');
                return `<h4 class="text-small font-semibold mb-2">${esc(d.title || label('card_result', 'Keputusan'))}</h4>
                        ${d.status ? `<div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-pill text-[11px] font-semibold mb-2 ${scls}">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>${esc(statusLabel(d.status))} · ${esc(label('card_ai_score', 'Skor AI'))} ${esc(d.ai_score ?? d.confidence ?? 0)}%
                        </div>` : ''}
                        <ul class="space-y-1">${items}</ul>`;
            }
            if (d.type === 'search_results') {
                const items = (d.results || []).map((r) => `<li class="flex items-center justify-between gap-3 py-1">
                    <div class="min-w-0">
                        <a href="${esc(r.profile)}" class="text-small font-medium hover:text-primary truncate block">${esc(r.nama)}</a>
                        <p class="text-[10px] text-secondary">${esc(r.ic)}</p>
                    </div>
                </li>`).join('');
                const heading = label('card_search_results', 'Hasil Carian (:count)').replace(':count', esc(d.count));
                return `<h4 class="text-small font-semibold mb-2">${esc(heading)}</h4><ul class="space-y-1">${items}</ul>`;
            }
            if (d.type === 'suggestions') {
                const items = (d.suggestions || []).map((s) => `<button type="button" @click="draft = ${esc(JSON.stringify(s.prompt))}; $dispatch('ai-send')" class="w-full text-left px-3 py-2 surface flex items-center gap-2.5 hover:border-primary transition-apple">
                    <span class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0"><i class="fa-solid ${esc(s.icon)} text-[10px]"></i></span>
                    <span class="text-small font-medium truncate flex-1">${esc(s.label)}</span>
                </button>`).join('');
                return `<h4 class="text-small font-semibold mb-2">${esc(label('card_suggestions', 'Cuba tanya saya'))}</h4><div class="grid grid-cols-1 gap-1.5">${items}</div>`;
            }
            return '';
        },
    }));

});

window.Alpine = Alpine;
Alpine.start();

const initTrendCharts = () => {
    document.querySelectorAll('canvas[id^="trend-"], canvas[data-values]').forEach((canvas) => {
        if (canvas.dataset.init === '1') return;
        canvas.dataset.init = '1';

        const labels = JSON.parse(canvas.dataset.labels || '[]');
        const values = JSON.parse(canvas.dataset.values || '[]');
        const color = canvas.dataset.color || '#245B91';
        const fill = canvas.dataset.fill === '1';
        const type = canvas.dataset.type || 'line';

        const ctx = canvas.getContext('2d');
        const h = canvas.clientHeight || 220;
        const gradient = ctx.createLinearGradient(0, 0, 0, h);
        gradient.addColorStop(0, color + '33');
        gradient.addColorStop(1, color + '00');

        if (type === 'bar') {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Permohonan', data: values,
                        backgroundColor: color,
                        borderRadius: 8, borderSkipped: false,
                        maxBarThickness: 32,
                    }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#15345B', titleColor: '#fff',
                            bodyColor: '#fff', cornerRadius: 6, padding: 10,
                            displayColors: false,
                        },
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#64748B', font: { size: 12 } }, border: { display: false } },
                        y: { beginAtZero: true, grid: { color: '#E2E8F0', drawBorder: false }, ticks: { color: '#64748B', font: { size: 12 }, precision: 0 }, border: { display: false } },
                    },
                },
            });
            return;
        }

        if (type === 'doughnut') {
            const palette = ['#15345B', '#15803D', '#D97706', '#DC2626', '#2878C8', '#64748B'];
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data: values,
                        backgroundColor: values.map((_, i) => palette[i % palette.length]),
                        borderColor: '#fff',
                        borderWidth: 3,
                    }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#15345B', titleColor: '#fff',
                            bodyColor: '#fff', cornerRadius: 6, padding: 10,
                        },
                    },
                },
            });
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Permohonan', data: values,
                    borderColor: color,
                    backgroundColor: fill ? gradient : 'transparent',
                    borderWidth: 2.5, fill, tension: 0.35,
                    pointRadius: 0, pointHoverRadius: 5,
                    pointHoverBackgroundColor: color,
                    pointHoverBorderColor: '#fff', pointHoverBorderWidth: 2,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#15345B', titleColor: '#fff',
                        bodyColor: '#fff', cornerRadius: 6, padding: 10,
                        displayColors: false,
                    },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#64748B', font: { size: 12 } }, border: { display: false } },
                    y: { beginAtZero: true, grid: { color: '#E2E8F0', drawBorder: false }, ticks: { color: '#64748B', font: { size: 12 }, precision: 0 }, border: { display: false } },
                },
            },
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTrendCharts);
} else {
    initTrendCharts();
}

const mo = new MutationObserver(() => initTrendCharts());
mo.observe(document.body, { childList: true, subtree: true });
