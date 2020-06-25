<?php

use QPlayer2\API;

require '../../../zb_system/function/c_system_base.php';

if (!$zbp->CheckPlugin('QPlayer2')) {
    $zbp->ShowError(48);
    die();
}

$do = GetVars('do');

require_once 'lib/API.php';

new API($do);
