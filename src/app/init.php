<?php
require_once 'model/Util.php';

Util::startTimer();

// Убираем слеши понаставленые magic quotes
if (get_magic_quotes_gpc()) {
    function stripslashes_gpc(&$value) {
        $value = stripslashes($value);
    }
    array_walk_recursive($_GET, 'stripslashes_gpc');
    array_walk_recursive($_POST, 'stripslashes_gpc');
    array_walk_recursive($_COOKIE, 'stripslashes_gpc');
    array_walk_recursive($_REQUEST, 'stripslashes_gpc');
}

// Для корректной работы в IIS
if(!isset($_SERVER['REQUEST_URI'])) {
  $arr = explode("/", $_SERVER['PHP_SELF']);
  $_SERVER['REQUEST_URI'] = "/" . $arr[count($arr)-1];
  if ($_SERVER['argv'][0]!="")
   $_SERVER['REQUEST_URI'] .= "?" . $_SERVER['argv'][0];
}
if (@$_SERVER['HTTPS'] == 'off')
    unset ($_SERVER['HTTPS']);

setlocale(LC_ALL, 'ru_RU.UTF-8');
session_start();

define('APPLICATION_PATH', realpath(dirname(__FILE__)));
define('PUBLIC_PATH', realpath(APPLICATION_PATH . '/..'));

$tmp = file_get_contents(PUBLIC_PATH . '/version');
define('VERSION', $tmp ? $tmp : 0);

if (!file_exists(APPLICATION_PATH . '/config.php')) {
	die ('Нет файла config.php!<br/>Переименуйте app/example_config.php в config.php и отредактируйте его в соответствии с подсказаками внутри.<br/>См. также install.txt');
}

require_once 'config.php';

date_default_timezone_set(get_config('timezone'));

// HTTP-авторизация
if (get_config('auth_login') != '') {
    if ($_SERVER['PHP_AUTH_USER'] != get_config('auth_login') ||
            $_SERVER['PHP_AUTH_PW'] != get_config('auth_password')) {
        header('WWW-Authenticate: Basic realm="MyFin"');
        header('HTTP/1.0 401 Unauthorized');
        echo "403 Forbidden";
        exit;
    }
}

require_once 'model/Page.php';
require_once 'model/Messages.php';
require_once 'model/Db.php';
require_once 'model/User.php';
require_once 'model/Events.php';
require_once 'model/Tags.php';

Page::set_scripts_dir(APPLICATION_PATH . '/view');
Page::set_layout('layout');