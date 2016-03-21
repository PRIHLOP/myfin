<?php
require_once 'app/init.php';
User::init();

if (isset ($_GET['id'])
        && Db::justQuery('DELETE FROM `events` WHERE `id`=@i AND user_id = @i LIMIT 1',
                $_GET['id'], User::getId())
        && Db::justQuery('DELETE FROM `ev2tag` WHERE `ev_id`=@i AND user_id = @i',
                $_GET['id'], User::getId())
        )
    Messages::addMessage ('Запись удалена');
   
$url = isset($_GET['r']) ? urldecode($_GET['r']) : Util::getBaseUrl();
Util::redirect( $url );