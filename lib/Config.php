<?php


namespace QPlayer2;


use QPlayer\Cache\Cache;

class Config
{
    const verJQ = '3.5.1';
    const verMarquee = '1.5.0';

    private $config;

    public function __construct()
    {
        global $zbp;
        $this->config = $zbp->HasConfig('QPlayer2') ? $zbp->Config('QPlayer2') : $this->getDefault();
    }

    private function getDefault()
    {
        $names = array('cdn', 'jQuery', 'isRotate', 'isShuffle');
        $r = array(
            'bitrate' => '320',
            'color' => '#EE1122',
            'list' => <<<JSON
[{
    "name": "Nightglow",
    "artist": "蔡健雅",
    "audio": "https://cdn.jsdelivr.net/gh/moeshin/QPlayer-res/Nightglow.mp3",
    "cover": "https://cdn.jsdelivr.net/gh/moeshin/QPlayer-res/Nightglow.jpg",
    "lrc": "https://cdn.jsdelivr.net/gh/moeshin/QPlayer-res/Nightglow.lrc"
},
{
    "name": "やわらかな光",
    "artist": "やまだ豊",
    "audio": "https://cdn.jsdelivr.net/gh/moeshin/QPlayer-res/やわらかな光.mp3",
    "cover": "https://cdn.jsdelivr.net/gh/moeshin/QPlayer-res/やわらかな光.jpg"
}]
JSON,
            'cacheType' => 'none'
        );
        foreach ($names as $name) {
            $r[$name] = true;
        }
        return (object) $r;
    }

    public function get($key)
    {
        return @$this->config->$key;
    }

    public function bool($key)
    {
        return $this->get($key) ? 'true' : 'false';
    }

    public function text($key)
    {
        $str = $this->get($key);
        if ($str === null) {
            return null;
        }
        return htmlspecialchars_decode($str, ENT_QUOTES);
    }

    public function submit()
    {
        global $zbp;
        $input = GetVars('QPlayer2');

        // Handle Cache
        require_once 'cache/Cache.php';
        $cacheTypeNow = $input['cacheType'];
        $cacheArgs = array(
            $cacheTypeNow,
            $input['cacheHost'],
            $input['cachePort']
        );
        $cacheTypeLast = $this->get('cacheType');
        if (!$cacheTypeLast) {
            $cacheTypeLast = 'none';
        }
        $cacheBuild = array('QPlayer\Cache\Cache', 'Build');
        $isNotNoneNow = $cacheTypeNow != 'none';
        if ($cacheTypeNow != $cacheTypeLast) {
            if ($isNotNoneNow) {
                $cache = call_user_func_array($cacheBuild, $cacheArgs);
                $cache->install();
                $cache->test();
            }
            if ($cacheTypeLast != 'none') {
                Cache::BuildWithConfig($this)->uninstall();
            }
        } elseif (
            $isNotNoneNow &&
            $cacheTypeNow != 'database' &&
            $this->compareCacheConfig($input)
        ) {
            $cache = call_user_func_array($cacheBuild, $cacheArgs);
            $cache->test();
        }

        $config = $zbp->Config('QPlayer2');

        // Checkbox
        $keys = array(
            'cdn',
            'jQuery',
            'isRotate',
            'isShuffle',
        );
        foreach ($keys as $key) {
            $config->$key = isset($input[$key]);
        }

        // Radio
        $keys = array(
            'bitrate',
            'cacheType',
        );
        foreach ($keys as $key) {
            $config->$key = $input[$key];
        }

        // Text
        $keys = array(
            'color',
            'cacheHost',
            'cachePort',
            'list',
            'cookie'
        );
        foreach ($keys as $key) {
            $config->$key = htmlspecialchars($input[$key], ENT_QUOTES, 'UTF-8');
        }

        $zbp->SaveConfig('QPlayer2');
        $zbp->SetHint('good');
        Redirect('./main.php');
    }

    private function compareCacheConfig($now) {
        $keys = array('cacheHost', 'cachePort');
        $length = count($keys);
        $config = $this->config;
        for ($i = 0; $i < $length; ++$i) {
            $key = $keys[$i];
            if ($now[$key] != $config->$key) {
                return true;
            }
        }
        return false;
    }
}