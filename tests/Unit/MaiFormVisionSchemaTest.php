<?php

namespace Tests\Unit;

use App\Services\Ocr\DocumentIntelligence\Vision\MaiFormVisionSchema;
use PHPUnit\Framework\TestCase;

class MaiFormVisionSchemaTest extends TestCase
{
    public function test_schema_covers_all_required_sections(): void
    {
        $sections = array_keys(MaiFormVisionSchema::sections());

        foreach ([
            'applicant', 'applications', 'sara_hidup', 'medical', 'business', 'housing', 'debt',
            'dependents', 'residence', 'assets', 'employment', 'income', 'expenses', 'declaration', 'verification',
        ] as $section) {
            if ($section === 'applications' || $section === 'dependents') {
                continue; // handled as arrays in the prompt
            }
            $this->assertContains($section, $sections, "Missing section: {$section}");
        }
    }

    public function test_prompt_contains_no_guess_and_label_value_rules(): void
    {
        $prompt = (new MaiFormVisionSchema)->build();

        $this->assertStringContainsString('Never invent a value that is not visible', $prompt['system']);
        $this->assertStringContainsString('Never guess names, ICs, phone numbers or amounts', $prompt['user']);
        $this->assertStringContainsString('FIELD LABEL', $prompt['user']);
        $this->assertStringContainsString('needs_review', $prompt['user']);
        $this->assertStringContainsString('bbox', $prompt['user']);
        $this->assertStringContainsString('signature', $prompt['user']);
    }

    public function test_checkbox_groups_are_declared(): void
    {
        $groups = MaiFormVisionSchema::checkboxGroups();

        $this->assertArrayHasKey('kesihatan', $groups);
        $this->assertArrayHasKey('status_perkahwinan', $groups);
        $this->assertArrayHasKey('merokok', $groups);
        $this->assertContains('sihat', $groups['kesihatan']);
    }

    public function test_field_types_cover_identity_and_money(): void
    {
        $types = MaiFormVisionSchema::fieldTypes();

        $this->assertSame('ic', $types['ic']);
        $this->assertSame('phone', $types['no_telefon_bimbit']);
        $this->assertSame('date', $types['pengakuan_tarikh']);
        $this->assertSame('amount', $types['jumlah_sara_hidup']);
    }
}
