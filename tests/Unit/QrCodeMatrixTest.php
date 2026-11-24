<?php

namespace Tests\Unit;

use App\Support\QrCode;
use PHPUnit\Framework\TestCase;

/**
 * Regresi: QR e-tiket dulu tidak terbaca scanner Gatekeeper/Ranger karena separator
 * di sekitar finder pattern tidak pernah terpasang (isset() pada sel null).
 */
class QrCodeMatrixTest extends TestCase
{
    public function test_finder_separators_are_light_modules(): void
    {
        $m = QrCode::matrixV1M('SMK-202611-A8F2K9QX');
        $n = count($m);
        $this->assertSame(21, $n);

        for ($i = 0; $i < 8; $i++) {
            $this->assertSame(0, $m[7][$i], "top-left row sep col {$i}");
            $this->assertSame(0, $m[$i][7], "top-left col sep row {$i}");
            $this->assertSame(0, $m[7][$n - 8 + $i], 'top-right row sep');
            $this->assertSame(0, $m[$i][$n - 8], 'top-right col sep');
            $this->assertSame(0, $m[$n - 8][$i], 'bottom-left row sep');
            $this->assertSame(0, $m[$n - 8 + $i][7], 'bottom-left col sep');
        }
    }

    public function test_timing_patterns_and_dark_module(): void
    {
        $m = QrCode::matrixV1M('SMK-202611-001');

        for ($i = 8; $i <= 12; $i++) {
            $expected = $i % 2 === 0 ? 1 : 0;
            $this->assertSame($expected, $m[6][$i]);
            $this->assertSame($expected, $m[$i][6]);
        }
        $this->assertSame(1, $m[13][8]);
    }

    public function test_format_information_for_level_m_mask_0(): void
    {
        $m = QrCode::matrixV1M('SMK-202611-001');
        $coords = [[8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5], [8, 7], [8, 8], [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8]];
        $bits = implode('', array_map(fn ($c) => $m[$c[0]][$c[1]], $coords));

        $this->assertSame('101010000010010', $bits);
    }

    public function test_rejects_text_longer_than_version_1_capacity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        QrCode::matrixV1M(str_repeat('A', 21));
    }
}
