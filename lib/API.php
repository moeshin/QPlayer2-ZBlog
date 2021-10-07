<?php


namespace QPlayer2;

use Exception;
use Metowolf\Meting;
use QPlayer\Cache\Cache;

require_once 'Config.php';
require_once 'cache/Cache.php';

class API
{
    private $config;

    /**
     * API constructor.
     * @param string $do
     * @throws Exception
     */
    public function __construct($do)
    {
        $this->config = new Config();
        if ($do == 'flush') {
            try {
                if ($this->config->text('cacheType') == 'none') {
                    echo '没有配置缓存！';
                } else {
                    Cache::BuildWithConfig($this->config)->flush();
                    echo '操作成功！';
                }
                echo '5 秒后自动关闭！';
                echo '<script>setTimeout(window.close, 5000);</script>';
            } catch (Exception $e) {
                echo '<p>操作失败！</p>';
                echo '<p>' . $e->getMessage() . '</p>';
                echo '<pre>';
                echo $e->getTraceAsString();
                echo '</pre>';
            }
            return;
        }

        $server = GetVars('server');
        $type = GetVars('type');
        $id = GetVars('id');

        if (!$this->test($server, $type, $id)) {
            http_response_code(403);
            die();
        }

        require_once 'Meting.php';
        $m = new Meting($server);
        $m->format(true);
        $cookie = $this->config->text('cookie');
        if ($server == 'netease' && !empty($cookie)) {
            $m->cookie($cookie);
        }
        $cache = $this->config->text('cacheType') == 'none' ? null : Cache::BuildWithConfig($this->config);
        $key = $server . $type . $id;
        if ($cache != null) {
            $data = $cache->get($key);
        }
        if (empty($data)) {
            $arg2 = null;
            $expire = 7200;
            switch ($type) {
                case 'audio':
                    $type = 'url';
                    $arg2 = $this->config->get('bitrate');
                    $expire = 1200;
                    break;
                case 'cover':
                    $type = 'pic';
                    $arg2 = 300;
                    $expire = 86400;
                    break;
                case 'lrc':
                    $type = 'lyric';
                    $expire = 86400;
                    break;
                case 'artist':
                    $arg2 = 50;
                    break;
            }
            $data = $m->$type($id, $arg2);
            $data = json_decode($data, true);
            switch ($type) {
                case 'url':
                case 'pic':
                    $url = $data['url'];
                    if (empty($url)) {
                        if ($server != 'netease') {
                            http_response_code(403);
                            die();
                        }
                        $url = 'https://music.163.com/song/media/outer/url?id=' . $id . '.mp3';
                    } else {
                        $url = preg_replace('/^http:/', 'https:', $url);
                    }
                    $data = $url;
                    break;
                case 'lyric':
                    $data = $data['lyric'] . "\n" . $data['tlyric'];
                    break;
                default:
                    $url = plugin_dir_url(dirname(__FILE__)) . 'api.php';
                    $array = array();
                    foreach ($data as $v) {
                        $prefix = $url . '?server=' . $v['source'];
                        $array [] = array(
                            'name' => $v['name'],
                            'artist' => implode(' / ', $v['artist']),
                            'audio' => $prefix . '&type=audio&id=' . $v['url_id'],
                            'cover' => $prefix . '&type=cover&id=' . $v['pic_id'],
                            'lrc' => $prefix . '&type=lrc&id=' . $v['lyric_id'],
                            'provider' => 'default'
                        );
                    }
                    $data = json_encode($array);
            }
            if ($cache != null) {
                $cache->set($key, $data, $expire);
            }
        }
        switch ($type) {
            case 'url':
            case 'pic':
            case 'audio':
            case 'cover':
                Redirect($data);
                exit;
            case 'lrc':
            case 'lyric':
                header("Content-Type: text/plain");
                break;
            default:
                header("Content-Type: application/json");
                break;
        }
        echo $data;
    }

    private function test($server, $type, $id)
    {
        if (!in_array($server, array('netease', 'tencent', 'baidu', 'xiami', 'kugou'))) {
            return false;
        }
        if (!in_array($type, array('audio', 'cover', 'lrc', 'song', 'album', 'artist', 'playlist'))) {
            return false;
        }
        if (empty($id)) {
            return false;
        }
        return true;
    }
}