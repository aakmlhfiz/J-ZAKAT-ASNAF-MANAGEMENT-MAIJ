<?php

namespace App\Services;

use App\Models\DocumentImport;
use App\Models\PortalUser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortalDocumentService
{
    public const DOC_TYPE_IC = 'ic';

    public const DOC_TYPE_SALARY = 'salary';

    public const DOC_TYPE_SUPPORTING = 'supporting';

    public const DOC_TYPE_OTHER = 'other';

    /**
     * Human-friendly labels for the document type select.
     */
    public static function typeOptions(): array
    {
        return [
            self::DOC_TYPE_IC => 'Salinan Kad Pengenalan',
            self::DOC_TYPE_SALARY => 'Slip Gaji / Penyata Pendapatan',
            self::DOC_TYPE_SUPPORTING => 'Dokumen Sokongan',
            self::DOC_TYPE_OTHER => 'Lain-lain',
        ];
    }

    /**
     * Persist a supporting document for the authenticated applicant.
     *
     * Generic supporting documents are stored as "uploaded" (no OCR pass);
     * Borang Bantuan MAIJ goes through the dedicated scan pipeline instead.
     */
    public static function store(PortalUser $user, UploadedFile $file, string $documentType): DocumentImport
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid().'.'.$ext;
        $path = config('ocr.path', 'ocr');
        $storedPath = $file->storeAs($path, $filename, config('ocr.disk', 'local'));

        return DocumentImport::create([
            'portal_user_id' => $user->id,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_hash' => sha1_file($file->getRealPath()),
            'document_type' => $documentType,
            'template' => self::typeOptions()[$documentType] ?? 'Dokumen Sokongan',
            'status' => 'uploaded',
            'page_count' => 1,
        ]);
    }

    /**
     * Resolve the absolute path for a stored document (authorisation is the
     * caller's responsibility).
     */
    public static function path(DocumentImport $import): string
    {
        return Storage::disk(config('ocr.disk', 'local'))->path($import->stored_path);
    }
}
