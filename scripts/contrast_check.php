<?php
/** WCAG contrast ratio calculator for tile-info icon colors */

function hexToRgb(string $hex): array {
    $hex = ltrim($hex, '#');
    return [
        (int) hexdec(substr($hex, 0, 2)),
        (int) hexdec(substr($hex, 2, 2)),
        (int) hexdec(substr($hex, 4, 2)),
    ];
}

function relativeLuminance(array $rgb): float {
    $channels = array_map(function ($c) {
        $s = $c / 255;
        return $s <= 0.03928 ? $s / 12.92 : pow(($s + 0.055) / 1.055, 2.4);
    }, $rgb);
    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

function contrastRatio(string $fg, string $bg): float {
    $l1 = relativeLuminance(hexToRgb($fg));
    $l2 = relativeLuminance(hexToRgb($bg));
    $lighter = max($l1, $l2);
    $darker = min($l1, $l2);
    return ($lighter + 0.05) / ($darker + 0.05);
}

function blendOnWhite(string $fgHex, float $alpha): string {
    [$r, $g, $b] = hexToRgb($fgHex);
    $r = (int) round(255 * (1 - $alpha) + $r * $alpha);
    $g = (int) round(255 * (1 - $alpha) + $g * $alpha);
    $b = (int) round(255 * (1 - $alpha) + $b * $alpha);
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

$bgIcon = blendOnWhite('0dcaf0', 0.16); // rgba(13,202,240,.16) on white
$oldFg = '#0aa8c2';
$newFg = '#025058';
$altFg = '#0b7285';

echo "Icon background (rgba(13,202,240,.16) on #fff): $bgIcon\n";
echo "Old foreground #0aa8c2 vs icon bg: " . round(contrastRatio($oldFg, $bgIcon), 2) . ":1\n";
echo "New foreground #025058 vs icon bg: " . round(contrastRatio($newFg, $bgIcon), 2) . ":1\n";
echo "Alt foreground #0b7285 vs icon bg: " . round(contrastRatio($altFg, $bgIcon), 2) . ":1\n";
