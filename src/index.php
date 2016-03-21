<?php

/*
 * Список событий
 */

require_once 'app/init.php';
require_once 'app/model/EventsList.php';
User::init();

Page::set_title('Мои финансы');

/* Получение списка событий
 */

$events_list = Events::getByParams($_GET);

if (!isset($_GET['no_limit'])) {
    $found_rows = Db::selectGetValue('SELECT FOUND_ROWS()');
    if ($found_rows > get_config('items_on_page')) {
        Page::addVar('found_rows', $found_rows);
        Page::addVar('no_limit_link', Util::linkReplaceParam(array('no_limit' => 1)));
    }
}

$events_list = EventsList::prepareEventsList($events_list);

Page::addVar('events_list', $events_list['list']);
Page::addVar('events_list_st', $events_list['st']);
Page::addVar('total_in', Util::formatMoneyValue($events_list['total_in'], false, false));
Page::addVar('total_out', Util::formatMoneyValue(0 - $events_list['total_out'], false, false));
Page::addVar('total', Util::formatMoneyValue($events_list['total_in'] - $events_list['total_out']));

/* Построение ссылок для выборок по времени
 */

$tmp = EventsList::makeLinks4SelectByDate();
Page::addVar('date_links', $tmp[0]);
Page::addVar('date_links_d', $tmp[1]);

Page::addVar('date_start', isset($_GET['date_start']) ? $_GET['date_start'] : 
        date('Y-m-d H:i', Events::getMinDate()));
Page::addVar('date_end', isset($_GET['date_end']) ? $_GET['date_end'] : date('Y-m-d H:i', time()));

$hidden_inputs = $_GET;
unset($hidden_inputs['date_start']);
unset($hidden_inputs['date_end']);
unset($hidden_inputs['no_limit']);

Page::addVar('hidden_inputs', $hidden_inputs);

/* Выборка по ключевому слову (поиск)
 */
$hidden_inputs4search = $_GET;
unset($hidden_inputs4search['search']);
unset($hidden_inputs4search['no_limit']);

Page::addVar('hidden_inputs4search', $hidden_inputs4search);
Page::addVar('search_str',  isset($_GET['search']) ? $_GET['search'] : '' );

/* Построение ссылок для выборок по типу
 */
Page::addVar('money_in_type_link',
                Util::linkReplaceParam(array('mft' => 1), array('no_limit')));
Page::addVar('money_out_type_link',
                Util::linkReplaceParam(array('mft' => 0), array('no_limit')));

/* Параметры выборки
 */

Page::addVar('select', EventsList::selectParams());

/* Теги для облака
 */

$tmp = Tags::tags4Cloud();

$tl = array();
if (count($tmp) > 0) {
    $max = $tmp[0]['count'];
    $min = $tmp[count($tmp) - 1]['count'];

    $steps = 4;

    foreach ($tmp as $key => $value) {
        if ($max == $min)
            $tmp[$key]['size'] = intval($steps / 2) + 1;
        else
            $tmp[$key]['size'] = intval($steps * ($value['count'] - $min) / ($max - $min)) + 1;

        $tmp[$key]['link'] = Util::linkReplaceParam(array('by_tag' => $value['id']),
                        array('no_limit'));
        $tl[] = "{name: '" . addcslashes($value['name'], "'\\") . "', color: '" . $value['color'] . "'}";
    }

}

$tl = count($tl) ? "[" . implode(", ", $tl) . "]" : '[]';

Page::addVar('cloud_tags', $tmp);
Page::addVar('tag_list', $tl);

/* Построение ссылок для ПодИтогов
 */
Page::addVar('SubTotal_Daily_link',
                Util::linkReplaceParam(array('st' => 1), array('no_limit', 'st_only')));
Page::addVar('SubTotal_Weekly_link',
                Util::linkReplaceParam(array('st' => 2), array('no_limit', 'st_only')));
Page::addVar('SubTotal_Monthly_link',
                Util::linkReplaceParam(array('st' => 3), array('no_limit', 'st_only')));
Page::addVar('SubTotal_Yearly_link',
                Util::linkReplaceParam(array('st' => 4), array('no_limit', 'st_only')));

/* Еще по мелочи
 */

Page::addVar('new_button_link', "edit.php?new&r=" . urlencode($_SERVER["REQUEST_URI"]));

/* Всё готово, осталось отрисовать страницу
 */

Page::draw('list');
