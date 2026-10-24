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
];
