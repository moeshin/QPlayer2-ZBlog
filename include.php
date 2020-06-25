<?php

use QPlayer\Cache\Cache;
use QPlayer2\Config;
use QPlayer2\Footer;

RegisterPlugin("QPlayer2", "ActivePlugin_QPlayer2");

function ActivePlugin_QPlayer2()
{
    Add_Filter_Plugin('Filter_Plugin_Zbp_MakeTemplatetags', 'QPlayer2_footer');
}

function UninstallPlugin_QPlayer2() {
    global $zbp;
    require_once 'lib/Config.php';
    require_once 'lib/cache/Cache.php';
    $config = new Config();
    if ($config->get('cacheType') != 'none') {
        Cache::BuildWithConfig($config)->uninstall();
    }
    $zbp->DelConfig('QPlayer2');
}

function QPlayer2_footer()
{
    require_once 'lib/Footer.php';
    new Footer();
}
