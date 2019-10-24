<?php
require('../vendor/autoload.php');

$mobilephone = '18665027895';
$ok = \finger\Validator::is_mobilephone($mobilephone);
if ($ok) {
    echo '是手机号码';
} else {
    echo '不是手机号码';

}