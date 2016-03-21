<?php

/**
 * Description of Messages
 *
 * @author anon
 */
class Messages {
    /*
     * Messages
     */

    public static function addMessage($text) {
        if (!isset($_SESSION['messages']['messages']))
            $_SESSION['messages']['messages'] = array();

        $_SESSION['messages']['messages'][] = $text;
    }

    public static function popMessage() {
        if (!isset($_SESSION['messages']['messages']) || count($_SESSION['messages']['messages']) == 0)
            return null;

        return array_pop($_SESSION['messages']['messages']);
    }

    public static function hasMessages() {
        return isset($_SESSION['messages']['messages']) && (count($_SESSION['messages']['messages']) > 0);
    }

    /*
     * Errors
     */

    public static function addError($text) {
        if (!isset($_SESSION['messages']['errors']))
            $_SESSION['messages']['errors'] = array();

        $_SESSION['messages']['errors'][] = $text;
    }

    public static function popError() {
        if (!isset($_SESSION['messages']['errors']) || count($_SESSION['messages']['errors']) == 0)
            return null;

        return array_pop($_SESSION['messages']['errors']);
    }

    public static function hasErrors() {
        return isset($_SESSION['messages']['errors']) && (count($_SESSION['messages']['errors']) > 0);
    }

    /*
     * Warnings
     */

    public static function addWarning($text) {
        if (!isset($_SESSION['messages']['warnings']))
            $_SESSION['messages']['warnings'] = array();

        $_SESSION['messages']['warnings'][] = $text;
    }

    public static function popWarning() {
        if (!isset($_SESSION['messages']['warnings']) || count($_SESSION['messages']['warnings']) == 0)
            return null;

        return array_pop($_SESSION['messages']['warnings']);
    }

    public static function hasWarnings() {
        return isset($_SESSION['messages']['warnings']) && (count($_SESSION['messages']['warnings']) > 0);
    }

    /*
     * Debug
     */

    public static function addDebug($text) {
        if (!isset($_SESSION['messages']['debug']))
            $_SESSION['messages']['debug'] = array();

        $_SESSION['messages']['debug'][] = $text;
    }

    public static function popDebug() {
        if (!isset($_SESSION['messages']['debug']) || count($_SESSION['messages']['debug']) == 0)
            return null;

        return array_pop($_SESSION['messages']['debug']);
    }

    public static function hasDebug() {
        return isset($_SESSION['messages']['debug']) && (count($_SESSION['messages']['debug']) > 0);
    }

}