<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Aset frontend dibangun lewat Vite. Pengujian tidak perlu (dan
        // tidak boleh bergantung pada) hasil `npm run build`; tanpa ini
        // setiap request yang memakai layout akan gagal saat manifest
        // belum ada di mesin pengujian.
        $this->withoutVite();
    }
}
