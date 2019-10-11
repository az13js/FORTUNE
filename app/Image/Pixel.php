<?php
namespace App\Image;

class Pixel
{
    private $rgba = [0, 0, 0, 0];

    public function setRGBA(int $r, int $g, int $b, int $a): bool
    {
        if ($r > 255 || $r < 0 || $g > 255 || $g < 0 || $b > 255 || $b < 0 || $a > 127 || $a < 0) {
            return false;
        }
        $this->rgba = [$r, $g, $b, $a];
        return true;
    }

    public function getRGBA(): array
    {
        return $this->rgba;
    }

    public function equal(Pixel $pix): bool
    {
        return serialize($this) == serialize($pix);
    }
}
