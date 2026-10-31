<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentVerifiedValue extends Model
{
    protected $fillable = [
        'document_import_id',
        'field_key',
        'field_group',
        'value_text',
        'value_numeric',
        'value_date',
        'value_json',
        'confidence',
        'confidence_source',
        'source_page',
        'verified_by_admin_id',
        'verified_by_portal_user_id',
        'applied_to_type',
        'applied_to_id',
    ];

    protected $casts = [
        'value_numeric' => 'decimal:2',
        'value_date' => 'date',
        'value_json' => 'array',
        'confidence' => 'decimal:2',
    ];

    public function documentImport()
    {
        return $this->belongsTo(DocumentImport::class);
    }
}
