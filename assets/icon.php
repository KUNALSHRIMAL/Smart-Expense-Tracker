<?php
/**
 * Generates a PNG app icon dynamically using GD.
 * Served to manifest.json and apple-touch-icon.
 */
header('Content-Type: image/png');
header('Cache-Control: public, max-age=604800');

$size = max(64, min(512, (int)($_GET['size'] ?? 192)));
$img  = imagecreatetruecolor($size, $size);

// Background: near-black
$bg = imagecolorallocate($img, 8, 8, 8);
imagefill($img, 0, 0, $bg);

// Rounded rectangle — approximate with filled arcs + rectangles
$r  = (int)($size * 0.22);   // corner radius
$m  = (int)($size * 0.08);   // margin from edge
$x1 = $m; $y1 = $m; $x2 = $size - $m - 1; $y2 = $size - $m - 1;

$violet = imagecolorallocate($img, 139, 92, 246);

// Fill rect body
imagefilledrectangle($img, $x1 + $r, $y1,      $x2 - $r, $y2, $violet);
imagefilledrectangle($img, $x1,      $y1 + $r,  $x2,     $y2 - $r, $violet);

// Round corners
imagefilledellipse($img, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $violet);
imagefilledellipse($img, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $violet);
imagefilledellipse($img, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $violet);
imagefilledellipse($img, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $violet);

// Draw ₹ symbol using basic GD lines (stylised)
$white = imagecolorallocate($img, 255, 255, 255);
$lw    = max(2, (int)($size * 0.04));   // line weight
$cx    = (int)($size / 2);
$sy    = (int)($size * 0.28);            // symbol top
$ey    = (int)($size * 0.75);            // symbol bottom
$sw    = (int)($size * 0.32);            // symbol half-width

// Vertical stroke of ₹
for ($i = 0; $i < $lw; $i++) {
    imageline($img, $cx - ($lw >> 1) + $i, $sy, $cx - ($lw >> 1) + $i, $ey, $white);
}

// Top horizontal bar
for ($i = 0; $i < $lw; $i++) {
    imageline($img, $cx - $sw, $sy + $i, $cx + $sw, $sy + $i, $white);
}

// Middle horizontal bar
$my = $sy + (int)(($ey - $sy) * 0.35);
for ($i = 0; $i < $lw; $i++) {
    imageline($img, $cx - $sw, $my + $i, $cx + $sw, $my + $i, $white);
}

// Diagonal stroke (bottom right)
$diagY = $my + (int)(($lw * 1.5));
imageline($img, $cx + $sw, $diagY, $cx - $sw, $ey, $white);
for ($i = 1; $i < $lw; $i++) {
    imageline($img, $cx + $sw - $i, $diagY, $cx - $sw - $i, $ey, $white);
}

imagepng($img, null, 6);
imagedestroy($img);
