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
        try {
            $d = $this->getAll($this->curlGet('http://tieba.baidu.com/f/index/forumclass'));
            foreach ($d as $k) {
                for ($i = 1; true; $i++) {
                    $f = $this->curlGet('http://tieba.baidu.com/f/index/forumpark?cn='.urlencode($k[1]).'&ci=0&pcn='.urlencode($k[0]).'&pci=0&ct=1&st=new&pn='.$i);
                    $f = $this->tiebaList($f);
                    if (empty($f)) {
                        break;
                    }
                    foreach ($f as $g) {
                        echo $g[1] . PHP_EOL;
                        $time = date('Y-m-d H:i:s');
                        file_put_contents('public/storage/tieba-data.csv', "\"$time\",\"{$k[0]}\",\"{$k[1]}\",\"{$g[0]}\",\"{$g[1]}\"" . PHP_EOL, FILE_APPEND);
                    }
                }
                
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function tiebaList($str)
    {
        $results = [];
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($str);
        $list = $dom->getElementsByTagName('a');
        for ($i = 0; $i < $list->length; $i++) {
            if (is_null($list->item($i)->attributes)) {
                continue;
            }
            $result = [];
            parse_str(parse_url($list->item($i)->attributes->getNamedItem('href')->nodeValue, PHP_URL_QUERY), $result);
            if (isset($result['kw'])) {
                if (isset($results[urlencode($result['kw'])])) {
                    continue;
                }
                $results[urlencode($result['kw'])] = [$result['kw'], 'http://tieba.baidu.com/f?kw='.urlencode($result['kw'])];
            }
        }
        return array_values($results);
    }

    public function getAll($str)
    {
        $results = [];
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($str);
        $list = $dom->getElementsByTagName('a');
        for ($i = 0; $i < $list->length; $i++) {
            if (is_null($list->item($i)->attributes)) {
                continue;
            }
            $result = [];
            parse_str(parse_url($list->item($i)->attributes->getNamedItem('href')->nodeValue, PHP_URL_QUERY), $result);
            if (isset($result['cn'])) {
                $results[] = [$result['pcn'], $result['cn'], 'http://tieba.baidu.com/f/index/forumpark?cn='.urlencode($result['cn']).'&ci=0&pcn='.urlencode($result['pcn']).'&pci=0&ct=1'];
            }
        }
        return $results;
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
            curl_setopt($h, CURLOPT_REFERER, 'http://tieba.baidu.com');
            curl_setopt($h, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
            curl_setopt($h, CURLOPT_HTTPHEADER, ['Content-type: text/html; charset=UTF-8']);
            //curl_setopt($h, CURLOPT_COOKIE, 'TIEBA_USERTYPE=902f5df6fcb32456243f4253; bdshare_firstime=1540112903703; BDUSS=jB5N1dtZ3BQSnh4S2R6NWtHSGY3OWdRUTkyRVJMdkFzb0k3cEFzbmRKdGtWWWRjQVFBQUFBJCQAAAAAAAAAAAEAAAC0xQE6d1ptWjJxWWxSc2RYMFIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAGTIX1xkyF9ca; TIEBAUID=35f770fc23495e2a322108f1; PSTM=1552206572; BAIDUID=11BCED6C3A2D242E1BD506EB5FE65F9F:FG=1; BIDUPSID=7E9B130D420E8212A57167E47E22DB83; pgv_pvi=2835934208; STOKEN=fc7e8b2ad63188fd11244e7ea15cab90176230054e2e0022b05499a63a56db4e; Hm_lvt_98b9d8c2fd6608d564bf2ac2ae642948=1552737320,1552828878,1554615565,1554729915; Hm_lpvt_98b9d8c2fd6608d564bf2ac2ae642948=1554740546');
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
