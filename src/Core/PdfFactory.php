<?php

namespace limePDF\Core;

class PdfFactory {
    public static function create(array $options = []): CorePDF
    {
        $cfg = new ConfigManager();

        // Load defaults from file if needed
        if (file_exists(__DIR__ . '/../../config/pdf-defaults.json')) {
            $cfg->loadFromJson(__DIR__ . '/../../config/pdf-defaults.json');
        }

        // Override with runtime options
        $cfg->loadFromArray($options);

        return new CorePDF($cfg);
    }
}
