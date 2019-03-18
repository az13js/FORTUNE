<?php
namespace App\Image;

class Helper
{
    /**
     * Load input array from a image file.
     *
     * @param string      $file        The image, jpg or png.
     * @param int         $resetWidth  Reset the image data to this width.
     * @param int         $resetHeight Reset the image data to this height.
     * @param bool        $rgb         Use RGB, default is true, yes.
     * @return array                   If read success return array, else return empty array.
     */
    public static function arrayFromImage(string $file, int $resetWidth, int $resetHeight, bool $rgb = true): array
    {
        switch (exif_imagetype($file)) {
            case IMAGETYPE_JPEG:
                $gdResource = imagecreatefromjpeg($file);
                break;
            case IMAGETYPE_PNG:
                $gdResource = imagecreatefrompng($file);
                break;
            default:
                $gdResource = false;
        }
        if (!$gdResource) {
            return [];
        }
        $width = imagesx($gdResource);
        $height = imagesy($gdResource);
        if ($resetWidth != $width || $resetHeight != $height) {
            $tempResource = imagecreatetruecolor($resetWidth, $resetHeight);
            imagecopyresampled($tempResource, $gdResource, 0, 0, 0, 0, $resetWidth, $resetHeight, $width, $height);
            imagedestroy($gdResource);
            $gdResource = $tempResource;
            unset($tempResource);
            $width = $resetWidth;
            $height = $resetHeight;
        }
        $result = [];
        for ($h = 0; $h < $height; $h++) {
            for ($w = 0; $w < $width; $w++) {
                $colorIndex = imagecolorat($gdResource, $w, $h);
                $rgba = imagecolorsforindex($gdResource, $colorIndex);
                if ($rgb) {
                    $result[] = $rgba['red'] / 255 - 0.5;
                    $result[] = $rgba['green'] / 255 - 0.5;
                    $result[] = $rgba['blue'] / 255 - 0.5;
                } else {
                    $result[] = ($rgba['red'] + $rgba['green'] + $rgba['blue']) / 3 / 255 - 0.5;
                }
            }
        }
        imagedestroy($gdResource);
        return $result;
    }

    /**
     * Recover a array to image file.
     *
     * @param array $data
     * @param string $file
     * @param int $width
     * @param int $height
     * @param bool $rgb
     * @return bool
     */
    public static function arrayToImage(array $data, string $file, int $width, int $height, bool $rgb = true): bool
    {
        if ($rgb && count($data, COUNT_NORMAL) / 3 != $width * $height) {
            return false;
        }
        if (!$rgb && count($data, COUNT_NORMAL) != $width * $height) {
            return false;
        }
        $gdResource = imagecreatetruecolor($width, $height);
        for ($h = 0; $h < $height; $h++) {
            for ($w = 0; $w < $width; $w++) {
                if ($rgb) {
                    $offset =($width * $h + $w) * 3;
                    $color = imagecolorallocate($gdResource, 255 * ($data[$offset] + 0.5), 255 * ($data[$offset + 1] + 0.5), 255 * ($data[$offset + 2] + 0.5));
                } else {
                    $offset =$width * $h + $w;
                    $color = imagecolorallocate($gdResource, 255 * ($data[$offset] + 0.5), 255 * ($data[$offset] + 0.5), 255 * ($data[$offset] + 0.5));
                }
                imagesetpixel($gdResource, $w, $h, $color);
                imagecolordeallocate($gdResource, $color);
            }
        }
        switch (mb_strtoupper(pathinfo($file, PATHINFO_EXTENSION))) {
            case 'PNG':
                $result = imagepng($gdResource, $file);
                break;
            case 'JPEG':
            case 'JPG':
                $result = imagejpeg($gdResource, $file);
                break;
            default:
                $result = imagepng($gdResource, $file);
        }
        imagedestroy($gdResource);
        return $result;
    }

    /**
     * Recover a array to image file.
     *
     * @param array $data
     * @param string $file
     * @param int $width
     * @param int $height
     * @param bool $rgb
     * @return bool
     */
    public static function intelligenceArrayToImage(array $data, string $file, int $width, int $height, bool $rgb = true): bool
    {
        $min = min($data);
        $max = max($data);
        $mapData = array_map(function ($element) use ($min, $max) {
            $rall = $max - $min;
            $rall = $rall <= 0 ? 1 : $rall;
            return ($element - $min) / ($rall) - 0.5;
        }, $data);
        return self::arrayToImage($mapData, $file, $width, $height, $rgb);
    }
}
