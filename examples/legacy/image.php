<?php
// Your image is valid, so let's fix the display issues
require_once __DIR__ . '/../../src/PDF.php';
require_once '../../vendor/autoload.php'; 

use LimePDF\Pdf;

$pdf = new Pdf();

use LimePDF\Config\ConfigManager;

// Instantiate and load ConfigManager
$config = new ConfigManager();
$config->loadFromArray([
]);

// Deep debugging for missing images in PDF

$imgHeaderPath = dirname(__DIR__) . '/images/limePDF_logo.png';

// Solution 1: Check if LimePDF has image processing issues
class DebugPDF extends PDF {
    public function Image($file, $x, $y, $w=0, $h=0, $type='', $link='') {
        echo "=== PDF Image Debug ===" . PHP_EOL;
        echo "File: {$file}" . PHP_EOL;
        echo "Position: x={$x}, y={$y}" . PHP_EOL;
        echo "Size: w={$w}, h={$h}" . PHP_EOL;
        echo "Type: {$type}" . PHP_EOL;
        
        // Check internal image array
        if (isset($this->images)) {
            echo "Current images in PDF: " . count($this->images) . PHP_EOL;
            echo "Image keys: " . implode(', ', array_keys($this->images)) . PHP_EOL;
        }
        
        try {
            $result = parent::Image($file, $x, $y, $w, $h, $type, $link);
            echo "Parent Image() returned: " . var_export($result, true) . PHP_EOL;
            
            // Check if image was added to internal array
            if (isset($this->images)) {
                echo "Images after insertion: " . count($this->images) . PHP_EOL;
            }
            
            return $result;
        } catch (\Exception $e) {
            echo "Image insertion threw exception: " . $e->getMessage() . PHP_EOL;
            throw $e;
        }
    }
}

// Test with debug PDF
$debugPdf = new DebugPDF();
$debugPdf->AddPage();
$debugPdf->Image($imgHeaderPath, 20, 20, 80, 25);

// Solution 2: Try converting PNG to JPEG (more universally supported)
function convertPngToJpeg(string $pngPath): string {
    $jpegPath = str_replace('.png', '_converted.jpg', $pngPath);
    
    // Load PNG
    $image = imagecreatefrompng($pngPath);
    if (!$image) {
        throw new \RuntimeException("Could not load PNG: {$pngPath}");
    }
    
    // Create white background (PNG transparency to white)
    $width = imagesx($image);
    $height = imagesy($image);
    $jpegImage = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($jpegImage, 255, 255, 255);
    imagefill($jpegImage, 0, 0, $white);
    
    // Copy PNG onto white background
    imagecopy($jpegImage, $image, 0, 0, 0, 0, $width, $height);
    
    // Save as JPEG
    $success = imagejpeg($jpegImage, $jpegPath, 90);
    
    // Clean up memory
    imagedestroy($image);
    imagedestroy($jpegImage);
    
    if (!$success) {
        throw new \RuntimeException("Could not save JPEG: {$jpegPath}");
    }
    
    echo "Converted PNG to JPEG: {$jpegPath}" . PHP_EOL;
    return $jpegPath;
}

// Test with JPEG version
try {
    $jpegPath = convertPngToJpeg($imgHeaderPath);
    $debugPdf->AddPage();
    $debugPdf->Text(20, 10, 'Testing JPEG version:');
    $debugPdf->Image($jpegPath, 20, 20, 80, 25);
} catch (\Exception $e) {
    echo "JPEG conversion failed: " . $e->getMessage() . PHP_EOL;
}

// Solution 3: Check PDF output for image data
function analyzePdfContent(string $pdfContent): array {
    $analysis = [
        'has_images' => false,
        'image_count' => 0,
        'has_png' => false,
        'has_jpeg' => false,
        'xobjects' => 0,
        'size' => strlen($pdfContent)
    ];
    
    // Look for image indicators
    $patterns = [
        '/Type /XObject' => 'xobjects',
        '/Subtype /Image' => 'image_count', 
        '/Filter /DCTDecode' => 'has_jpeg',
        '/Filter /FlateDecode' => 'has_png',
        'PNG' => 'has_png'
    ];
    
    foreach ($patterns as $pattern => $key) {
        $matches = preg_match_all('/' . preg_quote($pattern, '/') . '/', $pdfContent);
        if ($matches > 0) {
            if (is_bool($analysis[$key])) {
                $analysis[$key] = true;
            } else {
                $analysis[$key] = $matches;
            }
        }
    }
    
    $analysis['has_images'] = $analysis['image_count'] > 0;
    
    return $analysis;
}

// Analyze the PDF content
$pdfOutput = $debugPdf->Output('S');
$analysis = analyzePdfContent($pdfOutput);
echo "PDF Analysis: " . print_r($analysis, true) . PHP_EOL;

// Solution 4: Try base64 embedding (last resort)
function embedImageAsBase64($pdf, string $imagePath, float $x, float $y, float $w, float $h): void {
    $imageData = base64_encode(file_get_contents($imagePath));
    $imageInfo = getimagesize($imagePath);
    
    // This is a workaround - create a data URL
    $mimeType = $imageInfo['mime'];
    $dataUrl = "data:{$mimeType};base64,{$imageData}";
    
    echo "Trying base64 embedding (size: " . strlen($imageData) . " chars)" . PHP_EOL;
    
    try {
        // Some PDF libraries can handle data URLs
        $pdf->Image($dataUrl, $x, $y, $w, $h);
        echo "Base64 embedding successful" . PHP_EOL;
    } catch (\Exception $e) {
        echo "Base64 embedding failed: " . $e->getMessage() . PHP_EOL;
    }
}

// Test base64 method
$debugPdf->AddPage();
$debugPdf->Text(20, 10, 'Testing Base64 embedding:');
embedImageAsBase64($debugPdf, $imgHeaderPath, 20, 20, 80, 25);

// Solution 5: Manual image processing check
function checkImageProcessingCapability(): array {
    $capabilities = [
        'gd_extension' => extension_loaded('gd'),
        'imagick_extension' => extension_loaded('imagick'),
        'png_support' => function_exists('imagecreatefrompng'),
        'jpeg_support' => function_exists('imagecreatefromjpeg'),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time')
    ];
    
    if ($capabilities['gd_extension']) {
        $gdInfo = gd_info();
        $capabilities['gd_version'] = $gdInfo['GD Version'] ?? 'Unknown';
        $capabilities['png_read'] = $gdInfo['PNG Support'] ?? false;
        $capabilities['jpeg_read'] = $gdInfo['JPEG Support'] ?? false;
    }
    
    return $capabilities;
}

$capabilities = checkImageProcessingCapability();
echo "System capabilities: " . print_r($capabilities, true) . PHP_EOL;

// Solution 6: Final test with minimal PDF
$testPdf = new PDF();
$testPdf->AddPage();
$testPdf->SetFont('Arial', 'B', 12);

// Add a lot of visible content to ensure PDF is working
$testPdf->Text(10, 10, 'PDF Test - Image should appear below');
$testPdf->Rect(20, 20, 80, 30); // Visible border
$testPdf->Text(10, 60, 'Image border above, image below:');

// Try the simplest possible image insertion
$testPdf->Image($imgHeaderPath, 20, 20);

$testPdf->Text(10, 80, 'If you see this text, PDF is working');
$testPdf->Output('image_test.pdf', 'D'); // Force download
?>