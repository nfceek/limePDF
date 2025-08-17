<?php

namespace LimePDF\Text;

use LimePDF\Include\FontTrait;
use LimePDF\Include\FontDataTrait;
use LimePDF\Support\StaticTrait;

trait WriteTrait{
    /**
     * Main Write function - orchestrates the text writing process
     * PHP 8+ compliant
     */
    public function Write(
        float $h, 
        string $txt, 
        string $link = '', 
        bool $fill = false, 
        string $align = '', 
        bool $ln = false, 
        int $stretch = 0, 
        bool $firstline = false, 
        bool $firstblock = false, 
        float $maxh = 0, 
        float $wadj = 0, 
        ?array $margin = null
    ): string|int {
        // Check page for no-write regions and adapt page margins if necessary
        [$this->x, $this->y] = $this->checkPageRegions($h, $this->x, $this->y);
        
        // Prepare and validate text input
        $textData = $this->prepareTextForWrite($txt, $margin);
        
        // Calculate character metrics and text direction
        $charMetrics = $this->calculateCharacterMetrics($textData['text'], $textData['chars']);
        $textDirection = $this->detectTextDirection($textData['text']);
        
        // Calculate line constraints and boundaries
        $constraints = $this->calculateLineConstraints($h, $wadj, $firstline, $textData['margin']);
        
        // Check if maximum width character fits in column
        if (!$firstline && ($charMetrics['chrwidth'] > $constraints['wmax'] || $charMetrics['maxchwidth'] > $constraints['wmax'])) {
            return '';
        }
        
        // Prepare parameters for character processing
        $params = $this->buildProcessingParams(
            $h, $align, $fill, $link, $stretch, $firstline, $firstblock, 
            $maxh, $ln, $textDirection, $constraints, $textData['margin']
        );
        
        // Process characters and handle line breaks
        return $this->processCharacters(
            $textData['chars'], 
            $textData['uchars'], 
            $charMetrics,
            $constraints, 
            $params
        );
    }
    
    /**
     * Prepare and validate text input
     */
    private function prepareTextForWrite(string $txt, ?array $margin): array 
    {
        // Fix empty text
        $text = strlen($txt) === 0 ? ' ' : $txt;
        
        // Set default margins
        $processedMargin = $margin ?? $this->cell_margin;
        
        // Remove carriage returns
        $cleanText = str_replace("\r", '', $text);
        
        // Get character arrays
        $chars = $this->UTF8StringToArray($cleanText, $this->isunicode, $this->CurrentFont);
        $uchars = $this->UTF8ArrayToUniArray($chars, $this->isunicode);
        
        return [
            'text' => $cleanText,
            'chars' => $chars,
            'uchars' => $uchars,
            'margin' => $processedMargin,
            'nb' => count($chars)
        ];
    }
    
    /**
     * Calculate character metrics and widths
     */
    private function calculateCharacterMetrics(string $text, array $chars): array 
    {
        // Get a char width (dot character)
        $chrwidth = $this->GetCharWidth(46);
        
        // Calculate maximum width for a single character
        $chrw = $this->GetArrStringWidth($chars, '', '', 0, true);
        array_walk($chrw, [$this, 'getRawCharWidth']);
        $maxchwidth = (is_countable($chrw) && count($chrw) > 0) ? max($chrw) : 0;
        
        // SHY character replacement setup
        $shy_replacement = 45;
        $shy_replacement_char = $this->unichr($shy_replacement, $this->isunicode);
        $shy_replacement_width = $this->GetCharWidth($shy_replacement);
        
        return [
            'chrwidth' => $chrwidth,
            'maxchwidth' => $maxchwidth,
            'shy_replacement' => $shy_replacement,
            'shy_replacement_char' => $shy_replacement_char,
            'shy_replacement_width' => $shy_replacement_width
        ];
    }
    
    /**
     * Detect text direction (RTL/Arabic)
     */
    private function detectTextDirection(string $text): array 
    {
        $arabic = (bool) preg_match(self::$uni_RE_PATTERN_ARABIC, $text);
        $rtlmode = $arabic || 
                   ($this->tmprtl === 'R') || 
                   (bool) preg_match(self::$uni_RE_PATTERN_RTL, $text);
        
        return [
            'arabic' => $arabic,
            'rtlmode' => $rtlmode
        ];
    }
    
    /**
     * Calculate line width constraints and boundaries
     */
    private function calculateLineConstraints(float $h, float $wadj, bool $firstline, array $margin): array 
    {
        // Page width calculations
        $pw = $this->w - $this->lMargin - $this->rMargin;
        
        // Calculate remaining line width
        $w = $this->rtl 
            ? $this->x - $this->lMargin
            : $this->w - $this->rMargin - $this->x;
        
        // Max column width
        $wmax = $w - $wadj;
        if (!$firstline) {
            $wmax -= ($this->cell_padding['L'] + $this->cell_padding['R']);
        }
        
        // Row height calculations
        $row_height = max($h, $this->getCellHeight($this->FontSize));
        
        return [
            'pw' => $pw,
            'w' => $w,
            'wmax' => $wmax,
            'row_height' => $row_height
        ];
    }
    
    /**
     * Build parameters object for character processing
     */
    private function buildProcessingParams(
        float $h, 
        string $align, 
        bool $fill, 
        string $link, 
        int $stretch, 
        bool $firstline, 
        bool $firstblock, 
        float $maxh,
        bool $ln,
        array $textDirection,
        array $constraints,
        ?array $margin
    ): array {
        return [
            'h' => $h,
            'align' => $align,
            'fill' => $fill,
            'link' => $link,
            'stretch' => $stretch,
            'firstline' => $firstline,
            'firstblock' => $firstblock,
            'maxh' => $maxh,
            'ln' => $ln,
            'maxy' => $this->y + $maxh - max($constraints['row_height'], $h),
            'start_page' => $this->page,
            'arabic' => $textDirection['arabic'],
            'rtlmode' => $textDirection['rtlmode'],
            'margin' => $margin
        ];
    }
    
    /**
     * Main character processing loop
     */
    private function processCharacters(
        array $chars, 
        array $uchars, 
        array $charMetrics,
        array $constraints, 
        array $params
    ): string|int {
        $nb = count($chars);
        $i = 0; // character position
        $j = 0; // current starting position
        $sep = -1; // position of the last blank space
        $prevsep = $sep;
        $shy = false; // true if the last blank is a soft hyphen (SHY)
        $prevshy = $shy;
        $l = 0; // current string length
        $nl = 0; // number of lines
        $linebreak = false;
        $pc = 0; // previous character
        
        $w = $constraints['w'];
        $wmax = $constraints['wmax'];
        
        while ($i < $nb) {
            if (($params['maxh'] > 0) && ($this->y > $params['maxy'])) {
                break;
            }
            
            $c = $chars[$i];
            
            if ($c === 10) { // "\n" = new line
                $result = $this->handleExplicitLineBreak(
                    $chars, $uchars, $i, $j, $w, $constraints, $params, $nl
                );
                
                if ($result['return'] !== null) {
                    return $result['return'];
                }
                
                [$j, $l, $sep, $prevsep, $shy, $nl, $w, $wmax] = array_values($result['state']);
                
            } else {
                // Handle regular characters and word wrapping
                $spaceResult = $this->handleSpaceAndSeparators($c, $i, $chars, $nb, $pc, $sep, $prevsep, $shy, $prevshy, $charMetrics);
                [$sep, $prevsep, $shy, $prevshy] = array_values($spaceResult);
                
                // Update string length
                $l = $this->updateStringLength($chars, $j, $i, $l, $c, $nb, $params['arabic']);
                
                // Check for line overflow
                $tmp_shy_replacement_width = $spaceResult['tmp_shy_replacement_width'] ?? 0;
                
                if (($l > $wmax) || (($c === 173) && (($l + $tmp_shy_replacement_width) >= $wmax))) {
                    $wrapResult = $this->handleLineOverflow(
                        $chars, $uchars, $i, $j, $sep, $prevsep, $shy, $prevshy, 
                        $l, $w, $constraints, $params, $charMetrics, $tmp_shy_replacement_width
                    );
                    
                    if ($wrapResult['return'] !== null) {
                        return $wrapResult['return'];
                    }
                    
                    if (isset($wrapResult['state'])) {
                        [$i, $j, $sep, $shy, $w, $wmax, $linebreak, $nl, $l] = array_values($wrapResult['state']);
                    }
                }
            }
            
            $pc = $c;
            ++$i;
        }
        
        // Handle final substring
        return $this->handleFinalSubstring($chars, $uchars, $j, $nb, $l, $w, $constraints, $params, $nl);
    }
    
    /**
     * Handle explicit line break (\n character)
     */
    private function handleExplicitLineBreak(
        array $chars, 
        array $uchars, 
        int $i, 
        int $j, 
        float $w, 
        array $constraints, 
        array $params,
        int &$nl
    ): array {
        // Determine alignment for justified text
        $talign = match($params['align']) {
            'J' => $this->rtl ? 'R' : 'L',
            default => $params['align']
        };
        
        $tmpstr = $this->UniArrSubString($uchars, $j, $i);
        
        if ($params['firstline']) {
            $lineData = $this->calculateFirstLineMetrics($chars, $j, $i, $tmpstr, $params);
            $w = $lineData['w'];
            $this->endlinex = $lineData['endlinex'];
            
            $tmpcellpadding = $this->cell_padding;
            if ($params['maxh'] === 0) {
                $this->setCellPadding(0);
            }
        }
        
        if ($params['firstblock'] && $this->isRTLTextDir()) {
            $tmpstr = $this->stringRightTrim($tmpstr);
        }
        
        // Skip newlines at the beginning of a page or column
        if (!empty($tmpstr) || ($this->y < ($this->PageBreakTrigger - $constraints['row_height']))) {
            $this->Cell($w, $params['h'], $tmpstr, 0, 1, $talign, $params['fill'], $params['link'], $params['stretch']);
        }
        
        if ($params['firstline']) {
            $this->cell_padding = $tmpcellpadding;
            return ['return' => $this->UniArrSubString($uchars, $i), 'state' => null];
        }
        
        ++$nl;
        $this->handlePageBreakMargins($params['margin']);
        
        $newW = $this->getRemainingWidth();
        $newWmax = $newW - $this->cell_padding['L'] - $this->cell_padding['R'];
        
        return [
            'return' => null,
            'state' => [
                'j' => $i + 1,
                'l' => 0,
                'sep' => -1,
                'prevsep' => -1,
                'shy' => false,
                'nl' => $nl,
                'w' => $newW,
                'wmax' => $newWmax
            ]
        ];
    }
    
    /**
     * Handle space characters and separators for word wrapping
     */
    private function handleSpaceAndSeparators(
        int $c, 
        int $i, 
        array $chars, 
        int $nb, 
        int $pc,
        int $sep, 
        int $prevsep, 
        bool $shy, 
        bool $prevshy,
        array $charMetrics
    ): array {
        $result = [
            'sep' => $sep,
            'prevsep' => $prevsep, 
            'shy' => $shy,
            'prevshy' => $prevshy
        ];
        
        // Check for various separator conditions
        if (($c !== 160) && (
            ($c === 173) ||
            preg_match($this->re_spaces, $this->unichr($c, $this->isunicode)) ||
            (($c === 45) && 
             ($i < ($nb - 1)) &&
             @preg_match('/[\p{L}]/' . $this->re_space['m'], $this->unichr($pc, $this->isunicode)) &&
             @preg_match('/[\p{L}]/' . $this->re_space['m'], $this->unichr($chars[$i + 1], $this->isunicode))
            )
        )) {
            $result['prevsep'] = $sep;
            $result['sep'] = $i;
            
            // Check if it's a SHY character
            if (($c === 173) || ($c === 45)) {
                $result['prevshy'] = $shy;
                $result['shy'] = true;
                
                if ($pc === 45) {
                    $result['tmp_shy_replacement_width'] = 0;
                    $result['tmp_shy_replacement_char'] = '';
                } else {
                    $result['tmp_shy_replacement_width'] = $charMetrics['shy_replacement_width'];
                    $result['tmp_shy_replacement_char'] = $charMetrics['shy_replacement_char'];
                }
            } else {
                $result['shy'] = false;
            }
        }
        
        return $result;
    }
    
    /**
     * Update string length calculation
     */
    private function updateStringLength(
        array $chars, 
        int $j, 
        int $i, 
        float $l, 
        int $c, 
        int $nb, 
        bool $arabic
    ): float {
        if ($this->isUnicodeFont() && $arabic) {
            // Bidirectional algorithm - slower but necessary for Arabic
            return $this->GetArrStringWidth(
                $this->utf8Bidi(
                    array_slice($chars, $j, ($i - $j)), 
                    '', 
                    $this->tmprtl, 
                    $this->isunicode, 
                    $this->CurrentFont
                )
            );
        }
        
        return $l + $this->GetCharWidth($c, ($i + 1 < $nb));
    }
    
    /**
     * Handle line overflow and word wrapping
     */
    private function handleLineOverflow(
        array $chars,
        array $uchars, 
        int $i, 
        int $j, 
        int $sep, 
        int $prevsep, 
        bool $shy, 
        bool $prevshy,
        float $l,
        float $w,
        array $constraints,
        array $params,
        array $charMetrics,
        float $tmp_shy_replacement_width
    ): array {
        $c = $chars[$i];
        
                // Adjust separator position for SHY characters
        if (($c === 173) && (($l + $tmp_shy_replacement_width) > $constraints['wmax'])) {
            $sep = $prevsep;
            $shy = $prevshy;
        }
        
        if ($sep === -1) {
            // No separator found - handle as single word
            return $this->handleNoSeparatorOverflow($chars, $uchars, $i, $j, $w, $constraints, $params);
        }
        
        // Handle word wrapping
        return $this->handleWordWrapping($chars, $uchars, $i, $j, $sep, $shy, $w, $constraints, $params, $charMetrics, $tmp_shy_replacement_width);
    }
    
    /**
     * Handle overflow when no separator is found
     */
    private function handleNoSeparatorOverflow(
        array $chars,
        array $uchars,
        int $i,
        int $j, 
        float $w,
        array $constraints,
        array $params
    ): array {
        $margin = $params['margin'];
        
        // Check if line was already started
        $lineStarted = $this->rtl 
            ? ($this->x <= ($this->w - $this->rMargin - $this->cell_padding['R'] - $margin['R'] - $constraints['chrwidth']))
            : ($this->x >= ($this->lMargin + $this->cell_padding['L'] + $margin['L'] + $constraints['chrwidth']));
        
        if ($lineStarted) {
            // Print void cell and go to next line
            $this->Cell($w, $params['h'], '', 0, 1);
            $linebreak = true;
            
            if ($params['firstline']) {
                return ['return' => $this->UniArrSubString($uchars, $j), 'state' => null];
            }
        } else {
            // Truncate the word
            $result = $this->renderTruncatedWord($chars, $uchars, $i, $j, $w, $constraints, $params);
            if ($result['return'] !== null) {
                return $result;
            }
            $j = $i;
            --$i;
        }
        
        $this->handlePageBreakMargins($margin);
        $newW = $this->getRemainingWidth();
        $newWmax = $newW - $this->cell_padding['L'] - $this->cell_padding['R'];
        
        return [
            'return' => null,
            'state' => [$i, $j, -1, false, $newW, $newWmax, $linebreak ?? false, 1, 0]
        ];
    }
    
    /**
     * Handle word wrapping when separator is found
     */
    private function handleWordWrapping(
        array $chars,
        array $uchars,
        int $i,
        int $j,
        int $sep,
        bool $shy,
        float $w,
        array $constraints,
        array $params,
        array $charMetrics,
        float $tmp_shy_replacement_width
    ): array {
        $endspace = ($this->rtl && !$params['firstblock'] && ($sep < $i)) ? 1 : 0;
        
        // Check if next word fits on full page
        $strrest = $this->UniArrSubString($uchars, ($sep + $endspace));
        $nextstr = $this->pregSplit('/' . $this->re_space['p'] . '/', $this->re_space['m'], $this->stringTrim($strrest));
        
        if (isset($nextstr[0]) && ($this->GetStringWidth($nextstr[0]) > $constraints['pw'])) {
            // Truncate because word doesn't fit on full page
            $result = $this->renderTruncatedWord($chars, $uchars, $i, $j, $w, $constraints, $params);
            if ($result['return'] !== null) {
                return $result;
            }
            $j = $i;
            --$i;
        } else {
            // Normal word wrapping
            $result = $this->renderWrappedLine($chars, $uchars, $j, $sep, $endspace, $shy, $w, $constraints, $params, $charMetrics, $tmp_shy_replacement_width);
            if ($result['return'] !== null) {
                return $result;
            }
            $i = $sep;
            $sep = -1;
            $shy = false;
            $j = ($i + 1);
        }
        
        $margin = $params['margin'];
        $this->handlePageBreakMargins($margin);
        $newW = $this->getRemainingWidth();
        $newWmax = $newW - $this->cell_padding['L'] - $this->cell_padding['R'];
        
        return [
            'return' => null,
            'state' => [$i, $j, $sep, $shy, $newW, $newWmax, false, 1, 0]
        ];
    }
    
    /**
     * Render a truncated word that doesn't fit
     */
    private function renderTruncatedWord(
        array $chars,
        array $uchars,
        int $i,
        int $j,
        float $w,
        array $constraints,
        array $params
    ): array {
        $tmpstr = $this->UniArrSubString($uchars, $j, $i);
        
        if ($params['firstline']) {
            $lineData = $this->calculateFirstLineMetrics($chars, $j, $i, $tmpstr, $params);
            $w = $lineData['w'];
            $this->endlinex = $lineData['endlinex'];
            
            $tmpcellpadding = $this->cell_padding;
            if ($params['maxh'] === 0) {
                $this->setCellPadding(0);
            }
        }
        
        if ($params['firstblock'] && $this->isRTLTextDir()) {
            $tmpstr = $this->stringRightTrim($tmpstr);
        }
        
        $this->Cell($w, $params['h'], $tmpstr, 0, 1, $params['align'], $params['fill'], $params['link'], $params['stretch']);
        
        if ($params['firstline']) {
            $this->cell_padding = $tmpcellpadding;
            return ['return' => $this->UniArrSubString($uchars, $i), 'state' => null];
        }
        
        return ['return' => null, 'state' => null];
    }
    
    /**
     * Render a wrapped line with proper hyphenation
     */
    private function renderWrappedLine(
        array $chars,
        array $uchars,
        int $j,
        int $sep,
        int $endspace,
        bool $shy,
        float $w,
        array $constraints,
        array $params,
        array $charMetrics,
        float $tmp_shy_replacement_width
    ): array {
        // Handle hyphenation
        if ($shy) {
            $shy_width = $tmp_shy_replacement_width;
            if ($this->rtl) {
                $shy_char_left = $charMetrics['shy_replacement_char'];
                $shy_char_right = '';
            } else {
                $shy_char_left = '';
                $shy_char_right = $charMetrics['shy_replacement_char'];
            }
        } else {
            $shy_width = 0;
            $shy_char_left = '';
            $shy_char_right = '';
        }
        
        $tmpstr = $this->UniArrSubString($uchars, $j, ($sep + $endspace));
        
        if ($params['firstline']) {
            $lineData = $this->calculateFirstLineMetrics($chars, $j, ($sep + $endspace), $tmpstr, $params);
            $w = $lineData['w'];
            $this->endlinex = $lineData['endlinex'] + ($this->rtl ? -$shy_width : $shy_width);
            
            $tmpcellpadding = $this->cell_padding;
            if ($params['maxh'] === 0) {
                $this->setCellPadding(0);
            }
        }
        
        if ($params['firstblock'] && $this->isRTLTextDir()) {
            $tmpstr = $this->stringRightTrim($tmpstr);
        }
        
        $this->Cell($w, $params['h'], $shy_char_left . $tmpstr . $shy_char_right, 0, 1, $params['align'], $params['fill'], $params['link'], $params['stretch']);
        
        if ($params['firstline']) {
            if ($chars[$sep] === 45) {
                $endspace += 1;
            }
            $this->cell_padding = $tmpcellpadding;
            return ['return' => $this->UniArrSubString($uchars, ($sep + $endspace)), 'state' => null];
        }
        
        return ['return' => null, 'state' => null];
    }
    
    /**
     * Calculate metrics for first line rendering
     */
    private function calculateFirstLineMetrics(
        array $chars,
        int $start,
        int $end,
        string $tmpstr,
        array $params
    ): array {
        $startx = $this->x;
        $tmparr = array_slice($chars, $start, ($end - $start));
        
        if ($params['rtlmode']) {
            $tmparr = $this->utf8Bidi($tmparr, $tmpstr, $this->tmprtl, $this->isunicode, $this->CurrentFont);
        }
        
        $linew = $this->GetArrStringWidth($tmparr);
        $endlinex = $this->rtl ? ($startx - $linew) : ($startx + $linew);
        
        return [
            'w' => $linew,
            'endlinex' => $endlinex
        ];
    }
    
    /**
     * Handle final substring rendering
     */
    private function handleFinalSubstring(
        array $chars,
        array $uchars,
        int $j,
        int $nb,
        float $l,
        float $w,
        array $constraints,
        array $params,
        int $nl
    ): string|int {
        if ($l <= 0) {
            return $params['firstline'] ? '' : $nl;
        }
        
        // Calculate width based on alignment using PHP 8 match expression
        $finalWidth = match($params['align']) {
            'J', 'C' => $w,
            'L' => !$this->rtl ? $l : $w,
            'R' => $this->rtl ? $l : $w,
            default => $l
        };
        
        $tmpstr = $this->UniArrSubString($uchars, $j, $nb);
        
        if ($params['firstline']) {
            $lineData = $this->calculateFirstLineMetrics($chars, $j, $nb, $tmpstr, $params);
            $finalWidth = $lineData['w'];
            $this->endlinex = $lineData['endlinex'];
            
            $tmpcellpadding = $this->cell_padding;
            if ($params['maxh'] === 0) {
                $this->setCellPadding(0);
            }
        }
        
        if ($params['firstblock'] && $this->isRTLTextDir()) {
            $tmpstr = $this->stringRightTrim($tmpstr);
        }
        
        $this->Cell($finalWidth, $params['h'], $tmpstr, 0, $params['ln'], $params['align'], $params['fill'], $params['link'], $params['stretch']);
        
        if ($params['firstline']) {
            $this->cell_padding = $tmpcellpadding;
            return $this->UniArrSubString($uchars, $nb);
        }
        
        return $nl + 1;
    }
    
    /**
     * Handle page breaks and margin adjustments
     */
    private function handlePageBreakMargins(?array $margin): void 
    {
        if ((($this->y + $this->lasth) > $this->PageBreakTrigger) && $this->inPageBody()) {
            if ($this->AcceptPageBreak()) {
                if ($this->rtl) {
                    $this->x -= $margin['R'] ?? 0;
                } else {
                    $this->x += $margin['L'] ?? 0;
                }
                $this->lMargin += $margin['L'] ?? 0;
                $this->rMargin += $margin['R'] ?? 0;
            }
        }
    }
}