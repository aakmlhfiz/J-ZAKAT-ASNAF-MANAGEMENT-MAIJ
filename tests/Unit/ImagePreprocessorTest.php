<?php

namespace Tests\Unit;

use App\Services\Ocr\ImagePreprocessor;
use ReflectionProperty;
use Tests\TestCase;

class ImagePreprocessorTest extends TestCase
{
    public function test_returns_original_path_when_disabled(): void
    {
        config(['ocr.preprocess.enabled' => false]);
        $path = tempnam(sys_get_temp_dir(), 'ocr_').'.png';
        file_put_contents($path, "\x89PNG\r\n\x1a\n".str_repeat("\x00", 32));

        $preprocessor = new ImagePreprocessor;

        $this->assertSame($path, $preprocessor->process($path));

        @unlink($path);
    }

    public function test_returns_original_path_when_imagick_unavailable(): void
    {
        if (ImagePreprocessor::available()) {
            $this->markTestSkipped('Imagick is available — passthrough path not applicable.');
        }

        config(['ocr.preprocess.enabled' => true]);
        $path = tempnam(sys_get_temp_dir(), 'ocr_').'.png';
        file_put_contents($path, "\x89PNG\r\n\x1a\n".str_repeat("\x00", 32));

        $preprocessor = new ImagePreprocessor;

        $this->assertSame($path, $preprocessor->process($path));

        @unlink($path);
    }

    public function test_cleanup_removes_only_temp_files(): void
    {
        $preprocessor = new ImagePreprocessor;

        $original = tempnam(sys_get_temp_dir(), 'ocr_orig_').'.png';
        $tmp = tempnam(sys_get_temp_dir(), 'ocr_tmp_').'.png';
        file_put_contents($original, 'x');
        file_put_contents($tmp, 'y');

        $preprocessor->cleanup($tmp, $original);

        $this->assertFalse(file_exists($tmp));
        $this->assertTrue(file_exists($original));

        @unlink($original);
    }

    public function test_config_values_are_respected(): void
    {
        config(['ocr.preprocess.enabled' => true]);
        config(['ocr.preprocess.min_size' => 2000]);

        $preprocessor = new ImagePreprocessor;
        $reflection = new ReflectionProperty(ImagePreprocessor::class, 'minSize');

        $this->assertSame(2000, $reflection->getValue($preprocessor));
    }
}
