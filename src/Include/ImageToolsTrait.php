<?php
namespace LimePDF\Include;

trait ImageToolsTrait
{
       /**
     * Detects the image file type and fills $imsize with getimagesize() data.
     *
     * @param string $file   Path to image file
     * @param array  $imsize Passed by reference, filled with getimagesize() result
     * @return string|false  Returns type string (jpeg, png, gif, bmp) or false on failure
     */
    protected function getImageFileType(string $file, ?array &$imsize = null)
    {
        // Always try to get dimensions
        $imsize = @getimagesize($file);
        if (!$imsize) {
            return false;
        }

        // Prefer PHP’s IMAGETYPE_* constants
        switch ($imsize[2]) {
            case IMAGETYPE_JPEG:
                return 'jpeg';
            case IMAGETYPE_PNG:
                return 'png';
            case IMAGETYPE_GIF:
                return 'gif';
            case IMAGETYPE_BMP:
                return 'bmp';
        }

        // Fallback: mime_content_type if available
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($file);
            if ($mime) {
                switch ($mime) {
                    case 'image/jpeg':
                        return 'jpeg';
                    case 'image/png':
                        return 'png';
                    case 'image/gif':
                        return 'gif';
                    case 'image/bmp':
                        return 'bmp';
                }
            }
        }

        return false;
    }
    // /**
    //  * Detect the type of an image file.
    //  *
    //  * @param string $file   Path to the image file
    //  * @param array  $imsize Optional result from getimagesize() to avoid recalculating
    //  * @return string One of: jpeg, png, gif, bmp, or '' if unknown
    //  */
    // protected function getImageFileType(string $file, array $imsize = []): string
    // {
    //     // 1. Try mime_content_type if available
    //     if (function_exists('mime_content_type')) {
    //         $mime = @mime_content_type($file);
    //         if ($mime) {
    //             switch ($mime) {
    //                 case 'image/jpeg':
    //                     return 'jpeg';
    //                 case 'image/png':
    //                     return 'png';
    //                 case 'image/gif':
    //                     return 'gif';
    //                 case 'image/bmp':
    //                     return 'bmp';
    //             }
    //         }
    //     }

    //     // 2. Fallback: use getimagesize()
    //     if (empty($imsize)) {
    //         $imsize = @getimagesize($file);
    //     }
    //     if (!empty($imsize[2])) {
    //         switch ($imsize[2]) {
    //             case IMAGETYPE_JPEG:
    //                 return 'jpeg';
    //             case IMAGETYPE_PNG:
    //                 return 'png';
    //             case IMAGETYPE_GIF:
    //                 return 'gif';
    //             case IMAGETYPE_BMP:
    //                 return 'bmp';
    //         }
    //     }

    //     // 3. Last fallback: check file extension
    //     $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    //     switch ($ext) {
    //         case 'jpg':
    //         case 'jpeg':
    //             return 'jpeg';
    //         case 'png':
    //             return 'png';
    //         case 'gif':
    //             return 'gif';
    //         case 'bmp':
    //             return 'bmp';
    //     }

    //     // Unknown file type
    //     return '';
    // }
}
