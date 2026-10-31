<?php

return [
    'documents' => [
        'title' => 'My Documents',
        'subtitle' => 'Upload and manage your application supporting documents.',

        'upload' => [
            'title' => 'Upload Document',
            'description' => 'PDF, JPG or PNG format. Maximum 10MB.',
            'type' => 'Document Type',
            'file' => 'File',
            'submit' => 'Upload',
            'optional' => 'Optional',
            'uploading' => 'Uploading...',
        ],

        'types' => [
            'ic' => 'Identity Card Copy',
            'salary' => 'Payslip / Income Statement',
            'supporting' => 'Supporting Document',
            'other' => 'Others',
        ],

        'empty' => [
            'title' => 'No documents',
            'description' => 'Upload your supporting documents using the form above.',
        ],

        'status' => [
            'uploaded' => 'Uploaded',
            'processing' => 'Processing',
            'extracted' => 'Text Extracted',
            'needs_review' => 'Awaiting Verification',
            'committed' => 'Verified',
            'failed' => 'Failed',
            'rejected' => 'Rejected',
        ],

        'fallback_type' => 'Supporting Document',
        'ocr_confidence' => 'OCR Confidence: :value%',
        'download' => 'Download',
        'review' => 'Review',
        'reprocess' => 'Reprocess',
    ],

    'letters' => [
        'title' => 'Letters & Receipts',
        'subtitle' => 'Download your official letters and application receipts.',

        'empty' => [
            'title' => 'No letters or receipts',
            'description' => 'Approval letters, rejection notices and application receipts will be displayed here.',
        ],

        'fallback_assistance' => 'Assistance Application',
        'approved_letter' => 'Approval Letter',
        'rejection_notice' => 'Rejection Notice',
        'receipt' => 'Application Receipt',

        'status' => [
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'under_review' => 'Under Review',
            'submitted' => 'Submitted',
        ],
    ],

    'flash' => [
        'uploaded' => 'Document uploaded successfully.',
        'duplicate' => 'The same document was uploaded before. You have been taken to the existing record.',
        'file_not_found' => 'File not found in storage.',
        'reprocess_only_borang' => 'Only the MAIJ Assistance Form can be reprocessed.',
        'already_committed' => 'This record has been submitted — no need to reprocess.',
        'reprocessing' => 'The document is being reprocessed.',
    ],

    'notifications' => [
        'uploaded_title' => 'Document Uploaded',
        'uploaded_body' => 'Your document has been uploaded successfully.',
    ],
];
