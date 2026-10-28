<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Assistant (English)
    |--------------------------------------------------------------------------
    */

    'page' => [
        'title' => 'AI Assistant',
        'tab_title' => 'Ustaz Danial — J-ZAKAT',
        'subtitle' => 'MAIJ asnaf data assistant',
        'role' => 'MAIJ asnaf data assistant',
        'clear_history' => 'Clear history',
        'empty_title' => 'Ustaz Danial is ready to help',
        'empty_body' => 'Try asking me about applicants, statistics, or Had Kifayah.',
        'disclaimer' => 'Ustaz Danial provides analysis based on system data. Final decisions are subject to official MAIJ assessment.',
    ],

    'widget' => [
        'open' => 'Open Ustaz Danial',
        'close' => 'Close',
        'online' => 'online',
        'greeting' => 'Hi! I am Ustaz Danial',
        'greeting_body' => 'Ask me about applicants, statistics or Had Kifayah.',
        'placeholder' => 'Ask anything...',
        'send' => 'Send',
    ],

    'input' => [
        'placeholder' => 'Ask Ustaz Danial anything...',
        'send' => 'Send message',
    ],

    'suggestions' => [
        'heading' => 'Try asking me',
        'eligibility_stats' => 'Eligibility statistics',
        'generate_assessment' => 'Generate AI assessment',
        'search_applicant' => 'Search applicant',
        'kifayah_rate' => 'Had Kifayah rate',
        'prompt_eligibility_stats' => 'How many applicants are eligible this month?',
        'prompt_generate_assessment' => 'Generate an assessment for the latest applicant',
        'prompt_search_applicant' => 'Find the applicant named Ahmad',
        'prompt_kifayah_rate' => 'What is the current Had Kifayah rate?',
    ],

    'portal' => [
        'tab_title' => 'AI Assistant — Applicant Portal',
        'page_title' => 'AI Assistant',
        'title' => 'Virtual Assistant',
        'subtitle' => 'Guidance for MAIJ zakat assistance applications',
        'empty_title' => 'The virtual assistant is ready to help',
        'empty_body' => 'Ask me about assistance types, documents, or your application status.',
        'placeholder' => 'Ask the virtual assistant...',
        'send' => 'Send message',
        'disclaimer' => 'The virtual assistant provides general guidance only. Final eligibility decisions are subject to official MAIJ assessment.',
        'suggestions' => [
            'assistance_types' => 'Assistance types',
            'documents' => 'Required documents',
            'how_to_apply' => 'How to apply',
            'application_status' => 'Application status',
            'prompt_assistance_types' => 'What assistance can I apply for?',
            'prompt_documents' => 'What documents are required?',
            'prompt_how_to_apply' => 'How do I submit an application?',
            'prompt_application_status' => 'What is my application status?',
        ],
    ],

    'card' => [
        'stats' => 'Statistics',
        'profile' => 'Applicant Profile',
        'explanation' => 'Explanation',
        'result' => 'Result',
        'eligibility_result' => 'Eligibility Decision',
        'search_results' => 'Search Results (:count)',
        'ai_score' => 'AI Score',
        'suggestions' => 'Try asking me',
    ],

    'fields' => [
        'name' => 'Name',
        'ic' => 'IC No.',
        'income' => 'Income',
        'dependants' => 'Dependants',
        'rent' => 'Rent',
        'utilities' => 'Utilities',
        'had_kifayah' => 'Had Kifayah',
        'status' => 'Status',
    ],

    'values' => [
        'people' => ':count person|:count people',
    ],

    'errors' => [
        'connection' => 'Sorry, a connection error occurred. Please try again.',
        'no_reply' => 'Sorry, I cannot generate a response right now.',
        'unknown_tool' => 'Unknown tool: :name',
        'tool_execution' => 'Sorry, the data tool cannot be executed right now.',
    ],

    'tools' => [
        'get_applicant' => [
            'description' => 'Get the full asnaf applicant profile by 12-digit Malaysian IC number.',
            'ic_description' => '12-digit IC number without hyphens.',
            'label' => 'Find applicant by IC No.',
            'missing_ic' => 'IC No. is required to retrieve the applicant profile.',
            'not_found' => 'No applicant with IC No. **:ic** was found in the database.',
            'reply' => 'Here is the applicant profile for **:name**:',
            'reason_eligible' => 'Income is below the Had Kifayah. Eligible to be considered for assistance.',
            'reason_not_eligible' => 'Income exceeds the Had Kifayah. Not eligible at this time.',
            'reason_incomplete' => 'Financial data is incomplete.',
        ],

        'search_applicants' => [
            'description' => 'Search asnaf applicants by full or partial name, or by IC number.',
            'query_description' => 'Applicant name or IC number to search for.',
            'label' => 'Search applicant records',
            'missing_query' => 'A name or IC No. is required to search for applicants.',
            'empty_query' => 'Please provide a name or part of a name to search. Example: *find Ahmad*.',
            'not_found' => 'No applicants found for the search **:query**.',
            'reply' => 'Found **:count** applicants for the search **:query**:',
            'reply_one' => 'Found **:count** applicant for the search **:query**:',
        ],

        'dashboard_stats' => [
            'description' => 'Get current applicant counts and eligibility statistics.',
            'label' => 'Dashboard statistics',
            'reply' => 'Here are the current applicant statistics:',
            'title' => 'Applicant Statistics',
            'total' => 'Total Applicants',
            'eligible' => 'Eligible',
            'not_eligible' => 'Not Eligible',
            'under_review' => 'Under Review',
        ],

        'explain_eligibility' => [
            'description' => 'Explain an applicant eligibility decision using their IC number or full name.',
            'ic_description' => '12-digit IC number, if known.',
            'name_description' => 'Applicant full name, if the IC is unknown.',
            'label' => 'Explain applicant eligibility',
            'missing_input' => 'An IC No. or full name is required to explain eligibility.',
            'needs_input_reply' => 'Please provide the applicant IC No. or full name. Example: *Is 011212083433 eligible?*',
            'reply' => 'Here is the eligibility explanation for **:name** (:ic):',
            'title' => 'Eligibility Decision',
            'income_below' => '✓ Income (RM :income) is lower than the Had Kifayah (RM :had_kifayah) — gap of RM :gap',
            'income_above' => '✗ Income (RM :income) exceeds the Had Kifayah (RM :had_kifayah)',
            'dependants_high' => '✓ High number of dependants (:count person)|✓ High number of dependants (:count people)',
            'dependants_medium' => '○ Moderate number of dependants (:count person)|○ Moderate number of dependants (:count people)',
            'dependants_none' => '○ Dependant count not recorded (using bilangan_tanggungan: :count)',
            'fixed_expenses' => '✓ Fixed expenses (utilities + transport + medical): RM :amount',
            'no_expenses' => '○ No rent / utilities / transport / medical recorded',
            'rental' => '○ Housing: rented home (additional rent rate)',
        ],

        'calculate_kifayah' => [
            'description' => 'Calculate Had Kifayah and eligibility from household income and composition. Use only values supplied by the user or found through another tool.',
            'label' => 'Calculate Had Kifayah',
            'applicant_income' => 'Applicant monthly income (RM).',
            'spouse_income' => 'Spouse monthly income (RM).',
            'children_income' => 'Children monthly income (RM).',
            'other_income' => 'Other monthly income (RM).',
            'utilities_description' => 'Monthly utility cost (RM).',
            'transport_description' => 'Monthly transport cost (RM).',
            'medical_description' => 'Monthly medical cost (RM).',
            'missing_input' => 'Please provide at least an income or household detail for the Had Kifayah calculation.',
            'reply' => "Had Al-Kifayah calculation (MAIDAM):\n\n**:formula**\n\nStatus: **:status** (AI confidence: :confidence%)",
        ],
    ],

    'kifayah' => [
        'formula' => 'Had Kifayah = RM :had_kifayah  (A − C = RM :income − RM :had_kifayah)',
        'head' => 'Head of Household',
        'spouse' => 'Spouse',
        'parents' => 'Dependant Parents',
        'adult_unemployed' => 'Unemployed Adults',
        'adult_working_dependant' => 'Working Adults Still Dependant',
        'higher_education' => 'Higher Education',
        'teenager' => 'Teenagers',
        'primary_school' => 'Primary School',
        'kindergarten' => 'Kindergarten',
        'rent_selected' => 'Rented Home (selected)',
        'rent_not_selected' => 'Rented Home (not selected)',
        'utilities' => 'Utilities (input)',
        'transport' => 'Transport (input)',
        'medical' => 'Medical (input)',
    ],

    'mock' => [
        'fallback' => "I can help you with:\n\n".
            "• Find an applicant profile (provide IC No. or name)\n".
            "• Calculate Had Kifayah (example: *calculate 1500 3 300 100*)\n".
            "• Explain applicant eligibility (example: *is 011212083433 eligible?*)\n".
            "• Show system statistics\n".
            '• Search applicant records by name',
        'prompts' => [
            'get_applicant' => 'Show the profile of applicant 011212083433',
            'search_applicants' => 'Find applicant records for Ahmad',
            'dashboard_stats' => 'How many asnaf applicants are there currently?',
            'calculate_kifayah' => 'Calculate Had Kifayah 1500 3 300 100',
            'explain_eligibility' => 'Is applicant 011212083433 eligible?',
            'default' => 'Please help me',
        ],
    ],

    'providers' => [
        'labels' => [
            'mock' => 'Demo mode',
            'groq' => 'Groq',
            'openai' => 'OpenAI',
            'gemini' => 'Gemini',
            'ollama' => 'Ollama',
        ],
        'groq' => [
            'not_configured' => 'Groq is not configured. Set AI_GROQ_API_KEY in .env and AI_PROVIDER=groq.',
            'unreachable' => 'The Groq AI service cannot be reached. Please try again shortly.',
            'error' => 'The Groq AI service is having problems. Please try again shortly.',
            'unresolved' => 'I could not complete this request. Please try a more specific question.',
        ],
        'openai' => [
            'not_configured' => 'The OpenAI provider is not configured. Set AI_OPENAI_API_KEY in .env and AI_PROVIDER=openai to enable real LLM responses.',
            'stub' => '[OpenAI stub] Would call ChatGPT here with the system prompt and 5 tool definitions. See TODO in OpenAiProvider.',
        ],
        'gemini' => [
            'not_configured' => 'The Gemini provider is not configured. Set AI_GEMINI_API_KEY in .env and AI_PROVIDER=gemini to enable.',
            'stub' => '[Gemini stub] Would call Gemini here with the system prompt and 5 function declarations. See TODO in GeminiProvider.',
        ],
        'ollama' => [
            'unreachable' => 'Ollama cannot be reached at :host. Make sure the ollama service is running and AI_OLLAMA_HOST is correct.',
            'unreachable_with_error' => 'Ollama cannot be reached at :host (:error).',
            'stub' => '[Ollama stub] Ollama is reachable. Would POST to :host/api/chat here with the 5 tool definitions. See TODO in OllamaProvider.',
        ],
    ],

    'assessment' => [
        'title' => 'AI Assessment',
        'subtitle' => 'Smart assessment & applicant eligibility confidence',
        'eyebrow' => 'AI Assessment',
        'open' => 'Open AI Assistant',
        'total' => 'Total Assessments',
        'avg_confidence' => 'Average Confidence',
        'list_title' => 'Assessment List',
        'profiles' => ':count latest profiles',
        'score' => 'AI Score',
        'not_generated' => 'Assessment has not been generated.',
        'generate' => 'Generate',
        'details' => 'Details',
        'empty_title' => 'No assessments yet',
        'empty_desc' => 'Generate an AI assessment from a profile page to get started.',
        'select_applicant' => 'Select Applicant',
    ],

    'errors' => [
        'invalid_question' => 'Please enter a valid question.',
        'server' => 'Sorry, an error occurred while processing the AI request. Please try again shortly.',
        'connection' => 'Sorry, the connection to the AI assistant was interrupted. Please try again.',
        'no_reply' => 'Sorry, I cannot generate a reply right now.',
        'tool_execution' => 'Sorry, the data tool could not be executed right now.',
    ],

    'portal' => [
        'tab_title' => 'Ustaz Danial — J-ZAKAT',
        'page_title' => 'AI Assistant',
        'title' => 'Ustaz Danial',
        'subtitle' => 'Smart assistant for the applicant portal',
        'empty_title' => 'Ustaz Danial is ready to help',
        'empty_body' => 'Ask about assistance, documents, applications or your application status.',
        'placeholder' => 'Type your question...',
        'send' => 'Send',
        'disclaimer' => 'Answers are guidance based on system data. The final decision is subject to the official MAIJ assessment.',
        'guardrail' => 'The final eligibility decision is subject to the official assessment of Majlis Agama Islam Johor (MAIJ).',
        'suggestions_reply' => "Hello, I am the J-ZAKAT virtual assistant. You can ask about:\n\n• Types of assistance available\n• Required documents\n• How to submit an application\n• Your application status\n• Updating household information\n• What to do if a document is rejected\n\nPlease choose a suggestion or type your question.",
        'suggestions' => [
            'types' => ['label' => 'Assistance types', 'prompt' => 'What assistance can I apply for?'],
            'documents_item' => ['label' => 'Required documents', 'prompt' => 'What documents are required?'],
            'submit' => ['label' => 'How to apply', 'prompt' => 'How do I submit an application?'],
            'status' => ['label' => 'Application status', 'prompt' => 'What is my application status?'],
            'household' => ['label' => 'Update household', 'prompt' => 'How do I update my household information?'],
            'rejected' => ['label' => 'Rejected document', 'prompt' => 'My document was rejected, what should I do?'],
            'assistance_types' => 'Assistance types',
            'prompt_assistance_types' => 'What assistance can I apply for?',
            'documents' => 'Required documents',
            'prompt_documents' => 'What documents are required?',
            'how_to_apply' => 'How to apply',
            'prompt_how_to_apply' => 'How do I submit an application?',
            'application_status' => 'Application status',
            'prompt_application_status' => 'What is my application status?',
        ],
        'assistance_reply' => "You can apply for the following types of assistance:\n\n:list\n\nEach application will be assessed according to MAIJ guidelines. :guardrail",
        'assistance_types' => [
            'Sara Hidup' => 'Living Expenses',
            'Perubatan' => 'Medical',
            'Dialisis' => 'Dialysis',
            'Sewa Rumah' => 'House Rent',
            'IPT' => 'Higher Education',
            'Sekolah' => 'School',
            'Berdikari' => 'Self-Reliance',
            'Peralatan' => 'Equipment',
            'Emergency' => 'Emergency',
        ],
        'documents_reply' => "Documents commonly required:\n\n• Copies of identity cards for the applicant and household members\n• Recent payslips or income statements\n• Utility bills or proof of monthly commitments\n• Supporting documents based on the assistance type (for example, medical reports for medical assistance)\n\nPlease upload clear and valid documents. :guardrail",
        'how_to_submit_reply' => "To submit an application:\n\n1. Click \"Apply for Assistance\" on the dashboard\n2. Select the assistance type and complete the form\n3. Upload supporting documents\n4. Save as a draft or submit for review\n\nYou can also scan a completed MAIJ Assistance Form through the \"Assistance Applications\" menu.",
        'no_applications' => 'You do not have any applications yet. Click "Apply for Assistance" to start your first application.',
        'status_intro' => 'Here is a summary of your applications:',
        'status_line' => '• :no — :status',
        'status_footer' => 'For full details, open the "My Applications" menu. :guardrail',
        'application_statuses' => [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'incomplete' => 'Incomplete',
        ],
        'update_household' => 'You can view household members through the "My Household" menu. To update household information or income, please contact the MAIJ Zakat Management Division or update it through a new application. Household data must be verified by MAIJ officers.',
        'rejected_document' => "If your document was rejected or requires review:\n\n1. Check the message on the document\n2. Re-upload a clearer or more complete document\n3. Ensure the format is PDF/JPG/PNG and the information is readable\n4. Contact MAIJ at 07-228 2928 if the problem persists",
    ],

    'system_prompt' => 'You are the J-ZAKAT Asnaf Assistant for Majlis Agama Islam Johor (MAIJ). Respond in English. Use the provided tools to look up applicants, explain eligibility decisions, calculate Had Kifayah, and summarize profiles. Never invent applicant data or calculation rates: always call the relevant tool first. If a tool returns no record, say so clearly. Be concise, professional, protect personal data, and cite the specific data returned by the tools.',
];
