<?php
require_once 'app/init.php';
require_once 'app/openid.php';

try {
    if (!isset($_GET['openid_mode'])) {
        if (isset($_GET['openid_identifier'])) {
            $openid = new LightOpenID;
            $openid->identity = $_GET['openid_identifier'];
            $openid->required = array('namePerson/friendly', 'contact/email');
            Util::redirect($openid->authUrl());
        }
    } elseif ($_GET['openid_mode'] == 'cancel') {
        Messages::addWarning('Вы отменили вход!');
    } else {
        $openid = new LightOpenID;
        if ($openid->validate()) {
            $_SESSION['user_identity'] = $openid->identity;
            $_SESSION['user_attributes'] =  $openid->getAttributes();
            //var_dump($openid->identity);
            //var_dump($openid->getAttributes());
            Util::redirect( isset($_SESSION['redirect_after_login']) ? 
                    $_SESSION['redirect_after_login']  : Util::getBaseUrl() );
        } else {
            Messages::addWarning('Не удалось войти!');
        }
    }
} catch (ErrorException $e) {
    Messages::addError( $e->getMessage() );
}


Page::set_title('Вход / Мои финансы');
Page::draw('login');