<?php
namespace App\Wfc;

use App\Image\Image;

class Map
{
    private $unknowDatas = [];

    private $firstY = 0;
    private $firstX = 0;

    public function __construct(int $width, int $height, Image $input)
    {
        for ($i = 0; $i < $height; $i++) {
            $this->unknowDatas[$i] = [];
            for ($j = 0; $j < $width; $j++) {
                $this->unknowDatas[$i][$j] = new WaveFunction();
            }
        }
        $this->firstY = intval($height / 2);
        $this->firstX = intval($width / 2);
    }
}
