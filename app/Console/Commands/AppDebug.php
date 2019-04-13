<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    protected $description = '';

    private $curl = null;
    private $curlError = '';

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
        while (true) {
            $this->flushOnce();
        }
    }

    public function flushOnce()
    {
        for ($page = 1; $page < 100; $page++) {
            $newArtital = 0;
            $data = $this->getList($page);
            if (false == $data) {
                break;
            }
            foreach ($data['docs'] as $unit) {
                $first = DB::table('news')->where('news_key', $unit['id'])->first();
                if (is_null($first)) {
                    $contextFile = $this->getText($unit['url']);
                    if (!empty($this->curlError)) {
                        Log::error('[CURL]' . $this->curlError);
                        $this->curlError = '';
                        continue;
                    }
                    $result = DB::transaction(function () use ($unit, $contextFile) {
                        $id = DB::table('news')->insertGetId([
                            'news_key' => $unit['id'],
                            'title' => $unit['title'],
                            'url' => $unit['url'],
                            'public' => $unit['pubtime'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        return DB::table('context')->insert([
                            'news_id' => $id,
                            'context' => $contextFile,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    });
                    if (false == $result) {
                        Log::error('[SQL]transaction fail');
                    } else {
                        $newArtital++;
                    }
                }
            }
            if ($newArtital == 0) {
                Log::info('NO UPDATE, PAGE: '.$page.'.');
            } else {
                Log::info('UPDATE: ' . $newArtital . ', PAGE: ' . $page);
            }
        }
    }

    public function getText($url)
    {
        $context = $this->curlGet($url);
        if (empty($context)) {
            return '';
        }
        $fileName = hash('sha256', $context);
        if (!is_dir('public')) {
            mkdir('public');
        }
        if (!is_dir('public/storage')) {
            mkdir('public/storage');
        }
        file_put_contents('public/storage/' . $fileName . '.html', $context);
        return $fileName;
    }

    public function getList(int $pager = 1, int $pagenum = 8)
    {
        $result = $this->curlGet("http://channel.chinanews.com/cns/cjs/fortune.shtml?pager=$pager&pagenum=$pagenum&_=" . (1000 * time()));
        if (empty($this->curlError)) {
            $result = ltrim($result, 'specialcnsdata = ');
            $result = mb_substr($result, 0, 1 + mb_strrpos($result, '}'));
            $data = json_decode($result, true);
            if (is_null($data)) {
                Log::error('[JSON_DECODE]' . serialize($result));
            }
            return $data;
        }
        Log::error('[CURL]' . $this->curlError);
        $this->curlError = '';
        return false;
    }

    public function curlGet($url)
    {
        if (is_null($this->curl)) {
            $h = curl_init();
            curl_setopt($h, CURLOPT_AUTOREFERER, true);
            curl_setopt($h, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($h, CURLOPT_FORBID_REUSE, false);
            curl_setopt($h, CURLOPT_FRESH_CONNECT, false);
            curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($h, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($h, CURLOPT_DNS_CACHE_TIMEOUT, 60 * 5);
            curl_setopt($h, CURLOPT_MAXCONNECTS, 10);
            curl_setopt($h, CURLOPT_MAXREDIRS, 20);
            curl_setopt($h, CURLOPT_TIMEOUT, 60 * 2);
            curl_setopt($h, CURLOPT_REFERER, 'http://fortune.chinanews.com/');
            curl_setopt($h, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
            curl_setopt($h, CURLOPT_HTTPHEADER, ['Content-type: text/html; charset=UTF-8']);
            $this->curl = $h;
        } else {
            $h = $this->curl;
        }
        curl_setopt($h, CURLOPT_URL, $url);
        $data = curl_exec($h);
        if (false === $data) {
            $this->curlError = curl_error($h);
        }
        return $data;
    }
}
