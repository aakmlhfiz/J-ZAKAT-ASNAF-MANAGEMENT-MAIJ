@php
    $isAdmin = $isAdmin ?? false;
    $rejectRoute = $rejectRoute ?? null;
    $replacementRoute = $replacementRoute ?? null;
    $pageCount = app(\App\Services\Document\DocumentPreviewService::class)->pageCount($import);
    $extracted = $import->ocr_result['extracted_data'] ?? $import->extracted_data ?? [];
    $warnings = $import->ocr_result['warnings'] ?? [];
    $transactions = $import->ocr_result['meta']['transactions'] ?? [];
    $verified = $import->status === 'committed';
    $verifiedFields = $import->verified_data['fields'] ?? [];
    $isImage = str_starts_with((string) $import->mime_type, 'image/');
    $profile = $import->profilAsnaf;
    $application = $import->application;
    $household = $import->household;
    $hasTargets = $profile || $application || $household;
    $statusTone = $import->statusTone();
@endphp

<div
    x-data="{
        page: 1,
        maxPage: {{ max(1, $pageCount) }},
        zoom: 1,
        pageUrl: @js($previewPageUrl),
        nextPage() { if (this.page < this.maxPage) this.page++ },
        prevPage() { if (this.page > 1) this.page-- },
        zoomIn() { this.zoom = Math.min(3, this.zoom + 0.25) },
        zoomOut() { this.zoom = Math.max(1, this.zoom - 0.25) },
        resetZoom() { this.zoom = 1 },
    }"
    class="grid grid-cols-1 xl:grid-cols-12 gap-6"
>
    {{-- LEFT: secure preview --}}
    <div class="xl:col-span-5 space-y-4">
        <x-app-card class="p-4" :hover="false">
            <div class="flex items-center justify-between gap-3 mb-3">
                <div>
                    <p class="text-eyebrow text-tertiary uppercase tracking-[0.08em]">{{ __('document_ocr.review.original_document') }}</p>
                    <p class="text-small text-text font-semibold truncate mt-0.5">{{ $import->original_filename }}</p>
                </div>
                <x-status-badge :status="$statusTone">{{ $import->statusLabel() }}</x-status-badge>
            </div>

            <div class="relative bg-muted rounded-[8px] border border-divider overflow-auto" style="max-height: 620px;">
                <div class="min-h-[280px] flex items-start justify-center p-2 transition-transform duration-200" :style="`transform: scale(${zoom}); transform-origin: top center;`">
                    <img :src="pageUrl.replace('__PAGE__', page)" alt="{{ __('document_ocr.review.original_document') }}"
                         class="max-w-full h-auto rounded-[4px] bg-white">
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between gap-2 flex-wrap">
                <div class="flex items-center gap-1" x-show="maxPage > 1">
                    <button type="button" class="btn btn-sm btn-outline" @click="prevPage()" :disabled="page <= 1" aria-label="{{ __('document_ocr.review.page') }}">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>
                    <span class="text-small text-secondary px-2">
                        {{ __('document_ocr.review.page') }} <span x-text="page"></span> {{ __('document_ocr.review.of') }} <span x-text="maxPage"></span>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline" @click="nextPage()" :disabled="page >= maxPage" aria-label="{{ __('document_ocr.review.page') }}">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline" @click="zoomOut()" aria-label="{{ __('document_ocr.review.zoom_out') }}"><i class="fa-solid fa-magnifying-glass-minus text-[11px]"></i></button>
                    <button type="button" class="btn btn-sm btn-outline" @click="resetZoom()" aria-label="{{ __('document_ocr.review.reset_zoom') }}"><i class="fa-solid fa-arrows-rotate text-[11px]"></i></button>
                    <button type="button" class="btn btn-sm btn-outline" @click="zoomIn()" aria-label="{{ __('document_ocr.review.zoom_in') }}"><i class="fa-solid fa-magnifying-glass-plus text-[11px]"></i></button>
                </div>
            </div>

            <a href="{{ $fileRoute }}" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1.5 text-small text-primary hover:underline">
                <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                {{ __('document_ocr.review.open_original') }}
            </a>
        </x-app-card>

        <x-app-card class="p-4" :hover="false">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-eyebrow text-tertiary uppercase tracking-[0.08em]">{{ __('document_ocr.review.confidence_title') }}</p>
                    <p class="text-small text-secondary mt-1">{{ __('document_ocr.review.confidence_average') }}</p>
                </div>
                @if($confidence !== null)
                    <span class="text-metric-sm text-text tabular-nums">{{ number_format((float) $confidence, 1) }}%</span>
                @else
                    <span class="text-small text-tertiary">{{ __('document_ocr.review.not_available') }}</span>
                @endif
            </div>
            <p class="text-[11px] text-tertiary mt-2">{{ $confidence !== null ? __('document_ocr.review.confidence_available') : __('document_ocr.review.confidence_unavailable') }}</p>
        </x-app-card>

        @if($isAdmin && $rejectRoute && !$verified)
            <x-app-card class="p-4" :hover="false">
                <details>
                    <summary class="cursor-pointer text-small font-semibold text-danger flex items-center gap-2">
                        <i class="fa-solid fa-circle-xmark text-[12px]"></i> {{ __('document_ocr.review.reject') }}
                    </summary>
                    <form method="POST" action="{{ $rejectRoute }}" class="mt-3 space-y-2">
                        @csrf
                        <label class="block text-small font-medium text-text">{{ __('document_ocr.review.reject_reason') }}</label>
                        <textarea name="rejection_reason" rows="3" class="input-base w-full" required minlength="5">{{ old('rejection_reason') }}</textarea>
                        <button type="submit" class="btn btn-sm btn-destructive">{{ __('document_ocr.review.reject_submit') }}</button>
                    </form>
                </details>
            </x-app-card>
        @endif

        @if(!$isAdmin && $replacementRoute && !$verified)
            <x-app-card class="p-4" :hover="false">
                <form method="POST" action="{{ $replacementRoute }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <label class="block text-small font-semibold text-text">{{ __('document_ocr.review.replace_document') }}</label>
                    <p class="text-[11px] text-tertiary">{{ __('portal_documents.documents.upload.description') }}</p>
                    <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" class="input-base w-full" required>
                    <button type="submit" class="btn btn-sm btn-outline">{{ __('document_ocr.review.upload_replacement') }}</button>
                </form>
            </x-app-card>
        @endif
    </div>

    {{-- RIGHT: extracted fields / verification --}}
    <div class="xl:col-span-7 space-y-4">
        @if(session('success'))
            <x-app-card class="p-4 border-success/30" :hover="false">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-check text-success mt-0.5"></i>
                    <div>
                        <p class="text-small font-semibold text-text">{{ __('document_ocr.review.verified_title') }}</p>
                        <p class="text-small text-secondary mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            </x-app-card>
        @endif

        @if($import->status === 'rejected' && $import->rejection_reason)
            <x-app-card class="p-4 border-danger/30" :hover="false">
                <p class="text-small font-semibold text-danger">{{ __('document_ocr.admin.rejected_title') }}</p>
                <p class="text-small text-secondary mt-1">{{ $import->rejection_reason }}</p>
            </x-app-card>
        @endif

        @if($warnings !== [])
            <x-app-card class="p-4 border-warning/30" :hover="false">
                <p class="text-small font-semibold text-text flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation text-warning"></i> {{ __('document_ocr.review.warnings') }}</p>
                <ul class="mt-2 space-y-1 text-small text-secondary list-disc pl-5">
                    @foreach($warnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </x-app-card>
        @endif

        @if($verified)
            <x-app-card class="p-4 border-success/30" :hover="false">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-shield-halved text-success mt-0.5"></i>
                    <div>
                        <p class="text-small font-semibold text-text">{{ __('document_ocr.review.verified_title') }}</p>
                        <p class="text-small text-secondary mt-0.5">{{ __('document_ocr.review.verified_text') }}</p>
                    </div>
                </div>
            </x-app-card>
        @endif

        @if(!$verified && in_array($import->status, ['needs_review', 'extracted'], true))
            <form method="POST" action="{{ $verifyRoute }}">
                @csrf

                <x-app-card class="p-5" :hover="false">
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div>
                            <p class="text-body font-semibold text-text">{{ __('document_ocr.review.extracted_title') }}</p>
                            <p class="text-small text-secondary mt-0.5">{{ __('document_ocr.review.extracted_description') }}</p>
                        </div>
                        <span class="text-[11px] text-tertiary">{{ $import->ocr_engine ?? '—' }}</span>
                    </div>

                    @if($errors->any())
                        <div class="mb-4 p-3 rounded-[8px] bg-danger-soft border border-danger/20 text-small text-danger">
                            <ul class="list-disc pl-5 space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @forelse($groups as $groupKey => $group)
                        <div class="mb-5 last:mb-0">
                            <p class="text-eyebrow text-tertiary uppercase tracking-[0.08em] mb-2">{{ $group['label'] }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($group['fields'] as $key => $field)
                                    @php
                                        $source = $extracted[$key] ?? [];
                                        $value = old('fields.'.$key, $verifiedFields[$key]['value'] ?? $source['value'] ?? '');
                                        $fieldConfidence = $source['confidence'] ?? null;
                                        $needsReview = (bool) ($source['needs_review'] ?? true);
                                        $warning = $source['warnings'] ?? [];
                                        $colSpan = $field['type'] === 'textarea' ? 'sm:col-span-2' : '';
                                    @endphp
                                    <div class="{{ $colSpan }}">
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <label for="ocr-field-{{ $key }}" class="block text-small font-medium text-text">{{ $field['label'] }}</label>
                                            <span class="text-[10px] font-semibold {{ $fieldConfidence !== null && $fieldConfidence >= 70 ? 'text-success' : 'text-warning' }}">
                                                {{ $fieldConfidence !== null ? number_format((float) $fieldConfidence, 0).'%' : __('document_ocr.review.not_available') }}
                                            </span>
                                        </div>
                                        @if($field['type'] === 'textarea')
                                            <textarea id="ocr-field-{{ $key }}" name="fields[{{ $key }}]" rows="3" class="input-base w-full @if($needsReview) border-warning/40 @endif">{{ $value }}</textarea>
                                        @else
                                            <input id="ocr-field-{{ $key }}" type="{{ $field['type'] === 'amount' ? 'number' : ($field['type'] === 'date' ? 'date' : 'text') }}"
                                                   @if($field['type'] === 'amount') step="0.01" min="0" @endif
                                                   name="fields[{{ $key }}]" value="{{ $value }}"
                                                   class="input-base w-full @if($needsReview) border-warning/40 @endif">
                                        @endif
                                        @if($source['page'] ?? null)
                                            <p class="text-[10px] text-tertiary mt-1">{{ __('document_ocr.review.source_page', ['page' => $source['page']]) }}</p>
                                        @endif
                                        @foreach($warning as $warningText)
                                            <p class="text-[10px] text-warning mt-1">{{ $warningText }}</p>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-small text-secondary">{{ __('document_ocr.review.no_fields') }}</p>
                    @endforelse

                    @if($transactions !== [])
                        <div class="mt-5 pt-4 border-t border-divider">
                            <p class="text-eyebrow text-tertiary uppercase tracking-[0.08em] mb-2">{{ __('document_ocr.review.bank_transactions') }}</p>
                            <p class="text-[11px] text-warning mb-2">{{ __('document_ocr.review.credits_not_income') }}</p>
                            <div class="overflow-x-auto">
                                <table class="app-table">
                                    <thead><tr><th>Tarikh</th><th>Keterangan</th><th class="text-right">Jumlah</th><th>Jenis</th></tr></thead>
                                    <tbody>
                                        @foreach($transactions as $transaction)
                                            <tr>
                                                <td>{{ $transaction['date'] }}</td>
                                                <td>{{ $transaction['description'] }}</td>
                                                <td class="text-right">{{ number_format((float) $transaction['amount'], 2) }}</td>
                                                <td>{{ $transaction['type'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </x-app-card>

                @if($hasTargets)
                    <x-app-card class="p-5" :hover="false">
                        <p class="text-body font-semibold text-text">{{ __('document_ocr.review.suggested_updates') }}</p>
                        <p class="text-small text-secondary mt-0.5">{{ __('document_ocr.review.suggested_updates_description') }}</p>
                        <div class="mt-4 space-y-2">
                            @if($profile)
                                <label class="flex items-center gap-2 text-small text-text">
                                    <input type="checkbox" name="apply_profile" value="1" checked class="rounded border-divider">
                                    {{ __('document_ocr.review.apply_profile') }}
                                </label>
                            @endif
                            @if($application)
                                <label class="flex items-center gap-2 text-small text-text">
                                    <input type="checkbox" name="apply_application" value="1" checked class="rounded border-divider">
                                    {{ __('document_ocr.review.apply_application') }}
                                </label>
                            @endif
                            @if($household)
                                <label class="flex items-center gap-2 text-small text-text">
                                    <input type="checkbox" name="apply_household" value="1" checked class="rounded border-divider">
                                    {{ __('document_ocr.review.apply_household') }}
                                </label>
                            @endif
                            <label class="flex items-center gap-2 text-small text-text">
                                <input type="checkbox" name="replace_existing" value="1" class="rounded border-divider">
                                {{ __('document_ocr.review.replace_existing') }}
                            </label>
                        </div>

                        @if(in_array($type, [\App\Enums\DocumentType::SalarySlip, \App\Enums\DocumentType::EmployerLetter, \App\Enums\DocumentType::IncomeVerificationLetter], true))
                            <div class="mt-4 max-w-sm">
                                <label class="block text-small font-medium text-text mb-1">{{ __('document_ocr.review.income_field') }}</label>
                                <select name="income_field" class="input-base w-full">
                                    @if($type === \App\Enums\DocumentType::SalarySlip)
                                        <option value="gross_salary">{{ __('document_ocr.review.income_gross') }}</option>
                                        <option value="net_salary">{{ __('document_ocr.review.income_net') }}</option>
                                        <option value="basic_salary">{{ __('document_ocr.review.income_basic') }}</option>
                                    @else
                                        <option value="monthly_salary">{{ __('document_ocr.review.income_monthly') }}</option>
                                    @endif
                                </select>
                            </div>
                        @endif
                    </x-app-card>
                @endif

                <div class="flex items-center gap-3 flex-wrap">
                    <x-app-button type="submit" variant="primary" size="lg" icon="check-circle">{{ __('document_ocr.review.save_verify') }}</x-app-button>
                    <a href="{{ $backRoute }}" class="btn btn-lg btn-ghost">{{ __('document_ocr.review.back') }}</a>
                </div>
            </form>
        @elseif($verified)
            <x-app-card class="p-5" :hover="false">
                <p class="text-eyebrow text-tertiary uppercase tracking-[0.08em] mb-2">{{ __('document_ocr.review.extracted_title') }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($groups as $group)
                        @foreach($group['fields'] as $key => $field)
                            @php $value = $verifiedFields[$key]['value'] ?? ($extracted[$key]['value'] ?? null); @endphp
                            @if($value !== null)
                                <div class="p-3 rounded-[8px] bg-muted/60">
                                    <p class="text-[10px] uppercase tracking-[0.06em] text-tertiary">{{ $field['label'] }}</p>
                                    <p class="text-small text-text font-medium mt-0.5 break-words">{{ $value }}</p>
                                </div>
                            @endif
                        @endforeach
                    @endforeach
                </div>
            </x-app-card>
        @endif
    </div>
</div>
