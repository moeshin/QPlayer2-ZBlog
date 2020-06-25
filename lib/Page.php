<?php


namespace QPlayer2;

require_once 'Config.php';

class Page
{
    private $config;

    public function __construct()
    {
        $this->config = new Config();
        $this->checkbox(
            '常规',
            array(
                'cdn' => '使用 jsDelivr CDN 免费加速 js、css 文件',
                'jQuery' => '加载 jQuery。若冲突，请关闭',
                'isRotate' => '旋转封面',
                'isShuffle' => '随机播放'
            )
        );
        $this->text(
            'color',
            '主题颜色',
            '默认：<span style="color: #EE1122">#EE1122</span>'
        );
        $this->radio(
            'bitrate',
            '默认音质',
            array(
                '128' => '流畅品质 128K',
                '192' => '清晰品质 192K',
                '320' => '高品质 320K'
            )
        );
        $this->textarea(
            'list',
            '歌曲别表',
            <<<HTML
<a target="_blank" href="https://www.json.cn/">JSON 格式</a> 的数组，具体属性请看 
<a target="_blank" href="https://github.com/moeshin/QPlayer2#list-item">这里</a><br>
您也可以添加，例如：私人雷达<br>
<code>{"server": "netease", "type": "playlist", "id": "3136952023"}</code><br>
来引入第三方资源，此功能基于 <a href="https://github.com/metowolf/Meting">Meting</a><br>
<code>server</code>：netease、tencent、baidu、xiami、kugou<br>
<code>type</code>：playlist、song、album、artist<br>
（附：<a target="_blank" href="https://github.com/moeshin/netease-music-dynamic-playlist">网易云动态歌单整理</a>）
HTML
        );
        $this->textarea(
            'cookie',
            '网易云音乐 Cookie',
            <<<HTML
如果您是网易云音乐的会员或者使用私人雷达等动态歌单，可以将您的 cookie 的 <code>MUSIC_U</code>
填入此处来获取云盘等付费资源，听歌将不会计入下载次数<br>
<strong>如果不知道这是什么意思，忽略即可</strong>
HTML
        );
        $this->radio(
            'cacheType',
            '缓存类型',
            array(
                'none' => '无',
                'database' => '数据库',
                'memcached' => 'Memcached',
                'redis' => 'Redis'
            )
        );
        $this->text(
            'cacheHost',
            '缓存地址',
            '若使用数据库缓存，请忽略此项。默认：127.0.0.1'
        );
        $this->text(
            'cachePort',
            '缓存端口',
            '若使用数据库缓存，请忽略此项。默认，Memcached：11211；Redis：6379'
        );
    }

    private function table($title, $callback)
    {
        echo '<tr><th scope="row">' . $title . '</th><td>';
        $callback();
        echo '</td></tr>';
    }

    private function text($id, $title, $description)
    {
        $value = $this->config->text($id);
        $this->table($title, function () use ($id, &$value, &$description) {
            echo '<input name="QPlayer2[';
            echo $id;
            echo ']" type="text"  value="';
            echo $value;
            echo '">';
            echo '<p>';
            echo $description;
            echo '</p>';
        });
    }

    private function textarea($id, $title, $description)
    {
        $value = $this->config->text($id);
        $this->table($title, function () use ($id, &$value, &$description) {
            echo '<textarea name="QPlayer2[' . $id . ']">';
            echo $value;
            echo '</textarea>';
            echo '<p>';
            echo $description;
            echo '</p>';
        });
    }

    private function radio($id, $title, $options)
    {
        $option = $this->config->get($id);
        $this->table($title, function () use ($id, $option, &$options) {
            foreach ($options as $value => $text) {
                $checked = $option == $value ? 'checked' : '';
                $cid = "$id-$value";
                echo <<<HTML
<p>
    <label for="$cid">
        <input id="$cid" name="QPlayer2[$id]" type="radio" value="$value" $checked> $text
    </label>
</p>
HTML;
            }
        });
    }

    private function checkbox($title, $options)
    {
        $config = $this->config;
        $this->table($title, function () use ($config, $title, &$options) {
            foreach ($options as $id => $text) {
                $checked = $config->get($id) ? 'checked' : '';
                echo <<<HTML
<p>
    <label for="$id">
        <input id="$id" name="QPlayer2[$id]" type="checkbox" $checked> $text
    </label>
</p>
HTML;
            }
        });
    }
}