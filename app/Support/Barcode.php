<?php

namespace App\Support;

class Barcode
{
    /**
     * Code 128 character patterns (widths of bars and spaces).
     * Index corresponds to Code 128 value (0 - 106).
     */
    protected static array $patterns = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112'
    ];

    /**
     * Generate Code 128 Barcode as pure inline SVG.
     *
     * @param string $text Text to encode (e.g. 'SMK-202611-XMCX')
     * @param int $height Height of barcode in pixels
     * @param float $moduleWidth Width of single module in pixels
     * @param string $barColor Color of bars (default '#000000')
     * @param string $bgColor Background color (or 'transparent')
     */
    public static function code128Svg(string $text, int $height = 42, float $moduleWidth = 1.6, string $barColor = '#000000', string $bgColor = 'transparent'): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        // Start with Code B (Value 104)
        $startB = 104;
        $values = [$startB];
        $checksum = $startB;

        for ($i = 0; $i < strlen($text); $i++) {
            $charVal = ord($text[$i]) - 32;
            if ($charVal < 0 || $charVal > 95) {
                $charVal = 0; // fallback to space
            }
            $values[] = $charVal;
            $checksum += $charVal * ($i + 1);
        }

        $checksumVal = $checksum % 103;
        $values[] = $checksumVal;
        $values[] = 106; // Stop code

        // Assemble modules (1 for bar, 0 for space)
        $modules = '';
        foreach ($values as $val) {
            $pattern = static::$patterns[$val] ?? '212222';
            $isBar = true;
            for ($p = 0; $p < strlen($pattern); $p++) {
                $w = (int) $pattern[$p];
                $modules .= str_repeat($isBar ? '1' : '0', $w);
                $isBar = ! $isBar;
            }
        }
        // Terminal bar of stop code (always 2 modules wide)
        $modules .= '11';

        $totalModules = strlen($modules);
        $totalWidth = (int) ceil($totalModules * $moduleWidth);

        // Build SVG rects
        $svgElements = [];
        if ($bgColor !== 'transparent') {
            $svgElements[] = sprintf('<rect width="%d" height="%d" fill="%s"/>', $totalWidth, $height, $bgColor);
        }

        $pos = 0;
        $barStart = null;
        for ($m = 0; $m < $totalModules; $m++) {
            if ($modules[$m] === '1') {
                if ($barStart === null) {
                    $barStart = $m;
                }
            } else {
                if ($barStart !== null) {
                    $rectWidth = ($m - $barStart) * $moduleWidth;
                    $rectX = $barStart * $moduleWidth;
                    $svgElements[] = sprintf(
                        '<rect x="%.2f" y="0" width="%.2f" height="%d" fill="%s"/>',
                        $rectX,
                        $rectWidth,
                        $height,
                        $barColor
                    );
                    $barStart = null;
                }
            }
        }
        if ($barStart !== null) {
            $rectWidth = ($totalModules - $barStart) * $moduleWidth;
            $rectX = $barStart * $moduleWidth;
            $svgElements[] = sprintf(
                '<rect x="%.2f" y="0" width="%.2f" height="%d" fill="%s"/>',
                $rectX,
                $rectWidth,
                $height,
                $barColor
            );
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" style="display:inline-block; vertical-align:middle;">%s</svg>',
            $totalWidth,
            $height,
            $totalWidth,
            $height,
            implode('', $svgElements)
        );
    }

    /**
     * Generate Code 128 as Base64 Data URI SVG for DomPDF and img src.
     */
    public static function code128DataUri(string $text, int $height = 42, float $moduleWidth = 1.6, string $barColor = '#000000', string $bgColor = '#ffffff'): string
    {
        $svg = static::code128Svg($text, $height, $moduleWidth, $barColor, $bgColor);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
