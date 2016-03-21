<?php
require_once 'init.php';
User::init();

if ( count($_GET) == 0 && count($_POST) == 0 ) {
    $event['description'] = '';
    $event['value'] = 0;
    $event['type'] = 0;
    $event['date'] = time();
    $event['tags'] = '';
    $event['id'] = 0;
}

if (isset($_GET['id'])) {
    $event = Events::getById($_GET['id']);

    if (!$event) {
        Messages::addError('Запись не найдена');
        Page::set_title('Правка / Мои финансы');
        Page::draw();
        exit();
    } else {
        $event = $event[0];

        $event['value'] = $event['value'] / 100.0;
        $event['date'] = strtotime($event['date']);

        $tags = Tags::getOnlyNamesByEvent($event['id']);

        $event['tags'] = implode(', ', $tags);
    }
}

if (count($_POST)) {
    $event['description'] = $_POST['description'];
    $event['value'] = floatval(str_replace(array(',', ' '), array('.', ''), $_POST['value']));
    $event['type'] = (bool) $_POST['type'];
    $event['date'] = strtotime($_POST['date']);
    $event['tags'] = $_POST['tags'];
    $event['id'] = $_POST['id'] ? $_POST['id'] : 0;

    if (addEvent($event)) {
        $url = isset($_GET['r']) ? urldecode($_GET['r']) : Util::getBaseUrl();
        Util::redirect($url);
    }
}

if (count($_POST))
    $form_data = $_POST;
else {
    $form_data = $event;
    $form_data['date'] = date('Y-m-d H:i', $form_data['date']);
    $form_data['value'] = ($form_data['value'] - intval($form_data['value']) != 0) ?
            number_format($form_data['value'], 2, ',', ' ') :
            number_format($form_data['value'], 0, ',', ' ');
}

if ($form_data['value'] == 0)
    $form_data['value'] = '';

Page::set_title(($event['id'] == 0 ? 'Добавление' : 'Правка') . ' / Мои финансы');
Page::addVar('form_data', $form_data);

$tag_list = Tags::getAllUsed();
$tl = array();
foreach ($tag_list as $value)
    $tl[] = "{name: '" . $value['name'] . "', color: '" . $value['color'] . "'}";
Page::addVar('tag_list', count($tl) ? "[" . implode(", ", $tl) . "]" : '[]');

Page::draw('edit');

function addEvent(&$event) {
    $event['type'] = (bool) $event['type'];

    if ($event['date'] === false) {
        Messages::addError('Неверный формат даты');
        return false;
    }

    if ($event['id'] == 0) {
        $result =
            $event['id'] =
            Events::insertEvent($event['description'], $event['type'], $event['value'], $event['date']);
    }
    else
        $result = Events::updateEvent($event['description'], $event['type'], $event['value'],
                $event['date'], $event['id']);

    if (!$result)
        return false;

    if(!Tags::update4Event($event['id'], explode(',', $event['tags'])))
        return false;

    Messages::addMessage('Изменения сохранены');
    return true;
}
