@extends('portal.layouts.app')
@section('title', 'AI Assistant — Portal Pemohon')
@section('page-title', 'AI Assistant')

@section('content')
<div x-data="portalAiChat()" class="max-w-3xl mx-auto">
    <div class="mb-5 flex items-center gap-3">
        <div class="w-11 h-11 rounded-[14px] bg-primary text-white flex items-center justify-center font-bold text-[16px] shrink-0" style="background: var(--gradient-royal);">AI</div>
        <div>
            <h1 class="text-title text-text">Pembantu Maya</h1>
            <p class="text-small text-secondary">Panduan permohonan bantuan zakat MAIJ</p>
        </div>
    </div>

    <x-app-card class="p-0 overflow-hidden" :hover="false">
        <div x-ref="messages" class="ai-messages flex-1 overflow-y-auto px-5 md:px-6 py-6 space-y-4 bg-bg min-h-[420px] max-h-[60vh]">
            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex items-start gap-2'">
                    <div x-show="msg.role === 'ai'" class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-[11px] shrink-0" style="background: var(--gradient-royal);">AI</div>
                    <div :class="msg.role === 'user' ? 'bg-primary text-white rounded-[20px] rounded-br-md max-w-[80%] px-4 py-3 shadow-sm' : 'bg-card border border-divider rounded-[20px] rounded-bl-md max-w-[88%] px-4 py-3 shadow-card'">
                        <div x-html="msg.role === 'ai' ? renderMarkdown(msg.text) : msg.text" class="text-small leading-relaxed"></div>
                        <div x-show="msg.role === 'ai' && msg.data && msg.data.type === 'suggestions'" x-html="renderSuggestions(msg.data.suggestions)" class="mt-3"></div>
                    </div>
                </div>
            </template>

            <div x-show="messages.length === 0" class="text-center py-12">
                <div class="w-16 h-16 rounded-[20px] mx-auto flex items-center justify-center mb-4" style="background: var(--gradient-royal);">
                    <span class="text-white font-bold text-[22px]">AI</span>
                </div>
                <h3 class="text-h3 text-text">Pembantu maya sedia membantu</h3>
                <p class="text-body text-secondary mt-2 max-w-sm mx-auto">Tanya saya tentang jenis bantuan, dokumen, atau status permohonan anda.</p>
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-2 max-w-md mx-auto">
                    <button type="button" @click="draft='Apakah bantuan yang boleh saya mohon?'; send()" class="text-left p-3 rounded-[12px] bg-card border border-divider hover:border-primary transition-fast">
                        <i class="fa-solid fa-hand-holding-heart text-primary text-[12px]"></i>
                        <p class="text-small font-medium text-text mt-1.5">Jenis bantuan</p>
                    </button>
                    <button type="button" @click="draft='Apakah dokumen yang diperlukan?'; send()" class="text-left p-3 rounded-[12px] bg-card border border-divider hover:border-primary transition-fast">
                        <i class="fa-solid fa-folder-open text-primary text-[12px]"></i>
                        <p class="text-small font-medium text-text mt-1.5">Dokumen diperlukan</p>
                    </button>
                    <button type="button" @click="draft='Bagaimana cara menghantar permohonan?'; send()" class="text-left p-3 rounded-[12px] bg-card border border-divider hover:border-primary transition-fast">
                        <i class="fa-solid fa-paper-plane text-primary text-[12px]"></i>
                        <p class="text-small font-medium text-text mt-1.5">Cara memohon</p>
                    </button>
                    <button type="button" @click="draft='Apakah status permohonan saya?'; send()" class="text-left p-3 rounded-[12px] bg-card border border-divider hover:border-primary transition-fast">
                        <i class="fa-solid fa-magnifying-glass-chart text-primary text-[12px]"></i>
                        <p class="text-small font-medium text-text mt-1.5">Status permohonan</p>
                    </button>
                </div>
            </div>

            <div x-show="loading" class="flex justify-start">
                <div class="bg-card border border-divider rounded-[20px] rounded-bl-md px-4 py-3 shadow-card">
                    <span class="inline-flex items-center gap-1.5 align-middle">
                        <span class="ai-typing-dot"></span>
                        <span class="ai-typing-dot"></span>
                        <span class="ai-typing-dot"></span>
                    </span>
                </div>
            </div>
        </div>

        <form @submit.prevent="send()" class="border-t border-divider p-3 md:p-4 bg-card flex items-end gap-3">
            <div class="flex-1 relative">
                <textarea x-model="draft" @keydown.enter.prevent="if (!event.shiftKey) send()" rows="1" placeholder="Tanya pembantu maya..." class="input-base resize-none pr-11 py-3 max-h-32"></textarea>
                <i class="fa-solid fa-message text-tertiary absolute right-3.5 bottom-3 text-[13px] pointer-events-none"></i>
            </div>
            <button type="submit" :disabled="!draft.trim() || loading" class="btn btn-primary btn-icon btn-lg shrink-0" aria-label="Hantar mesej">
                <i class="fa-solid fa-paper-plane text-[14px]"></i>
            </button>
        </form>
    </x-app-card>

    <p class="text-[11px] text-tertiary mt-3 text-center">Pembantu maya memberi panduan am sahaja. Keputusan kelayakan muktamad adalah tertakluk kepada penilaian rasmi MAIJ.</p>
</div>
@endsection

@push('scripts')
<script>
function portalAiChat() {
    return {
        loading: false,
        messages: [],
        draft: '',
        send() {
            const text = this.draft.trim();
            if (!text || this.loading) return;
            this.messages.push({ id: Date.now(), role: 'user', text, data: null });
            this.draft = '';
            this.loading = true;
            this.scrollToBottom();
            fetch('/user/ai/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: text }),
            })
            .then(r => r.json())
            .then(data => {
                this.messages.push({ id: Date.now() + 1, role: 'ai', text: data.reply || '', data: data.data || { type: 'text' } });
            })
            .catch(() => {
                this.messages.push({ id: Date.now() + 1, role: 'ai', text: 'Maaf, berlaku ralat sambungan. Sila cuba lagi.', data: { type: 'text' } });
            })
            .finally(() => {
                this.loading = false;
                this.$nextTick(() => this.scrollToBottom());
            });
        },
        scrollToBottom() {
            const el = this.$refs.messages;
            if (el) setTimeout(() => { el.scrollTop = el.scrollHeight; }, 30);
        },
        escapeHtml(s) {
            return (s ?? '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        },
        renderMarkdown(s) {
            return this.escapeHtml(s).replace(/\*\*(.+?)\*\*/g, '<span class="font-semibold">$1</span>').replace(/`([^`]+)`/g, '<code class="bg-muted px-1 rounded">$1</code>').replace(/\n/g, '<br>');
        },
        renderSuggestions(list) {
            const esc = (s) => this.escapeHtml(s);
            const items = (list || []).map((s) => `<button type="button" @click="draft = ${esc(JSON.stringify(s.prompt))}; send()" class="w-full text-left px-3 py-2 bg-card border border-divider rounded-[10px] flex items-center gap-2.5 hover:border-primary transition-apple">
                <span class="w-6 h-6 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0"><i class="fa-solid ${esc(s.icon)} text-[10px]"></i></span>
                <span class="text-small font-medium truncate flex-1">${esc(s.label)}</span>
            </button>`).join('');
            return `<div class="grid grid-cols-1 gap-1.5">${items}</div>`;
        },
    };
}
</script>
@endpush
