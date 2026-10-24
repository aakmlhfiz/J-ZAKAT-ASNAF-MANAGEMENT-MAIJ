<?php

namespace App\Services;

use App\Models\AssistanceHistory;
use App\Models\PortalUser;

/**
 * User-scoped assistant for the applicant portal.
 *
 * Deliberately separate from the admin AiService: it never exposes other
 * applicants' data and never invents eligibility decisions. All answers are
 * deterministic, offline-safe, and always include the MAIJ final-decision
 * guardrail for eligibility-related questions.
 */
class PortalAiService
{
    public const GUARDRAIL = 'Keputusan kelayakan muktamad adalah tertakluk kepada penilaian rasmi Majlis Agama Islam Johor (MAIJ).';

    public function chat(PortalUser $user, string $message): array
    {
        $message = mb_strtolower(trim($message));

        if (preg_match('/(bantuan|jenis bantuan|apa bantuan|bantuan apa)/', $message) && preg_match('/(mohon|layak|boleh)/', $message)) {
            return $this->text($this->assistanceTypes());
        }

        if (preg_match('/(dokumen|apa dokumen|dokumen apa)/', $message)) {
            return $this->text($this->requiredDocuments());
        }

        if (preg_match('/(hantar|serah|submit|kemukakan).*(permohonan|borang)/', $message) || preg_match('/cara mohon|bagaimana mohon/', $message)) {
            return $this->text($this->howToSubmit());
        }

        if (preg_match('/(status|semakan).*(permohonan)/', $message) || preg_match('/status permohonan/', $message)) {
            return $this->text($this->applicationStatus($user));
        }

        if (preg_match('/(isi rumah|kemas kini|update).*(maklumat|isi rumah|rumah)/', $message)) {
            return $this->text($this->updateHousehold());
        }

        if (preg_match('/(dokumen.*(ditolak|reject)|ditolak|tidak lulus).*(dokumen)/', $message) || preg_match('/dokumen ditolak/', $message)) {
            return $this->text($this->rejectedDocument());
        }

        return $this->suggestions();
    }

    private function text(string $reply): array
    {
        return ['reply' => $reply, 'data' => ['type' => 'text']];
    }

    private function suggestions(): array
    {
        $items = [
            ['prompt' => 'Apakah bantuan yang boleh saya mohon?', 'label' => 'Jenis bantuan'],
            ['prompt' => 'Apakah dokumen yang diperlukan?', 'label' => 'Dokumen diperlukan'],
            ['prompt' => 'Bagaimana cara menghantar permohonan?', 'label' => 'Cara memohon'],
            ['prompt' => 'Apakah status permohonan saya?', 'label' => 'Status permohonan'],
            ['prompt' => 'Bagaimana kemas kini maklumat isi rumah?', 'label' => 'Kemas kini isi rumah'],
            ['prompt' => 'Dokumen saya ditolak, apa perlu saya buat?', 'label' => 'Dokumen ditolak'],
        ];

        $suggestions = array_map(fn ($i, $idx) => [
            'id' => 's'.$idx,
            'icon' => 'fa-solid fa-circle-question',
            'label' => $i['label'],
            'prompt' => $i['prompt'],
        ], $items, array_keys($items));

        return [
            'reply' => "Salam, saya pembantu maya J-ZAKAT AI. Anda boleh bertanya tentang:\n\n• Jenis bantuan yang boleh dipohon\n• Dokumen yang diperlukan\n• Cara menghantar permohonan\n• Status permohonan anda\n• Kemas kini maklumat isi rumah\n• Tindakan jika dokumen ditolak\n\nSila pilih atau taip soalan anda.",
            'data' => ['type' => 'suggestions', 'suggestions' => $suggestions],
        ];
    }

    private function assistanceTypes(): string
    {
        $list = implode("\n", array_map(fn ($v) => "• {$v}", array_values(AssistanceHistory::jenisBantuanOptions())));

        return "Anda boleh memohon jenis bantuan berikut:\n\n{$list}\n\nSetiap permohonan akan dinilai mengikut garis panduan MAIJ. ".self::GUARDRAIL;
    }

    private function requiredDocuments(): string
    {
        return "Dokumen yang lazimnya diperlukan:\n\n• Salinan Kad Pengenalan pemohon dan ahli isi rumah\n• Slip gaji atau penyata pendapatan terkini\n• Bil utiliti atau bukti komitmen bulanan\n• Dokumen sokongan mengikut jenis bantuan (contoh: laporan perubatan untuk bantuan perubatan)\n\nSila muat naik dokumen yang jelas dan sah. ".self::GUARDRAIL;
    }

    private function howToSubmit(): string
    {
        return "Untuk menghantar permohonan:\n\n1. Klik \"Mohon Bantuan\" pada dashboard\n2. Pilih jenis bantuan dan lengkapkan borang\n3. Muat naik dokumen sokongan\n4. Simpan sebagai draf atau hantar terus untuk semakan\n\nAnda juga boleh mengimbas Borang Bantuan MAIJ yang telah diisi melalui menu \"Permohonan Bantuan\".";
    }

    private function applicationStatus(PortalUser $user): string
    {
        $apps = $user->applications()->orderByDesc('id')->get();
        if ($apps->isEmpty()) {
            return 'Anda belum mempunyai sebarang permohonan. Klik "Mohon Bantuan" untuk memulakan permohonan pertama anda.';
        }

        $lines = ['Berikut ringkasan permohonan anda:'];
        foreach ($apps->take(5) as $app) {
            $status = match ($app->status) {
                'draft' => 'Draf',
                'submitted' => 'Dihantar',
                'under_review' => 'Dalam Semakan',
                'approved' => 'Diluluskan',
                'rejected' => 'Ditolak',
                'incomplete' => 'Tidak Lengkap',
                default => ucfirst((string) $app->status),
            };
            $lines[] = "• {$app->application_no} — {$status}";
        }

        $lines[] = '';
        $lines[] = 'Untuk butiran penuh, buka menu "Permohonan Saya". '.self::GUARDRAIL;

        return implode("\n", $lines);
    }

    private function updateHousehold(): string
    {
        return 'Anda boleh melihat ahli isi rumah melalui menu "Isi Rumah Saya". Untuk mengemas kini maklumat isi rumah atau pendapatan, sila hubungi Bahagian Pengurusan Zakat MAIJ atau kemas kini melalui permohonan baharu. Data isi rumah perlu disahkan oleh pegawai MAIJ.';
    }

    private function rejectedDocument(): string
    {
        return "Jika dokumen anda ditolak atau memerlukan semakan:\n\n1. Semak mesej pada dokumen tersebut\n2. Muat naik semula dokumen yang lebih jelas atau lengkap\n3. Pastikan format PDF/JPG/PNG dan maklumat boleh dibaca\n4. Hubungi MAIJ di talian 07-228 2928 jika masalah berterusan";
    }
}
