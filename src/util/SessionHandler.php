<?php


namespace Wulff\util;


use Wulff\config\Database;
use Wulff\repositories\CustomerRepo;

class SessionHandler
{

    public static function startSession(): void
    {
        session_start();
    }

    public static function setSession(SessionObject $sessionObject)
    {
        // when setting the session, it will auto send cookie header to client
        // if no cookie params are set, like expire time, it will expire when browser closes
        $_SESSION['session_object'] = $sessionObject;
    }

    public static function hasSession(): bool
    {
        return isset($_SESSION['session_object']);
    }

    public static function getSession(): ?SessionObject
    {

        if (!static::hasSession()) {
            return null;
        }

        /** @var SessionObject $sessionObject */
        $sessionObject = $_SESSION['session_object'];

        return $sessionObject;
    }

    public static function clear()
    {
        unset($_SESSION['session_object']);
        if (self::hasSession()){
            session_destroy();
        }
    }

    public static function currentUserId(): ?int
    {
        //SessionHandler::startSession();
        if (!SessionHandler::hasSession()) {
            return null;
        }

        $session = SessionHandler::getSession();
        return $session->getId();
    }

    public static function current(): ?array
    {
        $userId = self::currentUserId();

        $db = new Database();
        $customerRepo = new CustomerRepo($db);
        return $customerRepo->find($userId);
    }
}