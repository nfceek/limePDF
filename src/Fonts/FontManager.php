<?php

namespace LimePDF\Fonts;

class FontManager
{
    protected array $fonts = [];
    protected array $fontkeys = [];
    protected int $n = 0;
    protected array $font_obj_ids = [];
}