<?php
// extract_vars.php
$inputFile  = __DIR__ . '/tcpdf.php';
$outputFile = __DIR__ . '/tcpdf.vars.php';

$contents = file_get_contents($inputFile);

// Match all class variables (public/protected/private $varName = value;)
$pattern = '/\s*(public|protected|private)\s+\$[a-zA-Z0-9_]+\s*(=\s*[^;]+)?;/m';
preg_match_all($pattern, $contents, $matches);

// Create output file content
$output  = "<?php\n";
$output .= "// Auto-generated from tcpdf.php\n";
$output .= "namespace LimePDF;\n\n";
$output .= "trait TCPDF_Vars {\n";

foreach ($matches[0] as $line) {
    // Clean and indent
    $output .= "    {$line}\n";
}

$output .= "}\n";

// Save to file
file_put_contents($outputFile, $output);

echo "Extracted " . count($matches[0]) . " variables to $outputFile\n";
