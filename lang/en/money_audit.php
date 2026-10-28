<?php

return [
    'title' => 'Financial Audit',
    'subtitle' => 'Audit of assistance transactions & fund movements for J-ZAKAT',
    'eyebrow' => 'Administration',
    'export_csv' => 'Export CSV',

    'stats' => [
        'total_approved' => 'Total Approved (RM)',
        'records' => 'Number of Records',
        'this_month' => 'This Month (RM)',
        'households' => 'Households Involved',
    ],

    'records_count' => ':count records',

    'tabs' => [
        'transactions' => 'Transactions',
        'trail' => 'Audit Trail',
    ],

    'filter' => [
        'search' => 'Search',
        'search_placeholder' => 'Reference, officer, member, household...',
        'trail_search_placeholder' => 'Email, IP, action...',
        'user' => 'User',
        'action' => 'Action',
        'jenis_bantuan' => 'Assistance Type',
        'status' => 'Status',
        'frequency' => 'Frequency',
        'from' => 'From',
        'to' => 'To',
        'all' => 'All',
        'apply' => 'Filter',
        'reset' => 'Reset',
    ],

    'status' => [
        'approved' => 'Approved',
        'pending' => 'In Process',
        'rejected' => 'Rejected',
    ],

    'frequency' => [
        'one_off' => 'One-off',
        'monthly' => 'Monthly',
    ],

    'columns' => [
        'datetime' => 'Date / Time',
        'user' => 'User',
        'action' => 'Action',
        'description' => 'Description',
        'metadata' => 'Metadata',
        'ip' => 'IP',
        'approved_date' => 'Approval Date',
        'household' => 'Household',
        'member' => 'Member',
        'jenis_bantuan' => 'Assistance Type',
        'amount' => 'Amount',
        'status' => 'Status',
        'officer' => 'Officer',
        'reference' => 'Reference No.',
    ],

    'per_month_short' => '/mo',

    'metadata' => [
        'type' => 'Type',
        'amount' => 'Amount',
        'reference' => 'Reference',
        'view_changes' => 'View changes',
    ],

    'empty_transactions' => 'No assistance transactions found.',
    'empty_trail' => 'No financial audit trail found.',

    'export' => [
        'id' => 'ID',
        'approved_date' => 'Approval Date',
        'household' => 'Household',
        'member' => 'Member',
        'jenis_bantuan' => 'Assistance Type',
        'amount' => 'Amount (RM)',
        'frequency' => 'Frequency',
        'status' => 'Status',
        'approval_officer' => 'Approval Officer',
        'reference' => 'Reference No.',
        'description' => 'Description',
    ],
];
