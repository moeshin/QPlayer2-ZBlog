<?php

use QPlayer2\Config;
use QPlayer2\Page;

require '../../../zb_system/function/c_system_base.php';
require '../../../zb_system/function/c_system_admin.php';

/**
 * @var ZBlogPHP $zbp
 * @var string $blogpath
 * @var string $bloghost
 */

$zbp->Load();

$action = 'root';
if (!$zbp->CheckRights($action)) {
    $zbp->ShowError(6);
    die();
}

if (!$zbp->CheckPlugin('QPlayer2')) {
    $zbp->ShowError(48);
    die();
}

$blogtitle = 'QPlayer2';

$do = GetVars('do');

if ('submit' == $do) {
    CheckIsRefererValid();
    require_once 'lib/Config.php';
    $config = new Config();
    $config->submit();
}

require $blogpath . 'zb_system/admin/admin_header.php';
require $blogpath . 'zb_system/admin/admin_top.php';
require_once 'lib/Page.php';


?>
<style>
    code {
        padding: 3px 4px;
        background: rgba(0,0,0,.08);
        border-radius: 4px;
        font-style: normal;
        font-size: 13px;
    }
    textarea {
        width: 100%;
        height: 100px;
    }
</style>
<div id="divMain">
    <div class="divHeader"><?php echo $blogtitle; ?></div>
    <div id="divMain2">
        <form action="?do=submit" method="post">
            <input type="hidden" name="csrfToken" value="<?php echo $zbp->GetCsrfToken(); ?>">
            <table class="tableFull" role="presentation">
                <tbody>
                <?php new Page(); ?>
                </tbody>
            </table>
            <p><a target="_blank" href="api.php?do=flush"><input type="button" class="button" value="清除缓存"></a></p>
            <p><input type="submit" class="button" value="提交" /></p>
        </form>
        <script type="text/javascript">
            AddHeaderIcon("<?php echo plugin_dir_url(__FILE__) . 'logo.png'; ?>");
        </script>
    </div>
</div>
<?php
require $blogpath . 'zb_system/admin/admin_footer.php';

RunTime();
