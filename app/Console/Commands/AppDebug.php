<?php

namespace App\Console\Commands;

use App\Image\Helper;
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
        $arr = Helper::arrayFromImage('a.png', 80, 60, false);
        echo date('Y-m-d H:i:s') . PHP_EOL;
        Helper::arrayToImage($arr, 'b.png', 80, 60, false);
        Helper::intelligenceArrayToImage($arr, 'c.png', 80, 60, false);
    }

    /**
     * train and save a network
     *
     * @return void
     */
    private function createNetFile()
    {
        $train_data = $this->createTrainData(100);
        $network = fann_create_standard(4, 64 * 64, 700, 500, 2);
        fann_set_activation_function_hidden($network, FANN_SIGMOID_SYMMETRIC);
        fann_set_activation_function_output($network, FANN_SIGMOID);
        fann_set_training_algorithm($network, FANN_TRAIN_RPROP);
        fann_set_train_error_function($network, FANN_ERRORFUNC_LINEAR);
        fann_set_train_stop_function($network, FANN_STOPFUNC_MSE);
        fann_set_callback($network, [$this, 'fannTrainCallback']);
        fann_train_on_data($network, $train_data, 1000, 2, 0);
        fann_save($network, 'vendor/network.dat');
    }

    /**
     * create a train dataset
     *
     * @param int $number data pair number
     * @return resource a fann dataset resource(FANN Train Data)
     */
    private function createTrainData($number)
    {
        return fann_create_train_from_callback($number, 64 * 64, 2, [$this, 'createOneData']);
    }

    /**
     * create a array with keys input and output.
     * size of input is 64x64, size of output is 2
     *
     * params just for callback.
     *
     * @param int $num
     * @param int $num_input
     * @param int $num_output
     * @return array
     */
    private function createOneData($num, $num_input, $num_output)
    {
        $img = imagecreatetruecolor(64, 64);
        $background = imagecolorallocate($img, 255, 255, 255);
        imagefilledrectangle($img, 0, 0, 63, 63, $background);
        imagecolordeallocate($img, $background);
        $x = mt_rand(0, 63 - 8);
        $y = mt_rand(0, 63 - 10);
        $this->drawChar($img, range('A', 'Z')[mt_rand(0, 25)], $x, $y);
        $result = ['input' => [], 'output' => [($x + 4) / 64 - 0.5, ($y + 5) / 64 - 0.5]];
        for ($j = 0; $j < 64; $j++) {
            for ($i = 0; $i < 64; $i++) {
                $colorIndex = imagecolorat($img, $i, $j);
                $rgba = imagecolorsforindex($img, $colorIndex);
                $result['input'][] = ($rgba['red'] + $rgba['green'] + $rgba['blue']) / 3 / 255 - 0.5;
            }
        }
        imagedestroy($img);
        return $result;
    }

    /**
     * print a char on gd image
     *
     * @param resource $img
     * @param string $ch
     * @param int $x
     * @param int $y
     * @param int $r red color, default 0
     * @param int $g green color, default 0
     * @param int $b blue color, default 0
     * @return bool success return true, fail return false
     */
    private function drawChar($img, $ch, $x, $y, $r = 0, $g = 0, $b = 0)
    {
        $color = imagecolorallocate($img, $r, $g, $b);
        /* width:8 height:10 */
        $result = imagechar($img, 5, $x, $y - 3, $ch, $color);
        imagecolordeallocate($img, $color);
        return $result;
    }

    /**
     * just for fann train callback
     *
     * @return bool always return true
     */
    private function fannTrainCallback($ann, $train, $max_epochs, $epochs_between_reports, $desired_error, $epochs)
    {
        $test_data = $this->createTrainData(10);
        echo round($epochs / $max_epochs * 100);
        echo '% ';
        echo fann_get_MSE($ann);
        echo ' ';
        echo fann_test_data($ann, $test_data);
        echo PHP_EOL;
        fann_destroy_train($test_data);
        return true;
    }

    /**
     * @param string $file
     * @param array $inputArray
     * @param array $outputArray
     * @param array $networkInput
     */
    private function buildImageFrom($file, $inputArray, $outputArray, $networkInput)
    {
        /* build image from $inputArray */
        $img = imagecreatetruecolor(64, 64);
        foreach ($inputArray as $key => $pix) {
            $x = $key % 64;
            $y = intval($key / 64);
            if ($y > 63) {
                break;
            }
            $level = intval(($pix + 0.5) * 255);
            $level = $level < 0 ? 0 : $level;
            $level = $level > 255 ? 255 : $level;
            $color = imagecolorallocate($img, $level, $level, $level);
            imagesetpixel($img, $x, $y, $color);
            imagecolordeallocate($img, $color);
        }
        imagefilledellipse();
    }
}
