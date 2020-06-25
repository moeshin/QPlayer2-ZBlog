<?php


namespace QPlayer2;

require_once 'Config.php';

class Footer
{
    public function __construct()
    {
        global $zbp;
        $core = new Config();
        $url = plugin_dir_url(__DIR__);
        $assets = $url . 'assets';
        $api = $url . 'api.php';
        $cdn = $core->get('cdn');
        $footer = &$zbp->footer;
        if ($core->get('jQuery')) {
            $prefix = $cdn ? 'https://cdn.jsdelivr.net/npm/jquery@' . Config::verJQ . '/dist' : $assets;
            $footer .= '<script src="' . $prefix  . '/jquery.min.js"></script>';
        }
        $prefix = $cdn ? 'https://cdn.jsdelivr.net/npm/jquery.marquee@' . Config::verMarquee : $assets;
        $footer .=  '<script src="' . $prefix . '/jquery.marquee.min.js"></script>';
        $prefix = $cdn
            ? 'https://cdn.jsdelivr.net/gh/moeshin/QPlayer2-ZBlog@' . self::getVersion() . '/assets'
            : $assets;
        $footer .= <<<HTML
<link rel="stylesheet" href="$prefix/QPlayer.css">
<script src="$prefix/QPlayer.js"></script>
<script src="$prefix/QPlayer-plugin.js"></script>
<script>
$(function() {
    var q = QPlayer;
    var plugin = q.plugin;
    plugin.api = '$api';
    plugin.setList({$core->text('list')});
    q.isRoate = {$core->bool('isRotate')};
    q.isShuffle = {$core->bool('isShuffle')};
    q.setColor('{$core->text('color')}');
});
</script>
HTML;
    }

    private static function getVersion()
    {
        $xml = simplexml_load_file(dirname(__FILE__, 2) . '/plugin.xml');
        return $xml->version;
    }
}