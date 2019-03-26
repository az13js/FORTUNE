<?php

namespace App\Console\Commands;

use App\Image\Image;
use Illuminate\Console\Command;

class AppDebug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appdebug';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->createTrainData();
        $img1 = new Image();
        var_dump($img1->loadFromJpeg('vendor/test.jpg', 200, 200));
        $img1->saveAsPng('vendor/test1.png', 1000, 1000);
        var_dump(memory_get_peak_usage() / 1024 / 1024);
    }

    private function createTrainData()
    {
        $img = imagecreatetruecolor(500, 500);
        imageantialias($img, true);
        $white = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, 500-1, 500-1, $white);
        for ($i = 0; $i < 100; $i++) {
            $this->drawChar($img, range('A', 'Z')[mt_rand(0, 25)], mt_rand(0, 500-1-8), mt_rand(0, 500-1-10));
        }
        imagepng($img, 'test2.png');
        imagedestroy($img);
    }

    private function drawChar($img, $ch, $x, $y, $r = 0, $g = 0, $b = 0)
    {
        $color = imagecolorallocate($img, $r, $g, $b);
        /* width:8 height:10 */
        imagechar($img, 5, $x, $y - 3, $ch, $color);
        imagecolordeallocate($img, $color);
    }
}
