<?php
require_once 'app/init.php';

unset ($_SESSION['user_identity']);
unset ($_SESSION['user_attributes']);

Util::redirect( Util::getBaseUrl() );