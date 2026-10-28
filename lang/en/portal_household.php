<?php

return [
    'household' => [
        'title' => 'My Household',
        'subtitle' => 'Household members linked to your identity card number.',

        'empty' => [
            'title' => 'No household record',
            'description' => 'Household information will appear once your application is processed or you scan the MAIJ Assistance Form.',
            'action' => 'Apply for Assistance',
        ],

        'summary' => [
            'members' => 'Total Members',
            'dependants' => 'Total Dependants',
            'income' => 'Household Income',
            'commitments' => 'Monthly Commitments',
        ],

        'head' => [
            'title' => 'Head of Family',
            'description' => 'Household head',
        ],

        'labels' => [
            'name' => 'Name',
            'ic' => 'IC No.',
            'income' => 'Income',
            'age' => 'Age',
            'occupation' => 'Occupation',
            'health' => 'Health',
            'active_assistance' => 'Active assistance',
            'years' => ':age years old',
        ],

        'members' => [
            'title' => 'Household Members',
            'description' => ':count members in total',
            'dependant' => 'Dependant',
            'student' => 'Student',
            'empty_title' => 'No members recorded',
            'empty_description' => 'Household members will appear here.',
        ],
    ],

    'finance' => [
        'title' => 'My Finances',
        'subtitle' => 'A financial summary of your household.',

        'empty' => [
            'title' => 'No financial information',
            'description' => 'Household financial information will appear once your application is processed.',
        ],

        'summary' => [
            'income' => 'Total Household Income',
            'commitments' => 'Total Commitments',
            'dependants' => 'Number of Dependants',
        ],

        'income' => [
            'title' => 'Income',
            'description' => 'Household income sources',
            'applicant' => 'Applicant Income',
            'spouse' => 'Spouse Income',
            'other' => 'Other Members Income',
            'total' => 'Total Income',
        ],

        'commitments' => [
            'title' => 'Monthly Commitments',
            'description' => 'Fixed household expenses',
            'recorded' => 'Total monthly commitments recorded',
        ],

        'balance' => [
            'label' => 'Remaining Income After Commitments',
            'surplus' => 'Surplus',
            'deficit' => 'Deficit',
        ],
    ],

    'assistance' => [
        'title' => 'My Assistance',
        'subtitle' => 'All assistance received by your household members.',

        'empty' => [
            'title' => 'No assistance record',
            'description' => 'Assistance received by your household will be displayed here after MAIJ approval.',
            'recorded_title' => 'No assistance recorded',
            'recorded_description' => 'Approved assistance will be displayed here.',
            'household_description' => 'Approved assistance for your household will be displayed here.',
        ],

        'totals' => [
            'monthly' => 'Monthly Assistance',
            'one_off' => 'One-Off Assistance',
            'total' => 'Grand Total',
        ],

        'per_month' => '/ month',
        'reference' => 'Reference No.: :reference',
    ],

    'hadkifayah' => [
        'title' => 'Had Kifayah Calculator',
        'subtitle' => 'Estimate the had kifayah based on your household information.',

        'household_info' => 'Household Information',

        'fields' => [
            'applicant_income' => 'Applicant Income (RM)',
            'spouse_income' => 'Spouse Income (RM)',
            'dependants' => 'Number of Household Dependants',
            'n_isteri_suami' => 'Wife / Husband',
            'n_ibu_bapa' => 'Parents',
            'n_dewasa_tidak_bekerja' => 'Unemployed Adults',
            'n_dewasa_bekerja_tanggung' => 'Dependent Working Adults',
            'n_ipt' => 'Higher Education',
            'n_remaja' => 'Teenagers',
            'n_kanak_rendah' => 'Young Children',
            'n_kanak_tadika' => 'Kindergarten Children',
            'monthly_expenses' => 'Monthly Expenses',
            'housing_type' => 'Housing Type',
            'own_home' => 'Own Home',
            'rented_home' => 'Rented',
            'utilities' => 'Utilities (RM)',
            'transport' => 'Transport (RM)',
            'medical' => 'Medical (RM)',
        ],

        'calculate' => 'Calculate Had Kifayah',
        'reset' => 'Reset',

        'result' => [
            'title' => 'Result',
            'status_label' => 'Eligibility Status',
            'confidence' => 'Confidence: :value%',
            'income_total' => 'Total Income',
            'had_kifayah' => 'Had Kifayah',
            'balance' => 'Balance',
            'reasoning' => 'Justification',
            'empty_hint' => 'Fill in the information on the left and click',
        ],
    ],
];
