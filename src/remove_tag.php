<?php
require_once 'app/init.php';
User::init();

if (isset ($_GET['ev_id']) && isset ($_GET['tag_id'])
        && Db::justQuery('DELETE FROM `ev2tag` WHERE `ev_id`=@i AND `tag_id`=@i AND `user_id`=@i LIMIT 1',
                $_GET['ev_id'], $_GET['tag_id'],  User::getId())
        )
    Messages::addMessage ('Тег удален');

$url = isset($_GET['r']) ? urldecode($_GET['r']) : Util::getBaseUrl();
Util::redirect( $url );