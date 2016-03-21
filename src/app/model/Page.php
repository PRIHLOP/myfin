<?php
/**
 * Управление страницей
 * простой шаблонизатор или типа того
 *
 * @author roman
 */
class Page {
    private static $_title;
    private static $_vars;

    private static $_layout;
    private static $_script;

    private static $_scripts_dir;

    public static function draw( $script = null ) {
        if ($script !== null)
            self::$_script = $script;

        header ('Content-type: text/html; charset=utf-8');

        $content = '';

        if (self::$_script !== null)
            $content = self::_getContent();

        include self::$_scripts_dir . '/' . self::$_layout . '.phtml';
    }

    public static function addVar( $name, $value ) {
        self::$_vars[$name] = $value;
    }

    /*
     * Getters / Setters
     */

    public static function get_title() {
        return self::$_title;
    }

    public static function set_title($_title) {
        self::$_title = $_title;
    }

    public static function get_layout() {
        return self::$_layout;
    }

    public static function set_layout($_layout) {
        self::$_layout = $_layout;
    }

    public static function get_script() {
        return self::$_script;
    }

    public static function set_script($_script) {
        self::$_script = $_script;
    }

    public static function get_scripts_dir() {
        return self::$_scripts_dir;
    }

    public static function set_scripts_dir($_scripts_dir) {
        self::$_scripts_dir = $_scripts_dir;
    }

    /*
     * Private
     */

    private static function _getContent() {
        ob_start();

        if (self::$_vars)
            extract(self::$_vars);

        include self::$_scripts_dir . '/' . self::$_script . '.phtml';

        $return = ob_get_contents();
        ob_end_clean();

        return $return;
    }
}
?>
