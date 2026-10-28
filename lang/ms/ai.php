<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Assistant (Bahasa Melayu)
    |--------------------------------------------------------------------------
    */

    'page' => [
        'title' => 'Pembantu AI',
        'tab_title' => 'Ustaz Danial — J-ZAKAT',
        'subtitle' => 'Pembantu pintar data asnaf MAIJ',
        'role' => 'Pembantu pintar data asnaf MAIJ',
        'clear_history' => 'Kosongkan sejarah',
        'empty_title' => 'Ustaz Danial sedia membantu',
        'empty_body' => 'Cuba tanya saya tentang pemohon, statistik, atau Had Kifayah.',
        'disclaimer' => 'Ustaz Danial memberi analisis berdasarkan data sistem. Keputusan muktamad tertakluk kepada penilaian rasmi MAIJ.',
    ],

    'widget' => [
        'open' => 'Buka Ustaz Danial',
        'close' => 'Tutup',
        'online' => 'dalam talian',
        'greeting' => 'Hai! Saya Ustaz Danial',
        'greeting_body' => 'Tanya saya tentang pemohon, statistik atau Had Kifayah.',
        'placeholder' => 'Tanya apa-apa...',
        'send' => 'Hantar',
    ],

    'input' => [
        'placeholder' => 'Tanya Ustaz Danial apa-apa...',
        'send' => 'Hantar mesej',
    ],

    'suggestions' => [
        'heading' => 'Cuba tanya saya',
        'eligibility_stats' => 'Statistik kelayakan',
        'generate_assessment' => 'Jana penilaian AI',
        'search_applicant' => 'Cari pemohon',
        'kifayah_rate' => 'Kadar Had Kifayah',
        'prompt_eligibility_stats' => 'Berapa ramai pemohon yang layak bulan ini?',
        'prompt_generate_assessment' => 'Jana penilaian untuk pemohon terkini',
        'prompt_search_applicant' => 'Cari pemohon bernama Ahmad',
        'prompt_kifayah_rate' => 'Apakah kadar Had Kifayah semasa?',
    ],

    'portal' => [
        'tab_title' => 'Pembantu AI — Portal Pemohon',
        'page_title' => 'Pembantu AI',
        'title' => 'Pembantu Maya',
        'subtitle' => 'Panduan permohonan bantuan zakat MAIJ',
        'empty_title' => 'Pembantu maya sedia membantu',
        'empty_body' => 'Tanya saya tentang jenis bantuan, dokumen, atau status permohonan anda.',
        'placeholder' => 'Tanya pembantu maya...',
        'send' => 'Hantar mesej',
        'disclaimer' => 'Pembantu maya memberi panduan am sahaja. Keputusan kelayakan muktamad adalah tertakluk kepada penilaian rasmi MAIJ.',
        'suggestions' => [
            'assistance_types' => 'Jenis bantuan',
            'documents' => 'Dokumen diperlukan',
            'how_to_apply' => 'Cara memohon',
            'application_status' => 'Status permohonan',
            'prompt_assistance_types' => 'Apakah bantuan yang boleh saya mohon?',
            'prompt_documents' => 'Apakah dokumen yang diperlukan?',
            'prompt_how_to_apply' => 'Bagaimana cara menghantar permohonan?',
            'prompt_application_status' => 'Apakah status permohonan saya?',
        ],
    ],

    'card' => [
        'stats' => 'Statistik',
        'profile' => 'Profil Pemohon',
        'explanation' => 'Penjelasan',
        'result' => 'Keputusan',
        'eligibility_result' => 'Keputusan Kelayakan',
        'search_results' => 'Hasil Carian (:count)',
        'ai_score' => 'Skor AI',
        'suggestions' => 'Cuba tanya saya',
    ],

    'fields' => [
        'name' => 'Nama',
        'ic' => 'No. IC',
        'income' => 'Pendapatan',
        'dependants' => 'Tanggungan',
        'rent' => 'Sewa',
        'utilities' => 'Utiliti',
        'had_kifayah' => 'Had Kifayah',
        'status' => 'Status',
    ],

    'values' => [
        'people' => ':count orang|:count orang',
    ],

    'errors' => [
        'connection' => 'Maaf, berlaku ralat sambungan. Sila cuba lagi.',
        'no_reply' => 'Maaf, saya tidak dapat menjana jawapan buat masa ini.',
        'unknown_tool' => 'Tool tidak dikenali: :name',
        'tool_execution' => 'Maaf, alat data tidak dapat dilaksanakan buat masa ini.',
    ],

    'tools' => [
        'get_applicant' => [
            'description' => 'Dapatkan profil penuh pemohon asnaf melalui nombor kad pengenalan Malaysia 12 digit.',
            'ic_description' => 'Nombor kad pengenalan 12 digit tanpa sengkang.',
            'label' => 'Cari pemohon mengikut No. IC',
            'missing_ic' => 'No. IC diperlukan untuk mendapatkan profil pemohon.',
            'not_found' => 'Tiada pemohon dengan No. IC **:ic** dijumpai dalam pangkalan data.',
            'reply' => 'Berikut adalah profil pemohon **:name**:',
            'reason_eligible' => 'Pendapatan berada di bawah Had Kifayah. Layak dipertimbangkan untuk bantuan.',
            'reason_not_eligible' => 'Pendapatan melebihi Had Kifayah. Tidak layak pada masa ini.',
            'reason_incomplete' => 'Data kewangan belum lengkap.',
        ],

        'search_applicants' => [
            'description' => 'Cari pemohon asnaf mengikut nama penuh atau sebahagian nama, atau nombor kad pengenalan.',
            'query_description' => 'Nama pemohon atau nombor kad pengenalan untuk dicari.',
            'label' => 'Cari rekod pemohon',
            'missing_query' => 'Nama atau No. IC diperlukan untuk carian pemohon.',
            'empty_query' => 'Sila berikan nama atau sebahagian nama untuk dicari. Contoh: *cari Ahmad*.',
            'not_found' => 'Tiada pemohon dijumpai untuk carian **:query**.',
            'reply' => 'Ditemui **:count** pemohon untuk carian **:query**:',
            'reply_one' => 'Ditemui **:count** pemohon untuk carian **:query**:',
        ],

        'dashboard_stats' => [
            'description' => 'Dapatkan bilangan pemohon semasa dan statistik kelayakan.',
            'label' => 'Statistik dashboard',
            'reply' => 'Berikut adalah statistik pemohon semasa:',
            'title' => 'Statistik Pemohon',
            'total' => 'Jumlah Pemohon',
            'eligible' => 'Layak',
            'not_eligible' => 'Tidak Layak',
            'under_review' => 'Dalam Semakan',
        ],

        'explain_eligibility' => [
            'description' => 'Terangkan keputusan kelayakan pemohon menggunakan nombor kad pengenalan atau nama penuh.',
            'ic_description' => 'Nombor kad pengenalan 12 digit, jika diketahui.',
            'name_description' => 'Nama penuh pemohon, jika nombor kad pengenalan tidak diketahui.',
            'label' => 'Terangkan kelayakan pemohon',
            'missing_input' => 'No. IC atau nama penuh diperlukan untuk menjelaskan kelayakan.',
            'needs_input_reply' => 'Sila berikan No. IC atau nama penuh pemohon. Contoh: *Adakah 011212083433 layak?*',
            'reply' => 'Berikut adalah penjelasan kelayakan untuk **:name** (:ic):',
            'title' => 'Keputusan Kelayakan',
            'income_below' => '✓ Pendapatan (RM :income) lebih rendah daripada Had Kifayah (RM :had_kifayah) — jurang RM :gap',
            'income_above' => '✗ Pendapatan (RM :income) melebihi Had Kifayah (RM :had_kifayah)',
            'dependants_high' => '✓ Bilangan tanggungan tinggi (:count orang)|✓ Bilangan tanggungan tinggi (:count orang)',
            'dependants_medium' => '○ Bilangan tanggungan sederhana (:count orang)|○ Bilangan tanggungan sederhana (:count orang)',
            'dependants_none' => '○ Bilangan tanggungan tidak direkodkan (guna bilangan_tanggungan: :count)',
            'fixed_expenses' => '✓ Perbelanjaan tetap (utiliti + pengangkutan + perubatan): RM :amount',
            'no_expenses' => '○ Tiada sewa / utiliti / pengangkutan / perubatan direkodkan',
            'rental' => '○ Tempat tinggal: rumah sewa (tambahan kadar sewa)',
        ],

        'calculate_kifayah' => [
            'description' => 'Kira Had Kifayah dan kelayakan daripada pendapatan dan komposisi isi rumah. Gunakan hanya nilai yang diberikan oleh pengguna atau ditemui melalui alat lain.',
            'label' => 'Kira Had Kifayah',
            'applicant_income' => 'Pendapatan bulanan pemohon (RM).',
            'spouse_income' => 'Pendapatan bulanan pasangan (RM).',
            'children_income' => 'Pendapatan bulanan anak (RM).',
            'other_income' => 'Pendapatan bulanan lain (RM).',
            'utilities_description' => 'Kos utiliti bulanan (RM).',
            'transport_description' => 'Kos pengangkutan bulanan (RM).',
            'medical_description' => 'Kos perubatan bulanan (RM).',
            'missing_input' => 'Sila berikan sekurang-kurangnya pendapatan atau maklumat isi rumah untuk pengiraan Had Kifayah.',
            'reply' => "Pengiraan Had Al-Kifayah (MAIDAM):\n\n**:formula**\n\nStatus: **:status** (keyakinan AI: :confidence%)",
        ],
    ],

    'kifayah' => [
        'formula' => 'Had Kifayah = RM :had_kifayah  (A − C = RM :income − RM :had_kifayah)',
        'head' => 'Ketua Keluarga',
        'spouse' => 'Isteri / Suami',
        'parents' => 'Ibu Bapa Tanggungan',
        'adult_unemployed' => 'Dewasa Tidak Bekerja',
        'adult_working_dependant' => 'Dewasa Bekerja Ditanggung',
        'higher_education' => 'IPT',
        'teenager' => 'Remaja',
        'primary_school' => 'Sekolah Rendah',
        'kindergarten' => 'Tadika',
        'rent_selected' => 'Rumah Sewa (dipilih)',
        'rent_not_selected' => 'Rumah Sewa (tidak dipilih)',
        'utilities' => 'Utiliti (input)',
        'transport' => 'Pengangkutan (input)',
        'medical' => 'Perubatan (input)',
    ],

    'mock' => [
        'fallback' => "Saya boleh membantu anda dengan:\n\n".
            "• Cari profil pemohon (beri No. IC atau nama)\n".
            "• Kira Had Kifayah (contoh: *kira 1500 3 300 100*)\n".
            "• Jelaskan kelayakan pemohon (contoh: *adakah 011212083433 layak?*)\n".
            "• Papar statistik sistem\n".
            '• Cari rekod pemohon mengikut nama',
        'prompts' => [
            'get_applicant' => 'Berikan profil pemohon 011212083433',
            'search_applicants' => 'Cari rekod pemohon bernama Ahmad',
            'dashboard_stats' => 'Berapa jumlah asnaf semasa?',
            'calculate_kifayah' => 'Kira Had Kifayah 1500 3 300 100',
            'explain_eligibility' => 'Adakah pemohon 011212083433 layak?',
            'default' => 'Tolong bantu saya',
        ],
    ],

    'providers' => [
        'labels' => [
            'mock' => 'Mod demo',
            'groq' => 'Groq',
            'openai' => 'OpenAI',
            'gemini' => 'Gemini',
            'ollama' => 'Ollama',
        ],
        'groq' => [
            'not_configured' => 'Groq belum dikonfigurasi. Tetapkan AI_GROQ_API_KEY dalam .env dan AI_PROVIDER=groq.',
            'unreachable' => 'Perkhidmatan AI Groq tidak dapat dihubungi. Sila cuba lagi sebentar.',
            'error' => 'Perkhidmatan AI Groq sedang menghadapi masalah. Sila cuba lagi sebentar.',
            'unresolved' => 'Saya tidak dapat menyelesaikan permintaan ini. Sila cuba soalan yang lebih spesifik.',
        ],
        'openai' => [
            'not_configured' => 'Penyedia OpenAI belum dikonfigurasi. Tetapkan AI_OPENAI_API_KEY dalam .env dan AI_PROVIDER=openai untuk mendayakan respons LLM sebenar.',
            'stub' => '[Stub OpenAI] Akan memanggil ChatGPT di sini dengan system prompt dan 5 definisi alat. Lihat TODO dalam OpenAiProvider.',
        ],
        'gemini' => [
            'not_configured' => 'Penyedia Gemini belum dikonfigurasi. Tetapkan AI_GEMINI_API_KEY dalam .env dan AI_PROVIDER=gemini untuk mendayakan.',
            'stub' => '[Stub Gemini] Akan memanggil Gemini di sini dengan system prompt dan 5 deklarasi fungsi. Lihat TODO dalam GeminiProvider.',
        ],
        'ollama' => [
            'unreachable' => 'Ollama tidak dapat dihubungi di :host. Pastikan servis ollama berjalan dan AI_OLLAMA_HOST betul.',
            'unreachable_with_error' => 'Ollama tidak dapat dihubungi di :host (:error).',
            'stub' => '[Stub Ollama] Ollama boleh dihubungi. Akan POST ke :host/api/chat di sini dengan 5 definisi alat. Lihat TODO dalam OllamaProvider.',
        ],
    ],

    'assessment' => [
        'title' => 'AI Assessment',
        'subtitle' => 'Penilaian pintar & keyakinan kelayakan pemohon',
        'eyebrow' => 'Penilaian AI',
        'open' => 'Buka AI Assistant',
        'total' => 'Jumlah Penilaian',
        'avg_confidence' => 'Purata Keyakinan',
        'list_title' => 'Senarai Penilaian',
        'profiles' => ':count profil terkini',
        'score' => 'Skor AI',
        'not_generated' => 'Penilaian belum dijana.',
        'generate' => 'Jana',
        'details' => 'Butiran',
        'empty_title' => 'Belum ada penilaian',
        'empty_desc' => 'Jana penilaian AI dari halaman profil untuk mula.',
        'select_applicant' => 'Pilih Pemohon',
    ],

    'errors' => [
        'invalid_question' => 'Sila taip soalan yang sah.',
        'server' => 'Maaf, berlaku ralat semasa memproses permintaan AI. Sila cuba lagi sebentar.',
        'connection' => 'Maaf, sambungan ke pembantu AI terputus. Sila cuba lagi.',
        'no_reply' => 'Maaf, saya tidak dapat menjana jawapan buat masa ini.',
        'tool_execution' => 'Maaf, alat data tidak dapat dilaksanakan buat masa ini.',
    ],

    'portal' => [
        'tab_title' => 'Ustaz Danial — J-ZAKAT',
        'page_title' => 'AI Assistant',
        'title' => 'Ustaz Danial',
        'subtitle' => 'Pembantu pintar portal pemohon',
        'empty_title' => 'Ustaz Danial sedia membantu',
        'empty_body' => 'Tanya tentang bantuan, dokumen, permohonan atau status permohonan anda.',
        'placeholder' => 'Taip soalan anda...',
        'send' => 'Hantar',
        'disclaimer' => 'Jawapan adalah panduan berdasarkan data sistem. Keputusan muktamad tertakluk kepada penilaian rasmi MAIJ.',
        'guardrail' => 'Keputusan kelayakan muktamad adalah tertakluk kepada penilaian rasmi Majlis Agama Islam Johor (MAIJ).',
        'suggestions_reply' => "Salam, saya pembantu maya J-ZAKAT. Anda boleh bertanya tentang:\n\n• Jenis bantuan yang boleh dipohon\n• Dokumen yang diperlukan\n• Cara menghantar permohonan\n• Status permohonan anda\n• Kemas kini maklumat isi rumah\n• Tindakan jika dokumen ditolak\n\nSila pilih atau taip soalan anda.",
        'suggestions' => [
            'types' => ['label' => 'Jenis bantuan', 'prompt' => 'Apakah bantuan yang boleh saya mohon?'],
            'documents_item' => ['label' => 'Dokumen diperlukan', 'prompt' => 'Apakah dokumen yang diperlukan?'],
            'submit' => ['label' => 'Cara memohon', 'prompt' => 'Bagaimana cara menghantar permohonan?'],
            'status' => ['label' => 'Status permohonan', 'prompt' => 'Apakah status permohonan saya?'],
            'household' => ['label' => 'Kemas kini isi rumah', 'prompt' => 'Bagaimana kemas kini maklumat isi rumah?'],
            'rejected' => ['label' => 'Dokumen ditolak', 'prompt' => 'Dokumen saya ditolak, apa perlu saya buat?'],
            'assistance_types' => 'Jenis bantuan',
            'prompt_assistance_types' => 'Apakah bantuan yang boleh saya mohon?',
            'documents' => 'Dokumen diperlukan',
            'prompt_documents' => 'Apakah dokumen yang diperlukan?',
            'how_to_apply' => 'Cara memohon',
            'prompt_how_to_apply' => 'Bagaimana cara menghantar permohonan?',
            'application_status' => 'Status permohonan',
            'prompt_application_status' => 'Apakah status permohonan saya?',
        ],
        'assistance_reply' => "Anda boleh memohon jenis bantuan berikut:\n\n:list\n\nSetiap permohonan akan dinilai mengikut garis panduan MAIJ. :guardrail",
        'assistance_types' => [
            'Sara Hidup' => 'Sara Hidup',
            'Perubatan' => 'Perubatan',
            'Dialisis' => 'Dialisis',
            'Sewa Rumah' => 'Sewa Rumah',
            'IPT' => 'IPT',
            'Sekolah' => 'Sekolah',
            'Berdikari' => 'Berdikari',
            'Peralatan' => 'Peralatan',
            'Emergency' => 'Kecemasan',
        ],
        'documents_reply' => "Dokumen yang lazimnya diperlukan:\n\n• Salinan Kad Pengenalan pemohon dan ahli isi rumah\n• Slip gaji atau penyata pendapatan terkini\n• Bil utiliti atau bukti komitmen bulanan\n• Dokumen sokongan mengikut jenis bantuan (contoh: laporan perubatan untuk bantuan perubatan)\n\nSila muat naik dokumen yang jelas dan sah. :guardrail",
        'how_to_submit_reply' => "Untuk menghantar permohonan:\n\n1. Klik \"Mohon Bantuan\" pada dashboard\n2. Pilih jenis bantuan dan lengkapkan borang\n3. Muat naik dokumen sokongan\n4. Simpan sebagai draf atau hantar terus untuk semakan\n\nAnda juga boleh mengimbas Borang Bantuan MAIJ yang telah diisi melalui menu \"Permohonan Bantuan\".",
        'no_applications' => 'Anda belum mempunyai sebarang permohonan. Klik "Mohon Bantuan" untuk memulakan permohonan pertama anda.',
        'status_intro' => 'Berikut ringkasan permohonan anda:',
        'status_line' => '• :no — :status',
        'status_footer' => 'Untuk butiran penuh, buka menu "Permohonan Saya". :guardrail',
        'application_statuses' => [
            'draft' => 'Draf',
            'submitted' => 'Dihantar',
            'under_review' => 'Dalam Semakan',
            'approved' => 'Diluluskan',
            'rejected' => 'Ditolak',
            'incomplete' => 'Tidak Lengkap',
        ],
        'update_household' => 'Anda boleh melihat ahli isi rumah melalui menu "Isi Rumah Saya". Untuk mengemas kini maklumat isi rumah atau pendapatan, sila hubungi Bahagian Pengurusan Zakat MAIJ atau kemas kini melalui permohonan baharu. Data isi rumah perlu disahkan oleh pegawai MAIJ.',
        'rejected_document' => "Jika dokumen anda ditolak atau memerlukan semakan:\n\n1. Semak mesej pada dokumen tersebut\n2. Muat naik semula dokumen yang lebih jelas atau lengkap\n3. Pastikan format PDF/JPG/PNG dan maklumat boleh dibaca\n4. Hubungi MAIJ di talian 07-228 2928 jika masalah berterusan",
    ],

    'system_prompt' => 'Anda ialah Pembantu Asnaf J-ZAKAT untuk Majlis Agama Islam Johor (MAIJ). Jawab dalam Bahasa Melayu. Gunakan alat yang disediakan untuk mencari pemohon, menjelaskan keputusan kelayakan, mengira Had Kifayah dan meringkaskan profil. Jangan reka data pemohon atau kadar pengiraan: sentiasa panggil alat berkaitan terlebih dahulu. Jika alat tidak menemui rekod, nyatakan dengan jelas. Jawapan hendaklah ringkas, profesional, melindungi data peribadi, dan memetik data khusus yang dikembalikan oleh alat.',
];
