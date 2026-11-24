<?php

namespace App\Support;

class QrCode
{
    // Galois Field GF(256) tables for QR Reed-Solomon
    protected static ?array $exp = null;
    protected static ?array $log = null;

    protected static function initGf(): void
    {
        if (static::$exp !== null) {
            return;
        }
        static::$exp = array_fill(0, 512, 0);
        static::$log = array_fill(0, 256, 0);

        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            static::$exp[$i] = $x;
            static::$log[$x] = $i;
            $x <<= 1;
            if ($x >= 256) {
                $x ^= 0x11D; // Primitive polynomial x^8 + x^4 + x^3 + x^2 + 1
            }
        }
        for ($i = 255; $i < 512; $i++) {
            static::$exp[$i] = static::$exp[$i - 255];
        }
    }

    protected static function gfMul(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }
        return static::$exp[static::$log[$x] + static::$log[$y]];
    }

    /**
     * Compute Reed-Solomon error correction codewords for QR.
     */
    protected static function calculateEcc(array $data, int $eccCount): array
    {
        static::initGf();

        // Build generator polynomial
        $gen = [1];
        for ($i = 0; $i < $eccCount; $i++) {
            $next = [0];
            $factor = static::$exp[$i];
            foreach ($gen as $coef) {
                $next[] = static::gfMul($coef, $factor);
            }
            for ($j = 0; $j < count($gen); $j++) {
                $next[$j] ^= $gen[$j];
            }
            $gen = $next;
        }

        // Polynomial division
        $poly = array_merge($data, array_fill(0, $eccCount, 0));
        $dataLen = count($data);

        for ($i = 0; $i < $dataLen; $i++) {
            $lead = $poly[$i];
            if ($lead !== 0) {
                for ($j = 0; $j < count($gen); $j++) {
                    $poly[$i + $j] ^= static::gfMul($gen[$j], $lead);
                }
            }
        }

        return array_slice($poly, $dataLen);
    }

    /**
     * Encode alphanumeric text into bits for QR (0-9, A-Z, space, $, %, *, +, -, ., /, :).
     */
    protected static function encodeAlphanumeric(string $text, int $dataCap): array
    {
        $text = strtoupper(trim($text));
        $charset = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ $%*+-./:';
        $charVals = [];
        for ($i = 0; $i < strlen($text); $i++) {
            $pos = strpos($charset, $text[$i]);
            $charVals[] = ($pos !== false) ? $pos : 36; // fallback to space
        }

        // Mode 0010 (Alphanumeric) + 9-bit character count indicator
        $bits = '0010' . sprintf('%09b', count($charVals));

        for ($i = 0; $i < count($charVals); $i += 2) {
            if ($i + 1 < count($charVals)) {
                $val = $charVals[$i] * 45 + $charVals[$i + 1];
                $bits .= sprintf('%011b', $val);
            } else {
                $bits .= sprintf('%06b', $charVals[$i]);
            }
        }

        // Terminator up to 4 zero bits
        $capBits = $dataCap * 8;
        $rem = $capBits - strlen($bits);
        if ($rem > 0) {
            $bits .= str_repeat('0', min(4, $rem));
        }

        // Pad to byte boundary
        while (strlen($bits) % 8 !== 0) {
            $bits .= '0';
        }

        // Convert bits to bytes
        $bytes = [];
        for ($i = 0; $i < strlen($bits); $i += 8) {
            $bytes[] = bindec(substr($bits, $i, 8));
        }

        // Pad with alternating 0xEC and 0x11
        $pad = [0xEC, 0x11];
        $pIdx = 0;
        while (count($bytes) < $dataCap) {
            $bytes[] = $pad[$pIdx % 2];
            $pIdx++;
        }

        return $bytes;
    }

    /**
     * Generate QR Code matrix (Version 1-M: 21x21 modules).
     * Capacity: 16 data bytes, 10 EC bytes.
     * Alphanumeric capacity: up to 25 characters (perfect for 'SMK-202611-XMCX').
     *
     * @return array 21x21 array of 1 (black) and 0 (white)
     */
    public static function matrixV1M(string $text): array
    {
        $size = 21;
        $grid = array_fill(0, $size, array_fill(0, $size, null));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        // 1. Finder patterns at (0,0), (0, 14), (14, 0)
        $finderCoords = [[0, 0], [0, 14], [14, 0]];
        foreach ($finderCoords as [$fr, $fc]) {
            for ($r = 0; $r < 7; $r++) {
                for ($c = 0; $c < 7; $c++) {
                    $isBlack = ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4));
                    $grid[$fr + $r][$fc + $c] = $isBlack ? 1 : 0;
                    $reserved[$fr + $r][$fc + $c] = true;
                }
            }
        }

        // 2. Separators around finders
        for ($i = 0; $i < 8; $i++) {
            // Top-left
            if ($i < $size) {
                if (isset($grid[7][$i])) { $grid[7][$i] = 0; $reserved[7][$i] = true; }
                if (isset($grid[$i][7])) { $grid[$i][7] = 0; $reserved[$i][7] = true; }
            }
            // Top-right
            $c = 13 + $i;
            if ($c < $size) {
                if (isset($grid[7][$c])) { $grid[7][$c] = 0; $reserved[7][$c] = true; }
            }
            if ($i < 8 && isset($grid[$i][13])) {
                $grid[$i][13] = 0; $reserved[$i][13] = true;
            }
            // Bottom-left
            $r = 13 + $i;
            if ($r < $size) {
                if (isset($grid[$r][7])) { $grid[$r][7] = 0; $reserved[$r][7] = true; }
            }
            if ($i < 8 && isset($grid[13][$i])) {
                $grid[13][$i] = 0; $reserved[13][$i] = true;
            }
        }

        // 3. Timing patterns at row 6 and col 6
        for ($i = 8; $i < 13; $i++) {
            $color = ($i % 2 === 0) ? 1 : 0;
            $grid[6][$i] = $color;
            $reserved[6][$i] = true;
            $grid[$i][6] = $color;
            $reserved[$i][6] = true;
        }

        // 4. Dark module at (13, 8)
        $grid[13][8] = 1;
        $reserved[13][8] = true;

        // 5. Reserve format info modules
        for ($i = 0; $i < 9; $i++) {
            $reserved[8][$i] = true;
            $reserved[$i][8] = true;
        }
        for ($i = 0; $i < 8; $i++) {
            $reserved[8][13 + $i] = true;
            $reserved[13 + $i][8] = true;
        }

        // 6. Encode data + EC
        $dataBytes = static::encodeAlphanumeric($text, 16);
        $ecBytes = static::calculateEcc($dataBytes, 10);
        $allBytes = array_merge($dataBytes, $ecBytes);

        $allBits = '';
        foreach ($allBytes as $b) {
            $allBits .= sprintf('%08b', $b);
        }

        // 7. Place data bits (Mask 0: (r + c) % 2 == 0)
        $bitIdx = 0;
        $bitLen = strlen($allBits);
        $up = true;

        for ($col = $size - 1; $col > 0; $col -= 2) {
            if ($col === 6) {
                $col--; // Skip vertical timing pattern
            }
            $rows = $up ? range($size - 1, 0) : range(0, $size - 1);
            foreach ($rows as $row) {
                foreach ([$col, $col - 1] as $c) {
                    if (! $reserved[$row][$c]) {
                        $bit = ($bitIdx < $bitLen) ? (int) $allBits[$bitIdx] : 0;
                        $bitIdx++;
                        // Apply Mask 0: flip if (row + col) % 2 === 0
                        if (($row + $c) % 2 === 0) {
                            $bit ^= 1;
                        }
                        $grid[$row][$c] = $bit;
                    }
                }
            }
            $up = ! $up;
        }

        // 8. Format info for Level M, Mask 0
        $formatBits = '101010000010010';
        $fmtCoords1 = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5],
            [8, 7], [8, 8], [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8]
        ];
        $fmtCoords2 = [
            [20, 8], [19, 8], [18, 8], [17, 8], [16, 8], [15, 8], [14, 8],
            [8, 13], [8, 14], [8, 15], [8, 16], [8, 17], [8, 18], [8, 19], [8, 20]
        ];

        for ($i = 0; $i < 15; $i++) {
            $bit = (int) $formatBits[$i];
            [$r1, $c1] = $fmtCoords1[$i];
            $grid[$r1][$c1] = $bit;
            [$r2, $c2] = $fmtCoords2[$i];
            $grid[$r2][$c2] = $bit;
        }

        // Clean any nulls
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($grid[$r][$c] === null) {
                    $grid[$r][$c] = 0;
                }
            }
        }

        return $grid;
    }

    /**
     * Generate pure SVG string of QR Code.
     */
    public static function svg(string $text, int $displaySize = 100, int $quietZone = 2, string $darkColor = '#000000', string $lightColor = '#ffffff'): string
    {
        $grid = static::matrixV1M($text);
        $matrixSize = count($grid);
        $totalSize = $matrixSize + ($quietZone * 2);

        $rects = [];
        if ($lightColor !== 'transparent') {
            $rects[] = sprintf('<rect width="%d" height="%d" fill="%s"/>', $totalSize, $totalSize, $lightColor);
        }

        for ($r = 0; $r < $matrixSize; $r++) {
            for ($c = 0; $c < $matrixSize; $c++) {
                if ($grid[$r][$c] === 1) {
                    $rects[] = sprintf('<rect x="%d" y="%d" width="1" height="1" fill="%s"/>', $c + $quietZone, $r + $quietZone, $darkColor);
                }
            }
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" shape-rendering="crispEdges" style="display:inline-block; vertical-align:middle;">%s</svg>',
            $displaySize,
            $displaySize,
            $totalSize,
            $totalSize,
            implode('', $rects)
        );
    }

    /**
     * Generate Base64 PNG Data URI (100% compatible with DomPDF and all PDF renderers).
     */
    public static function pngDataUri(string $text, int $scale = 4, int $quietZone = 2): string
    {
        $grid = static::matrixV1M($text);
        $size = count($grid);
        $totalModules = $size + ($quietZone * 2);
        $imgSize = $totalModules * $scale;

        $img = imagecreatetruecolor($imgSize, $imgSize);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);

        imagefilledrectangle($img, 0, 0, $imgSize - 1, $imgSize - 1, $white);

        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($grid[$r][$c] === 1) {
                    $x1 = ($c + $quietZone) * $scale;
                    $y1 = ($r + $quietZone) * $scale;
                    $x2 = $x1 + $scale - 1;
                    $y2 = $y1 + $scale - 1;
                    imagefilledrectangle($img, $x1, $y1, $x2, $y2, $black);
                }
            }
        }

        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($pngData);
    }

    /**
     * Generate Base64 Data URI SVG for HTML img tag.
     */
    public static function dataUri(string $text, int $displaySize = 100, int $quietZone = 2, string $darkColor = '#000000', string $lightColor = '#ffffff'): string
    {
        $svg = static::svg($text, $displaySize, $quietZone, $darkColor, $lightColor);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
