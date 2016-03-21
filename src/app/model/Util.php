<?php

/**
 * Description of Util
 *
 * @author roman
 */
class Util {

    public static function redirect($url) {
        header("location: $url");
        exit ();
    }

    public static function readlyTime($time, $full = false, $noYear = false, $plainText = true) {
        if (!is_int($time))
            $time = strtotime($time);

        if ($full)
            return Util::date_ru('H:i, d л Y (з)', $time);

        $now_time = time();

        if (date('dmY', $time) === date('dmY', $now_time)) // сегодня
            return date('H:i', $time);
        if (date('WY', $time) === date('WY', $now_time)) // на этой неделе
            return self::date_ru('з', $time);
        if (date('Y', $time) === date('Y', $now_time) || $noYear) // в этом году
            return self::date_ru('d л', $time);

        if ($plainText)
            return self::date_ru('d л Y', $time);
        else {
            return self::date_ru('d', $time) .
                   '<div class="date_layout">' .
                   self::date_ru('л', $time) . '<br>' .
                   self::date_ru('Y', $time) . '</div>';
        }
    }

    /*
      these are the russian additional format characters
      д: full textual representation of the day of the week
      Д: full textual representation of the day of the week (first character is uppercase),
      к: short textual representation of the day of the week,
      К: short textual representation of the day of the week (first character is uppercase),
      м: full textual representation of a month
      М: full textual representation of a month (first character is uppercase),
      л: short textual representation of a month
      Л: short textual representation of a month (first character is uppercase),
     */

    public static function date_ru($formatum, $timestamp=0) {
        if (!is_int($timestamp))
            $timestamp = strtotime($timestamp);

        if (($timestamp <= -1) || !is_numeric($timestamp))
            return '';

        $q['д'] = array(-1 => 'w', 'воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота');
        $q['Д'] = array(-1 => 'w', 'Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота');
        $q['к'] = array(-1 => 'w', 'вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб');
        $q['К'] = array(-1 => 'w', 'Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб');
        $q['з'] = array(-1 => 'w', 'вос', 'пон', 'вто', 'сре', 'чет', 'пят', 'суб');
        $q['ж'] = array(-1 => 'w', 'воскрес.', 'понед.', 'вторник', 'среда', 'четверг',
            'пятница', 'суббота');
        $q['м'] = array(-1 => 'n', '', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
        $q['М'] = array(-1 => 'n', '', 'Января', 'Февраля', 'Март', 'Апреля', 'Май', 'Июня', 'Июля', 'Август', 'Сентября', 'Октября', 'Ноября', 'Декабря');
        $q['л'] = array(-1 => 'n', '', 'янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек');
        $q['Л'] = array(-1 => 'n', '', 'Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек');

        if ($timestamp == 0)
            $timestamp = time();

        $from = array();
        $to = array();
        foreach ($q as $key => $value) {
            $from[] = $key;
            $to[] = $value[date($value[-1], $timestamp)];
        }

        $formatum = str_replace($from, $to, $formatum);

        return date($formatum, $timestamp);
    }

    public static function getBaseUrl( $veryBase = false ) {
        $temp = preg_replace('@/[^/]*$@', '', $_SERVER["REQUEST_URI"]);
        $temp = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER["HTTP_HOST"] . $temp;

        if ($veryBase && defined('IN_MOBILE_VERSION'))
            $temp = preg_replace('@/m/?$@', '', $temp);

        return $temp;
    }

    public static function linkReplaceParam($params, $remove=array()) {
        $tmp = $_GET;
        foreach ($params as $k => $v)
            $tmp[$k] = $v;
        foreach ($remove as $v)
            unset($tmp[$v]);
        return self::linkFromArray($tmp);
    }

    public static function linkWithoutParam($param) {
        $tmp = $_GET;
        unset($tmp[$param]);
        return self::linkFromArray($tmp);
    }

    public static function linkFromArray($arr) {
        foreach ($arr as $key => $value)
            $arr[$key] = "$key=" . urlencode($value);
        return Util::getBaseUrl() . '/' . ((count($arr) > 0) ? '?' . implode('&', $arr) : '');
    }

    public static function formatMoneyValue($value, $plus=false, $minus=true) {
        $abs = abs($value);
        $cents = strval($abs * 100) % 100;
        $ceil = (int) ($abs - strval($cents / 100));

        return '<span class="MoneyValue ' . ($value == 0 ? 'money_stay' :
                (($value > 0) ? 'money_in' : 'money_out')) . '">'
        . ($value == 0 ? '' : (($value > 0) ? ($plus ? '+' : '') : ($minus ? '-' : '')))
        . number_format($ceil, 0, ',', ' ')
        . ($cents > 0 ? ("<sup>" . sprintf("%02d", $cents) . "</sup>") : '')
        . '</span>';
    }

    public static function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    private static $_timerTime;

    public static function startTimer() {
        self::$_timerTime = self::microtime_float();
    }

    public static function whatTimer() {
        return self::microtime_float() - self::$_timerTime;
    }

}
