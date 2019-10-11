<?php
namespace App\Image;

class Image
{
    /*
          x------------>
         y|
          |
          |
          |
          V
     */
    private $pixMatrix = [];

    public function saveAsPng(string $file, int $rWidth = -1, int $rHeight = -1): bool
    {
        if (empty($this->pixMatrix)) {
            return false;
        }
        $width = count($this->pixMatrix[0], COUNT_NORMAL);
        $height = count($this->pixMatrix, COUNT_NORMAL);
        $gd = imagecreatetruecolor($width, $height);
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                list($r, $g, $b, $a) = $this->pixMatrix[$y][$x]->getRGBA();
                $color = imagecolorallocatealpha($gd, $r, $g, $b, $a);
                imagesetpixel($gd, $x, $y, $color);
                imagecolordeallocate($gd, $color);
            }
        }
        if ($rWidth > 0 && $rHeight > 0) {
            $temp = imagecreatetruecolor($rWidth, $rHeight);
            imagecopyresampled($temp, $gd, 0, 0, 0, 0, $rWidth, $rHeight, $width, $height);
            imagedestroy($gd);
            $gd = $temp;
            unset($temp);
        }
        $result = imagepng($gd, $file);
        imagedestroy($gd);
        return $result;
    }

    public function loadFromJpeg(string $jpegFile, int $width = -1, int $height = -1): bool
    {
        if (!is_file($jpegFile)) {
            return false;
        }
        if (filesize($jpegFile) < 12) {
            return false;
        }
        $imageType = exif_imagetype($jpegFile);
        if ($imageType !== IMAGETYPE_JPEG) {
            return false;
        }
        $gd = imagecreatefromjpeg($jpegFile);
        $x = imagesx($gd);
        $y = imagesy($gd);
        if ($width > 0 && $height > 0) {
            $temp = imagecreatetruecolor($width, $height);
            imagecopyresampled($temp, $gd, 0, 0, 0, 0, $width, $height, $x, $y);
            imagedestroy($gd);
            $gd = $temp;
            unset($temp);
            $x = $width;
            $y = $height;
        }
        for ($dy = 0; $dy < $y; $dy++) {
            if (!isset($this->pixMatrix[$dy])) {
                $this->pixMatrix[$dy] = [];
            }
            for ($dx = 0; $dx < $x; $dx++) {
                $colorIndex = imagecolorat($gd, $dx, $dy);
                $rgba = imagecolorsforindex($gd, $colorIndex);
                $this->pixMatrix[$dy][$dx] = new Pixel();
                $this->pixMatrix[$dy][$dx]->setRGBA($rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha']);
            }
        }
        imagedestroy($gd);
        return true;
    }

    public function loadFromPng(string $pngFile, int $width = -1, int $height = -1): bool
    {
        if (!is_file($pngFile)) {
            return false;
        }
        if (filesize($pngFile) < 12) {
            return false;
        }
        $imageType = exif_imagetype($pngFile);
        if ($imageType !== IMAGETYPE_PNG) {
            return false;
        }
        $gd = imagecreatefrompng($pngFile);
        $x = imagesx($gd);
        $y = imagesy($gd);
        if ($width > 0 && $height > 0) {
            $temp = imagecreatetruecolor($width, $height);
            imagecopyresampled($temp, $gd, 0, 0, 0, 0, $width, $height, $x, $y);
            imagedestroy($gd);
            $gd = $temp;
            unset($temp);
            $x = $width;
            $y = $height;
        }
        for ($dy = 0; $dy < $y; $dy++) {
            if (!isset($this->pixMatrix[$dy])) {
                $this->pixMatrix[$dy] = [];
            }
            for ($dx = 0; $dx < $x; $dx++) {
                $colorIndex = imagecolorat($gd, $dx, $dy);
                $rgba = imagecolorsforindex($gd, $colorIndex);
                $this->pixMatrix[$dy][$dx] = new Pixel();
                $this->pixMatrix[$dy][$dx]->setRGBA($rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha']);
            }
        }
        imagedestroy($gd);
        return true;
    }

    public function empty(int $width, int $height): bool
    {
        $this->pixMatrix = [];
        for ($dy = 0; $dy < $height; $dy++) {
            if (!isset($this->pixMatrix[$dy])) {
                $this->pixMatrix[$dy] = [];
            }
            for ($dx = 0; $dx < $width; $dx++) {
                $this->pixMatrix[$dy][$dx] = new Pixel();
                $this->pixMatrix[$dy][$dx]->setRGBA(0, 0, 0, 0);
            }
        }
        return true;
    }

    public function getPixel(int $x, int $y): Pixel
    {
        return $this->pixMatrix[$y][$x];
    }
}
