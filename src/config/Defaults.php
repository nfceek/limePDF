<?php
namespace LimePDF\Config;

class Defaults {
    public static array $cellPadding = ['T' => 0, 'R' => 0, 'B' => 0, 'L' => 0];
    public static array $cellMargin = ['T' => 0, 'R' => 0, 'B' => 0, 'L' => 0];
    public static int $startingPageNumber = 1;
    public static float $imageScale = 1.0;
    public static string $pdfVersion = '1.7';
    public static bool $docInfoUnicode = true;
    public static bool $isUnicode = false;
    public static string $title = '';
    public static string $subject = '';
    public static string $author = '';
    public static string $creator = '';
    public static string $keywords = '';

    // Reserved for future: logo, metadata colors, etc.
}
