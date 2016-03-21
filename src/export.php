<?php
require_once 'app/init.php';
User::init();

if (isset($_FILES['import_it'])) {
	
	$content = file_get_contents($_FILES['import_it']['tmp_name']);
	
	if(empty($content)) {
		Messages::addError('Файл пуст');
	} elseif (NULL === ($data = json_decode($content, true)) || !isset($data['version']) || !isset($data['data'])) {
		Messages::addError('Файл не является корректным бекапом myfin');
	} elseif ($data['version'] != 1) {
		Messages::addError('Эта версия бекапа не поддерживается');
	} elseif (!Events::reset($data['data'])) {
		Messages::addError('Не удалось импортирорвать');
	} else {
		Messages::addMessage('Данные импортированы');
	}
	
	Util::redirect('export.php');
} elseif (isset($_GET['save'])) {
	header('Content-type: application/json');
	header('Content-Disposition: attachment; filename="' . date('Y_m_d__H_i_s') . '_myfin.json"');
	
	$events = Events::export();
	foreach ($events as &$event) {
		$event['value'] = $event['value'] / 100;
	}
	
	echo json_encode(array('version' => 1,
						   'data' => $events));
	die;
} else {
	Page::set_title('Экспорт / импорт');
	Page::draw('export');
}