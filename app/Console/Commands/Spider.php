<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Tools\MultipleUrlContextLoader;
use App\Image\Image;
use App\Wfc\Map;

class Spider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test';

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
        $m = new Map(3, 3);
        var_dump($m);
    }

    private function getInputImage(): Image
    {
        $m = new Image();
        $m->empty(3, 3);
        $m->getPixel(0, 0)->setRGBA(255, 0, 0, 0);
        $m->getPixel(0, 1)->setRGBA(0, 255, 0, 0);
        $m->getPixel(0, 2)->setRGBA(0, 0, 255, 0);
        $m->getPixel(1, 0)->setRGBA(0, 0, 255, 0);
        $m->getPixel(1, 1)->setRGBA(255, 0, 0, 0);
        $m->getPixel(1, 2)->setRGBA(0, 255, 0, 0);
        return $m;
    }
}
