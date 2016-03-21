<?php

class User {

    private static $_user = null;

    public static function init() {
        if (!get_config('use_openid'))
            return;

        if (isset ($_SESSION['user_identity'])) {
            $user = Db::selectGetArray('SELECT * FROM users WHERE ident_hash = @s',
                    md5($_SESSION['user_identity']));

            if (is_array($user) && count($user) == 1) // пользователь уже есть
                self::$_user = $user[0];
            else { // надо создать
                $email = @$_SESSION['user_attributes']["contact/email"];
                $name = @$_SESSION['user_attributes']["namePerson/friendly"];

                if ($email == '')
                    $email = 'Scrooge.McDuck@wrong.hostname';

                if ($name == '') {
                    $name = split('@', $email);
                    $name = $name[0];
                }

                $user['name'] = $name;
                $user['email'] = $email;
                $user['ident_hash'] = md5($_SESSION['user_identity']);

                Db::justQuery('INSERT INTO users (name, email, ident_hash) VALUES (@s, @s, @s)',
                        $user['name'], $user['email'], $user['ident_hash']);

                $user['id'] = Db::insertedId();

                self::$_user = $user;
            }

        } else {
            $_SESSION['redirect_after_login'] = $_SERVER["REQUEST_URI"];
            Util::redirect( Util::getBaseUrl( true ) . '/login.php' );
        }
    }

    public static function getUser() {
        return self::$_user;
    }

    public static function getId() {
        if (!get_config('use_openid'))
            return 0;

        if (self::$_user !== null)
            return self::$_user['id'];

        return null;
    }

    public static function getName() {
        if (!get_config('use_openid'))
            return 'Launchpad McQuack';

        if (self::$_user !== null)
            return self::$_user['name'];

        return null;
    }


}