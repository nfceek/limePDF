<?php

$sourceFile = 'tcpdf-2-refactor.php';
$outputFile = 'functions-2-check.txt';

// Check if source file exists
if (!file_exists($sourceFile)) {
    die("Error: Source file '$sourceFile' not found.\n");
}

// Open source file for reading
$sourceHandle = fopen($sourceFile, 'r');
if (!$sourceHandle) {
    die("Error: Could not open source file '$sourceFile' for reading.\n");
}

// Open output file for writing
$outputHandle = fopen($outputFile, 'w');
if (!$outputHandle) {
    fclose($sourceHandle);
    die("Error: Could not open output file '$outputFile' for writing.\n");
}

$lineCount = 0;
$extractedCount = 0;

// Read file line by line
while (($line = fgets($sourceHandle)) !== false) {
    $lineCount++;
    
    // Trim whitespace and check if first word is "protected"
    $trimmedLine = ltrim($line);
    
    if (strpos($trimmedLine, 'protected') === 0) {
        // Check if it's actually the first word (followed by space or tab)
        if (preg_match('/^protected\s/', $trimmedLine)) {
            fwrite($outputHandle, $line);
            $extractedCount++;
        }
    }
}

// Close file handles
fclose($sourceHandle);
fclose($outputHandle);

echo "Script completed successfully!\n";
echo "Total lines processed: $lineCount\n";
echo "Lines starting with 'protected': $extractedCount\n";
echo "Output written to: $outputFile\n";

?>
