<?php

namespace Wulff\util;

use mysql_xdevapi\Session;
use Wulff\entities\Response;

class ConstrollerUtil
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
}