<?php
/**
 * File: scripts/extract_tcpdf_properties.php
 * 
 * Complete script to extract all protected properties from TCPDF
 * and generate the properties configuration file
 */

class TCPDFPropertyExtractor
{
    private static $stats = [
        'total_properties' => 0,
        'properties_with_defaults' => 0,
        'properties_without_defaults' => 0,
        'arrays' => 0,
        'strings' => 0,
        'numbers' => 0,
        'booleans' => 0,
        'nulls' => 0
    ];

    /**
     * Main extraction method
     */
    public static function extractFromFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }

        echo "Reading file: $filePath\n";
        $content = file_get_contents($filePath);
        
        if ($content === false) {
            throw new Exception("Could not read file: $filePath");
        }

        $properties = [];
        
        // Enhanced regex to capture protected properties with better accuracy
        // This handles multiline comments and various declaration styles
        $patterns = [
            // Standard protected property with PHPDoc
            '/\/\*\*.*?\*\/\s*protected\s+\$(\w+)(?:\s*=\s*([^;]+?))?;/ms',
            // Protected property without PHPDoc
            '/(?<!\/\*\*)(?<!\*)\s*protected\s+\$(\w+)(?:\s*=\s*([^;]+?))?;/m',
            // Protected property with inline comment
            '/protected\s+\$(\w+)(?:\s*=\s*([^;\/]+?))?(?:\s*\/\/.*)?;/m'
        ];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $propertyName = $match[1];
                $defaultValue = isset($match[2]) ? trim($match[2]) : null;
                
                // Skip if already found (avoid duplicates)
                if (isset($properties[$propertyName])) {
                    continue;
                }
                
                // Clean up the default value
                if ($defaultValue !== null) {
                    $defaultValue = self::cleanDefaultValue($defaultValue);
                    $properties[$propertyName] = self::parseDefaultValue($defaultValue);
                    self::$stats['properties_with_defaults']++;
                } else {
                    $properties[$propertyName] = null;
                    self::$stats['properties_without_defaults']++;
                }
                
                self::$stats['total_properties']++;
                self::updateStats($properties[$propertyName]);
            }
        }

        // Sort properties alphabetically for better organization
        ksort($properties);
        
        echo "Extraction complete!\n";
        self::printStats();
        
        return $properties;
    }

    /**
     * Clean up default value string
     */
    private static function cleanDefaultValue(string $value): string
    {
        // Remove comments
        $value = preg_replace('/\/\/.*$/', '', $value);
        $value = preg_replace('/\/\*.*?\*\//', '', $value);
        
        // Remove extra whitespace
        $value = trim($value);
        
        // Remove trailing commas
        $value = rtrim($value, ',');
        
        return $value;
    }

    /**
     * Parse default value from string to appropriate PHP type
     */
    private static function parseDefaultValue(?string $value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);

        // Handle specific cases
        if ($value === 'null') return null;
        if ($value === 'true') return true;
        if ($value === 'false') return false;
        
        // Handle arrays
        if ($value === 'array()' || $value === '[]') return [];
        if (preg_match('/^array\s*\(.*\)$/s', $value)) {
            return self::parseArrayValue($value);
        }
        if (preg_match('/^\[.*\]$/s', $value)) {
            return self::parseArrayValue($value);
        }
        
        // Handle numbers
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }
        
        // Handle strings (remove quotes)
        if (preg_match('/^[\'"].*[\'"]$/', $value)) {
            return substr($value, 1, -1); // Remove quotes
        }
        
        // Default: return as string
        return $value;
    }

    /**
     * Parse array values (basic implementation)
     */
    private static function parseArrayValue(string $value): array
    {
        // For complex arrays, we'll just return empty array
        // You can enhance this if you need to preserve complex default arrays
        if ($value === 'array()' || $value === '[]') {
            return [];
        }
        
        // Try to handle simple arrays like array(1, 2, 3) or [1, 2, 3]
        if (preg_match('/^(?:array\s*\(|\[)\s*(.+?)\s*(?:\)|\])$/s', $value, $matches)) {
            $elements = explode(',', $matches[1]);
            $result = [];
            
            foreach ($elements as $element) {
                $element = trim($element);
                if ($element !== '') {
                    $result[] = self::parseDefaultValue($element);
                }
            }
            
            return $result;
        }
        
        return []; // Fallback to empty array
    }

    /**
     * Update statistics
     */
    private static function updateStats($value): void
    {
        if ($value === null) {
            self::$stats['nulls']++;
        } elseif (is_bool($value)) {
            self::$stats['booleans']++;
        } elseif (is_array($value)) {
            self::$stats['arrays']++;
        } elseif (is_numeric($value)) {
            self::$stats['numbers']++;
        } else {
            self::$stats['strings']++;
        }
    }

    /**
     * Print extraction statistics
     */
    private static function printStats(): void
    {
        echo "\n=== EXTRACTION STATISTICS ===\n";
        echo "Total properties found: " . self::$stats['total_properties'] . "\n";
        echo "Properties with defaults: " . self::$stats['properties_with_defaults'] . "\n";
        echo "Properties without defaults: " . self::$stats['properties_without_defaults'] . "\n";
        echo "\nType breakdown:\n";
        echo "- Arrays: " . self::$stats['arrays'] . "\n";
        echo "- Strings: " . self::$stats['strings'] . "\n";
        echo "- Numbers: " . self::$stats['numbers'] . "\n";
        echo "- Booleans: " . self::$stats['booleans'] . "\n";
        echo "- Nulls: " . self::$stats['nulls'] . "\n";
        echo "=============================\n\n";
    }

    /**
     * Generate the properties file content
     */
    public static function generatePropertiesFile(array $properties): string
    {
        $code = "<?php\n\n";
        $code .= "/**\n";
        $code .= " * TCPDF Properties Configuration\n";
        $code .= " * Auto-generated on " . date('Y-m-d H:i:s') . "\n";
        $code .= " * Contains " . count($properties) . " properties extracted from TCPDF\n";
        $code .= " */\n";
        $code .= "class TCPDFProperties\n{\n";
        $code .= "    /**\n";
        $code .= "     * Get all default property values\n";
        $code .= "     * @return array\n";
        $code .= "     */\n";
        $code .= "    public static function getDefaultProperties(): array\n";
        $code .= "    {\n";
        $code .= "        return [\n";

        foreach ($properties as $name => $value) {
            $exportedValue = var_export($value, true);
            // Format the exported value for better readability
            $exportedValue = str_replace('array (', 'array(', $exportedValue);
            $code .= "            '$name' => $exportedValue,\n";
        }

        $code .= "        ];\n";
        $code .= "    }\n\n";
        
        // Add property groups method
        $code .= self::generatePropertyGroups($properties);
        
        $code .= "}\n";

        return $code;
    }

    /**
     * Generate property groups based on naming patterns
     */
    private static function generatePropertyGroups(array $properties): string
    {
        $groups = [
            'core' => [],
            'page' => [],
            'font' => [],
            'color' => [],
            'image' => [],
            'header' => [],
            'footer' => [],
            'margin' => [],
            'border' => [],
            'cell' => [],
            'link' => [],
            'bookmark' => [],
            'javascript' => [],
            'form' => [],
            'annotation' => [],
            'security' => [],
            'metadata' => [],
            'other' => []
        ];

        foreach (array_keys($properties) as $property) {
            $lower = strtolower($property);
            
            // Categorize based on property name patterns
            if (in_array($property, ['page', 'n', 'buffer', 'state', 'offsets'])) {
                $groups['core'][] = $property;
            } elseif (strpos($lower, 'page') !== false) {
                $groups['page'][] = $property;
            } elseif (strpos($lower, 'font') !== false || strpos($lower, 'Font') !== false) {
                $groups['font'][] = $property;
            } elseif (strpos($lower, 'color') !== false || strpos($lower, 'Color') !== false) {
                $groups['color'][] = $property;
            } elseif (strpos($lower, 'image') !== false || strpos($lower, 'img') !== false) {
                $groups['image'][] = $property;
            } elseif (strpos($lower, 'header') !== false) {
                $groups['header'][] = $property;
            } elseif (strpos($lower, 'footer') !== false) {
                $groups['footer'][] = $property;
            } elseif (strpos($lower, 'margin') !== false || strpos($lower, 'Margin') !== false) {
                $groups['margin'][] = $property;
            } elseif (strpos($lower, 'border') !== false) {
                $groups['border'][] = $property;
            } elseif (strpos($lower, 'cell') !== false || strpos($lower, 'Cell') !== false) {
                $groups['cell'][] = $property;
            } elseif (strpos($lower, 'link') !== false) {
                $groups['link'][] = $property;
            } elseif (strpos($lower, 'bookmark') !== false) {
                $groups['bookmark'][] = $property;
            } elseif (strpos($lower, 'js') !== false || strpos($lower, 'javascript') !== false) {
                $groups['javascript'][] = $property;
            } elseif (strpos($lower, 'form') !== false) {
                $groups['form'][] = $property;
            } elseif (strpos($lower, 'annot') !== false) {
                $groups['annotation'][] = $property;
            } elseif (strpos($lower, 'encrypt') !== false || strpos($lower, 'password') !== false) {
                $groups['security'][] = $property;
            } elseif (strpos($lower, 'meta') !== false || strpos($lower, 'author') !== false || strpos($lower, 'title') !== false) {
                $groups['metadata'][] = $property;
            } else {
                $groups['other'][] = $property;
            }
        }

        // Remove empty groups
        $groups = array_filter($groups, function($group) {
            return !empty($group);
        });

        $code = "    /**\n";
        $code .= "     * Get property groups for organized access\n";
        $code .= "     * @return array\n";
        $code .= "     */\n";
        $code .= "    public static function getPropertyGroups(): array\n";
        $code .= "    {\n";
        $code .= "        return [\n";

        foreach ($groups as $groupName => $properties) {
            if (empty($properties)) continue;
            
            $code .= "            '$groupName' => [\n";
            foreach ($properties as $property) {
                $code .= "                '$property',\n";
            }
            $code .= "            ],\n";
        }

        $code .= "        ];\n";
        $code .= "    }\n";

        return $code;
    }

    /**
     * Main runner method
     */
    public static function run(string $tcpdfPath, string $outputDir = null): void
    {
        echo "=== TCPDF Property Extractor ===\n\n";
        
        // Set default output directory
        if ($outputDir === null) {
            $outputDir = dirname($tcpdfPath) . '/include';
        }
        
        // Create output directory if it doesn't exist
        if (!is_dir($outputDir)) {
            if (!mkdir($outputDir, 0755, true)) {
                throw new Exception("Could not create output directory: $outputDir");
            }
            echo "Created output directory: $outputDir\n";
        }

        try {
            // Extract properties
            $properties = self::extractFromFile($tcpdfPath);
            
            // Generate properties file
            echo "Generating properties file...\n";
            $propertiesCode = self::generatePropertiesFile($properties);
            
            // Write properties file
            $propertiesFile = $outputDir . '/tcpdf_properties.php';
            if (file_put_contents($propertiesFile, $propertiesCode) === false) {
                throw new Exception("Could not write properties file: $propertiesFile");
            }
            
            echo "Properties file created: $propertiesFile\n";
            
            // Generate a sample of extracted properties for verification
            self::generateSampleFile($properties, $outputDir);
            
            echo "\n✅ SUCCESS! Property extraction completed.\n";
            echo "Next steps:\n";
            echo "1. Review the generated files in: $outputDir\n";
            echo "2. Copy tcpdf_property_manager.php to the include directory\n";
            echo "3. Update your TCPDF class to use the property manager\n";
            
        } catch (Exception $e) {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * Generate a sample file showing first 20 properties for verification
     */
    private static function generateSampleFile(array $properties, string $outputDir): void
    {
        $sampleFile = $outputDir . '/properties_sample.txt';
        $sample = "TCPDF Properties Sample (first 20 properties)\n";
        $sample .= "Generated on: " . date('Y-m-d H:i:s') . "\n";
        $sample .= "Total properties: " . count($properties) . "\n\n";
        
        $count = 0;
        foreach ($properties as $name => $value) {
            if ($count >= 20) break;
            $sample .= sprintf("%-30s => %s\n", $name, var_export($value, true));
            $count++;
        }
        
        if (count($properties) > 20) {
            $sample .= "\n... and " . (count($properties) - 20) . " more properties\n";
        }
        
        file_put_contents($sampleFile, $sample);
        echo "Sample file created: $sampleFile\n";
    }
}

// Command line runner
if (php_sapi_name() === 'cli') {
    // Get command line arguments
    $tcpdfPath = $argv[1] ?? '';
    $outputDir = $argv[2] ?? null;
    
    if (empty($tcpdfPath)) {
        echo "Usage: php extract_tcpdf_properties.php <path_to_tcpdf.php> [output_directory]\n";
        echo "\nExample:\n";
        echo "php extract_tcpdf_properties.php /path/to/tcpdf.php\n";
        echo "php extract_tcpdf_properties.php /path/to/tcpdf.php /path/to/output\n";
        exit(1);
    }
    
    try {
        TCPDFPropertyExtractor::run($tcpdfPath, $outputDir);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

/**
 * Web interface runner (if accessed via web browser)
 */
if (php_sapi_name() !== 'cli' && isset($_GET['run'])) {
    $tcpdfPath = $_GET['tcpdf_path'] ?? '';
    $outputDir = $_GET['output_dir'] ?? null;
    
    header('Content-Type: text/plain');
    
    if (empty($tcpdfPath)) {
        echo "Error: tcpdf_path parameter is required\n";
        echo "Usage: ?run=1&tcpdf_path=/path/to/tcpdf.php&output_dir=/path/to/output\n";
        exit;
    }
    
    try {
        TCPDFPropertyExtractor::run($tcpdfPath, $outputDir);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

?>