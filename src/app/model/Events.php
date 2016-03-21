<?php

class Events {

    public static function getById($id) {
        return Db::selectGetArray('SELECT * FROM `events` WHERE `id` = @i AND user_id = @i',
                $id, User::getId());
    }

    public static function getByParams($get) {
        $where = array();

        $where[] = Db::buildReq('events.user_id = @i', User::getId());

        if (isset($get['date_start']))
            $where[] = Db::buildReq('events.date > FROM_UNIXTIME(@i)', strtotime($get['date_start']));

        if (isset($get['date_end']))
            $where[] = Db::buildReq('events.date < FROM_UNIXTIME(@i)', strtotime($get['date_end']));

        if (isset($get['mft'])) // money flow type
            $where[] = Db::buildReq('events.type = @i', (bool) $get['mft']);

        if (isset($get['search']))
            $where[] = Db::buildReq('events.description LIKE \'%@l%\'', $get['search']);

        if (isset($get['by_tag'])) {
            $tags = explode(',', $get['by_tag']);

	    sort($tags, SORT_NUMERIC);
	    for($i = 0; $i < count($tags); $i++) {
		if ($tags[$i] < 0) {
		    $tags_minus[] = abs($tags[$i]);
		} else {
		    $tags_plus[] = (int)$tags[$i];
		}
	    }
	    $sql =
		'SELECT SQL_CALC_FOUND_ROWS events.* '.
		'FROM events '.
		    'JOIN '.
			'('.
			    'SELECT DISTINCT ev.id '.
			    'FROM '.
				(
				    count($where) > 0
					?
					    '('.
						'SELECT * '.
						'FROM events '.
						'WHERE '.
						    implode(' AND ', $where).
					    ')'
					:
					    'events'
				).
				' AS ev '.
				'LEFT JOIN '.
				    (
					count($tags_plus) > 0
					    ?
						'('.
						    'SELECT * '.
						    'FROM ev2tag '.
						    'WHERE '.
							'tag_id IN ('.implode(', ', $tags_plus).')'.
						')'
					    :
						'ev2tag'
				    ).
				    ' AS e2t '.
				    'ON e2t.ev_id = ev.id '.
				'LEFT JOIN '.
				    '('.
					'SELECT * '.
					'FROM ev2tag '.
					'WHERE '.
					    (
						count($tags_minus) > 0
						    ?
							'tag_id IN ('.implode(', ', $tags_minus).')'
						    :
							'tag_id IN (-1)'
					    ).
					') AS e2t_minus '.
				    'ON e2t_minus.ev_id = ev.id '.
			    'WHERE '.
				'e2t.tag_id IS NOT NULL AND '.
				'e2t_minus.tag_id IS NULL '.
			    (
				count($tags_plus) > 0
				    ?
					'GROUP BY ev.id '.
					'HAVING COUNT(id) = '.count($tags_plus)
				    :
					''
			    ).
			') AS ok '.
		    'USING (id)'.
		'ORDER BY date DESC';

//            echo $sql."<BR>";
        } else
            $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM `events` '
                    . (count($where) > 0 ? 'WHERE ' . implode(' AND ', $where) : '')
                    . ' ORDER BY date DESC';

        if (!isset($get['no_limit']))
            $sql .= Db::buildReq(' LIMIT @i', get_config('items_on_page'));

        $events_list = Db::selectGetArray($sql);

        return $events_list;
    }

    public static function getMinDate() {
        $tmp = (int) Db::selectGetValue('SELECT UNIX_TIMESTAMP(date)'
                        . ' FROM events WHERE user_id = @i ORDER BY date LIMIT 1',
                        User::getId());
        return $tmp > 0 ? $tmp - 1 : $tmp;
    }

    public static function getCurentBalance() {
        $tmp = Db::selectGetArray('SELECT type, SUM(value) AS sum FROM `events` WHERE user_id = @i GROUP BY type',
                User::getId());

        $result = 0;
        $plus = 0;
        $minus = 0;

        foreach ($tmp as $t)
            $result = ($t['type'] == 0) ?
                $result - ($minus+=$t['sum']/100) :
                $result + ($plus+=$t['sum']/100);

        return array($result, $plus, $minus);
    }

    public static function insertEvent($description, $type, $value, $date) {
        if (Db::justQuery('INSERT INTO `events` (`description`, `type`, `value`, `date`, user_id)'
                        . ' VALUES (@s, @i, @i, FROM_UNIXTIME(@i), @i)',
                        htmlspecialchars($description), $type,
                        abs(strval($value * 100)), $date, User::getId()))
            return Db::insertedId();
        return null;
    }

    public static function updateEvent($description, $type, $value, $date, $id) {
        return Db::justQuery('UPDATE `events` SET `description`=@s, `type`=@i, `value`=@i, '
                        . '`date`=FROM_UNIXTIME(@i) WHERE `id`=@i AND `user_id`=@i',
                        htmlspecialchars($description), $type, abs(strval($value * 100)),
                $date, $id, User::getId());
    }

    public static function getLast($limit) {
        return Db::selectGetArray('SELECT UNIX_TIMESTAMP(e.date) AS unix_date, e.*, '
                . 'GROUP_CONCAT(t.name SEPARATOR  ", ") AS tags '
                . 'FROM events AS e '
                . 'LEFT JOIN ev2tag e2t ON e.id = e2t.ev_id '
                . 'LEFT JOIN tags t ON t.id = e2t.tag_id '
                . 'WHERE e.user_id = @i '
                . 'GROUP BY e.id '
                . 'ORDER BY e.date DESC '
                . 'LIMIT @i',
                User::getId(), $limit);
    }
	
	public static function export() {
		return Db::selectGetArray('SELECT e.type, e.value, e.date, e.description, '
                . 'GROUP_CONCAT(t.name SEPARATOR  ", ") AS tags '
                . 'FROM events AS e '
                . 'LEFT JOIN ev2tag e2t ON e.id = e2t.ev_id '
                . 'LEFT JOIN tags t ON t.id = e2t.tag_id '
                . 'WHERE e.user_id = @i '
                . 'GROUP BY e.id '
                . 'ORDER BY e.date DESC ',
                User::getId());
	}
	
	public static function reset($events) {
		
		// Удаление всего добра текущего пользователя
		$ids = Db::selectGetVerticalArray('select id from events where user_id = @i', User::getId());
		if (!empty($ids)) {
			Db::justQuery('DELETE FROM `events` WHERE id in @a', $ids);
			Db::justQuery('DELETE FROM `ev2tag` WHERE `ev_id` in @a', $ids);
		}
		
		// Импорт
		foreach($events as $event) {
			$id = Events::insertEvent($event['description'], $event['type'], $event['value'], strtotime($event['date']));
			Tags::update4Event($id, explode(',', $event['tags']));
		}
		
		return true;
	}
}
