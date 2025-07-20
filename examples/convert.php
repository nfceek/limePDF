<?php
// Path to your original ConfigManager file
$inputFile = '../tcpdf_autoconfig.php';
$outputFile = 'modern_config_array.php';

// Choose output style: 'array' or 'class'
$outputMode = 'array';

$constants = [];
$lines = file($inputFile);

foreach ($lines as $line) {
    if (preg_match('/define\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(.+?)\s*\)\s*;/', $line, $matches)) {
        $key = $matches[1];
        $value = trim($matches[2]);

        // Normalize key name to lower_case_with_underscores
        $normalizedKey = strtolower(preg_replace('/^PDF_|^K_|_/', '', $key));
        $normalizedKey = preg_replace('/[^a-z0-9_]+/', '_', $normalizedKey);

        // Convert known constants to native types
        if (in_array(strtolower($value), ['true', 'false'])) {
            $value = strtolower($value);
        } elseif (is_numeric($value)) {
            // leave as-is
        } elseif (preg_match('/^\[.*\]$/', $value)) {
            // assume array
        } elseif (!str_starts_with($value, "'") && !str_starts_with($value, '"')) {
            $value = "'$value'";
        }

        $constants[$normalizedKey] = $value;
    }
}

// Build the output
$output = "<?php\n\n";
if ($outputMode === 'array') {
    $output .= "// Modernized config array format\nreturn [\n";
    foreach ($constants as $key => $value) {
        $output .= "    '$key' => $value,\n";
    }
    $output .= "];\n";
} else {
    $output .= "class ConfigManager {\n    protected array \$config = [\n";
    foreach ($constants as $key => $value) {
        $output .= "        '$key' => $value,\n";
    }
    $output .= "    ];\n\n    public function get(string \$key): mixed {\n        return \$this->config[\$key] ?? null;\n    }\n\n    public function all(): array {\n        return \$this->config;\n    }\n}\n";
}

// Write to file
file_put_contents($outputFile, $output);

echo "✅ Config rewritten to: $outputFile\n";
