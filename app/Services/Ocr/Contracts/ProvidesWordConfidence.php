<?php

namespace App\Services\Ocr\Contracts;

interface ProvidesWordConfidence
{
    /**
     * @return array<int, array{page:int, width:int, height:int, words:array<int, array{text:string, conf:float}>}>
     */
    public function wordPages(): array;

    /**
     * @return array<string, int>
     */
    public function timings(): array;
}
