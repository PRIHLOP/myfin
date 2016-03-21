<?php
require_once 'init.php';
User::init();

Page::set_title('Мои финансы');

$last_events =  Events::getLast(10);

foreach ($last_events as $i => $e) {
    $last_events[$i]['date_str'] = Util::readlyTime($e['date']);
    $last_events[$i]['value_str'] = Util::formatMoneyValue(
            ($e['type'] ? $e['value'] : 0 - $e['value']) / 100, true);
}

Page::addVar('last_events', $last_events);

Page::draw('list');
