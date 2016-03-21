<?php
require_once '../app/init.php';
Page::set_scripts_dir( realpath(dirname(__FILE__) . '/view') );

define('IN_MOBILE_VERSION', true);