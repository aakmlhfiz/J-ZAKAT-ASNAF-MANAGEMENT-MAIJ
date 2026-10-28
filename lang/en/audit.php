<?php

return [
    'module' => 'Audit Review',
    'module_subtitle' => 'Internal MAIJ audit review workspace for asnaf profiles & households',

    'status' => [
        'draft' => 'Draft',
        'in_progress' => 'In Progress',
        'pending_review' => 'Pending Review',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'decision' => [
        'compliant' => 'Compliant',
        'minor_issues' => 'Minor Issues',
        'major_issues' => 'Major Issues',
        'escalate' => 'Escalate',
    ],

    'review_status' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ],

    'severity' => [
        'critical' => 'Critical',
        'high' => 'High',
        'medium' => 'Medium',
        'low' => 'Low',
    ],

    'resolution' => [
        'false_positive' => 'False Positive',
        'acknowledged' => 'Acknowledged',
        'escalated' => 'Escalated',
        'resolved' => 'Resolved',
    ],

    'risk' => [
        'critical' => 'Critical',
        'high' => 'High',
        'medium' => 'Medium',
        'low' => 'Low',
    ],

    'step' => [
        'income_check' => 'Review Household Income',
        'kifayah_compare' => 'Compare with Had Kifayah',
        'commitments_check' => 'Review Key Commitments',
        'assistance_check' => 'Review Assistance Received',
        'anomaly_check' => 'Identify Anomalies',
        'record_action' => 'Record Officer Action',
    ],

    'flag' => [
        'INCOME_SPIKE' => 'Unusually High Income',
        'DUPLICATE_IC' => 'Duplicate Identity Card Number',
        'ASSISTANCE_OVERLOAD' => 'Active Assistance Overload',
        'EXPIRED_ASSISTANCE' => 'Expired Active Assistance',
        'HIGH_RENT_RATIO' => 'High Rent Ratio',
        'INCOME_NEAR_KIFAYAH' => 'Income Near Kifayah Line',
        'MISSING_DOCUMENT' => 'Assistance Without Document Reference',
        'OFFICER_FREQUENCY' => 'High Officer Approval Frequency',
    ],

    'four_eyes' => 'The reviewer cannot be the same as the audit officer (4-eyes principle).',
    'overdue_days' => 'The audit case has exceeded :days days without completion.',

    'category' => [
        'auth' => 'Authentication',
        'application' => 'Application',
        'data' => 'Data',
        'security' => 'Security',
        'system' => 'System',
        'money' => 'Financial',
        'audit' => 'Audit Review',
    ],

    'role' => [
        'admin' => 'Administrator',
        'portal' => 'Applicant',
        'system' => 'System',
    ],

    'logs' => [
        'title' => 'Audit Log',
        'subtitle' => 'J-ZAKAT system activity records',
        'eyebrow' => 'Administration',
        'export_csv' => 'Export CSV',

        'stats' => [
            'total' => 'Total Records',
            'today' => "Today's Activity",
            'logins_today' => "Today's Logins",
            'unique_users' => 'Unique Users Today',
        ],

        'filter' => [
            'search' => 'Search',
            'search_placeholder' => 'Email, IP, action...',
            'user' => 'User',
            'role' => 'Role',
            'category' => 'Category',
            'action' => 'Action',
            'from' => 'From',
            'to' => 'To',
            'all' => 'All',
            'apply' => 'Filter',
            'reset' => 'Reset',
        ],

        'columns' => [
            'datetime' => 'Date / Time',
            'user' => 'User',
            'category' => 'Category',
            'action' => 'Action',
            'description' => 'Description',
            'ip' => 'IP',
            'browser_os' => 'Browser · OS',
            'device' => 'Device',
        ],

        'view_metadata' => 'View metadata',
        'empty' => 'No audit log records found.',
    ],

    'logger' => [
        'login_success' => 'Login successful',
        'login_failed' => 'Failed login attempt',
        'logout' => 'Logged out',
        'password_changed' => 'Account password changed',
        'password_reset' => 'Password reset via email',
        'profile_updated' => 'Profile updated',
        'application_created' => 'New asnaf profile created: :name (:ic)',
        'application_updated' => 'Asnaf profile updated: :name (:ic)',
        'application_approved' => 'Application approved: :name (:ic)',
        'application_rejected' => 'Application rejected: :name (:ic)',
        'application_submitted' => 'New application submitted by applicant: :name',
        'documents_requested' => 'Additional documents requested: :name (:ic)',
        'csv_imported' => 'CSV import: :success successful, :failed failed (:total total)',
        'pdf_imported' => 'PDF/OCR import: :file',
        'pdf_committed' => 'PDF/OCR data confirmed: :file → :name',
        'borang_scan_uploaded' => 'MAIJ Assistance Form scan uploaded: :file',
        'borang_scan_committed' => 'MAIJ Assistance Form saved & submitted: :name (:ic)',
        'document_uploaded' => 'Document uploaded: :file',
        'ai_assessment' => 'AI assessment run: :name (:ic)',
        'money_created' => 'Assistance transaction recorded: :type RM :amount',
        'money_updated' => 'Assistance transaction updated: :type RM :amount',
        'money_deleted' => 'Assistance transaction deleted: :type RM :amount',
    ],

    'activity' => [
        'login' => 'Login successful',
        'logout' => 'Logged out',
        'profile_updated' => 'Profile updated',
        'password_changed' => 'Password changed',
        'photo_uploaded' => 'Profile photo updated',
        'settings_updated' => 'Settings :key updated',
        'two_factor_enabled' => 'Two-factor authentication enabled',
        'two_factor_disabled' => 'Two-factor authentication disabled',
        'account_password_changed' => 'Account password has been changed',
    ],

    'export' => [
        'id' => 'ID',
        'created_at' => 'Created At',
        'user_id' => 'User ID',
        'user_role' => 'User Role',
        'user_email' => 'User Email',
        'category' => 'Category',
        'action' => 'Action',
        'description' => 'Description',
        'ip_address' => 'IP Address',
        'browser' => 'Browser',
        'device' => 'Device',
        'os' => 'OS',
        'metadata' => 'Metadata',
    ],
];
