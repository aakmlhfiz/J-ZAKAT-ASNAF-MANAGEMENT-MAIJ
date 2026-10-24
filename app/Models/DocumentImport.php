<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentImport extends Model
{
    protected $fillable = [
        'user_id',
        'portal_user_id',
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
    ];

    public function user()
    {
        return $this->belongsTo(Admin::class);
    }

    public function profilAsnaf()
    {
        return $this->belongsTo(ProfilAsnaf::class, 'profil_asnaf_ic', 'ic');
    }

    public function household()
    {
        return $this->belongsTo(Household::class);
    }
}
