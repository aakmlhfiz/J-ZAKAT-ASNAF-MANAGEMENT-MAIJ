<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DocumentImport extends Model
{
    protected $fillable = [
        'user_id',
        'portal_user_id',
        'application_id',
        'replaces_document_id',
        'duplicate_of_id',
        'original_filename',
        'stored_path',
        'mime_type',
        'file_size',
        'page_count',
        'file_hash',
        'document_type',
        'template',
        'status',
        'progress',
        'ocr_text',
        'ocr_engine',
        'ocr_started_at',
        'ocr_completed_at',
        'extracted_data',
        'overall_confidence',
        'matched_labels',
        'validation_results',
        'household_members_extracted',
        'borang_data',
        'ocr_result',
        'duplicate_warnings',
        'assistance_histories',
        'profil_asnaf_ic',
        'household_id',
        'error_message',
        'verified_at',
        'verified_by_admin_id',
        'verified_by_portal_user_id',
        'reviewed_at',
        'reviewed_by_admin_id',
        'rejection_reason',
        'verified_data',
        'metadata',
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'matched_labels' => 'array',
        'validation_results' => 'array',
        'household_members_extracted' => 'array',
        'borang_data' => 'array',
        'ocr_result' => 'array',
        'duplicate_warnings' => 'array',
        'assistance_histories' => 'array',
        'overall_confidence' => 'decimal:2',
        'progress' => 'integer',
        'ocr_started_at' => 'datetime',
        'ocr_completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'verified_data' => 'array',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(Admin::class);
    }

    public function portalUser()
    {
        return $this->belongsTo(PortalUser::class, 'portal_user_id');
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function profilAsnaf()
    {
        return $this->belongsTo(ProfilAsnaf::class, 'profil_asnaf_ic', 'ic');
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }

    public function verifiedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'verified_by_admin_id');
    }

    public function reviewedByAdmin()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by_admin_id');
    }

    public function verifiedValues()
    {
        return $this->hasMany(DocumentVerifiedValue::class);
    }

    public function duplicateOf()
    {
        return $this->belongsTo(self::class, 'duplicate_of_id');
    }

    public function replaces()
    {
        return $this->belongsTo(self::class, 'replaces_document_id');
    }

    public function type(): DocumentType
    {
        return DocumentType::fromStored($this->document_type);
    }

    public function isGenericDocument(): bool
    {
        return ! $this->type()->isBorang();
    }

    public function statusLabel(): string
    {
        return __('document_ocr.status.'.$this->status);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'committed' => 'success',
            'needs_review', 'processing' => 'warning',
            'failed', 'rejected' => 'danger',
            'extracted' => 'info',
            default => 'secondary',
        };
    }

    public function fileExists(): bool
    {
        return $this->stored_path
            && Storage::disk(config('ocr.disk', 'local'))->exists($this->stored_path);
    }

    public function maskedIc(): ?string
    {
        $ic = $this->extracted_data['ic_number']['value'] ?? $this->extracted_data['ic']['value'] ?? null;
        if (! $ic) {
            return null;
        }
        $digits = preg_replace('/\D/', '', (string) $ic);

        return strlen($digits) >= 4
            ? str_repeat('*', max(0, strlen($digits) - 4)).substr($digits, -4)
            : '****';
    }
}
