<?php


namespace Wulff\entities;

use Wulff\config\Database;
use Wulff\repositories\CustomerRepo;
use Wulff\util\SessionHandler;

class Auth
{
    public static function current(): ?array
    {
        if (!SessionHandler::hasSession()) {
            return null;
        }

        $session = SessionHandler::getSession();
        $userId = $session->getId();

        $db = new Database();
        $customerRepo = new CustomerRepo($db);
        return $customerRepo->find($userId);
    }

}