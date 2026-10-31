<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\DocumentImport;
use App\Models\HouseholdMember;
use App\Models\PortalUser;
use App\Models\ProfilAsnaf;
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
        return DocumentType::portalOptions();
    }

    /**
     * Persist a supporting document for the authenticated applicant.
     */
    public static function store(
        PortalUser $user,
        UploadedFile $file,
        string $documentType,
        ?int $applicationId = null,
        ?int $replacesDocumentId = null,
    ): DocumentImport {
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid().'.'.$ext;
        $path = config('ocr.path', 'ocr');
        $storedPath = $file->storeAs($path, $filename, config('ocr.disk', 'local'));

        $type = DocumentType::fromStored($documentType);
        $profile = $user->ic_no
            ? ProfilAsnaf::where('ic', preg_replace('/\D/', '', (string) $user->ic_no))->first()
            : null;
        $member = $profile
            ? HouseholdMember::where('profil_asnaf_id', $profile->id)->first()
            : null;

        return DocumentImport::create([
            'portal_user_id' => $user->id,
            'application_id' => $applicationId,
            'replaces_document_id' => $replacesDocumentId,
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_hash' => sha1_file($file->getRealPath()),
            'document_type' => $documentType,
            'template' => $type->label(),
            'status' => 'uploaded',
            'page_count' => 1,
            'profil_asnaf_ic' => $profile?->ic,
            'household_id' => $member?->household_id,
            'metadata' => [
                'source' => 'portal_upload',
                'original_type' => $documentType,
                'normalized_type' => $type->value,
            ],
        ]);
    }

    /**
     * Find an existing document with the same content for this applicant.
     */
    public static function findDuplicate(PortalUser $user, string $hash): ?DocumentImport
    {
        return DocumentImport::where('portal_user_id', $user->id)
            ->where('file_hash', $hash)
            ->latest()
            ->first();
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
