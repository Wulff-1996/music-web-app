<?php

namespace Wulff\util;

define('LOG_FILE_NAME', 'log.htm');

use mysql_xdevapi\Session;
use Wulff\entities\Response;

class ControllerUtil
{
    public static function validatePathId($id)
    {
        if (!$id) {
            Response::notFoundResponse()->send();
            exit();
        }
    }

    /**
     * Validates weather or not he logged in user is the owner of the resource they want to access/modify
     */
    public static function validateOwnership($id) : bool
    {
        if (!$id) {
            return false;
        }

        // id provided
        $sessionUserId = SessionHandler::currentUserId();

        if (!$sessionUserId){
            return false;
        }

        // there is a session id and request path id
        if ($id != $sessionUserId){
            return false;
        }

        return true;
    }

    public static function debug($info){
        $fileName = LOG_FILE_NAME;
        $path = getcwd();

        // If the invoking php file is in the src directory, the log file is set in the root
        if (substr($path, strlen($path) - 4, 4) === '\src') {
            $fileName = '../' . $fileName;
        }

        $text = '';
        if (!file_exists($fileName)) {
            $text .= '<pre>';
        }
        $text .= '--- ' . date('Y-m-d h:i:s A', time()) . ' ---<br>';

        $logFile = fopen($fileName, 'a');

        if (gettype($info) === 'array') {
            $text .= print_r($info, true);
        } else {
            $text .= $info . '<br>';
        }
        fwrite($logFile, $text);

        fclose($logFile);
    }
}