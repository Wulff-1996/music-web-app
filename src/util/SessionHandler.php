<?php


namespace Wulff\util;


class SessionHandler
{

    public static function startSession(): void
    {
        session_set_cookie_params(3600 * 3); // valid in 3 hours
        session_start();
    }

    public static function setSession(SessionObject $sessionObject)
    {
        $_SESSION['session_object'] = $sessionObject;
    }

    public static function getSession(): ?SessionObject
    {

        if (!isset($_SESSION['session_object'])) {
            return null;
        }

        /** @var SessionObject $sessionObject */
        $sessionObject = $_SESSION['session_object'];

        return $sessionObject;
    }

    public static function clear(){
        unset($_SESSION['session_object']);
    }

}