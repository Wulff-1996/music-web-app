<?php


namespace Wulff\controllers;


use mysql_xdevapi\Session;
use Wulff\config\Database;
use Wulff\entities\Response;
use Wulff\repositories\AuthRepo;
use Wulff\util\SessionHandler;
use Wulff\util\SessionObject;
use Wulff\util\Validator;

class AuthController
{
    private string $useCase;
    private Database $db;
    private string $method;
    private AuthRepo $authRepo;
    private ?string $id;

    public function __construct(string $useCase, string $method, ?int $id)
    {
        $this->useCase = $useCase;
        $this->db = new Database();
        $this->method = $method;
        $this->id = $id;
        $this->authRepo = new AuthRepo($this->db);
    }

    public function processRequest()
    {
        switch ($this->method) {
            case 'POST':

                switch ($this->useCase) {
                    case ADMIN_LOGIN_PATH:
                        $data = json_decode(file_get_contents('php://input'), true);
                        $response = $this->validateAdminLogin($data);
                        break;

                    case CUSTOMER_LOGIN_PATH:
                        $data = json_decode(file_get_contents('php://input'), true);
                        $response = $this->validateCustomerLogin($data);
                        break;

                    case LOGOUT_PATH:
                        //$data = json_decode(file_get_contents('php://input'), true);
                        $response = $this->logout();
                        break;
                }
                break;

            default:
                $response = Response::notFoundResponse();
                break;
        }

        // send response
        $response->send();
    }

    private function validateAdminLogin($data): Response
    {
        // validate request
        $rules = [
            'password' => [Validator::REQUIRED, Validator::TEXT]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()) {
            // request is invalid
            return Response::badRequest($validator->error());
        }

        // request valid
        $password = $data['password'];

        // get admin password
        $adminPassword = $this->authRepo->getAdminPassword();

        if (!$adminPassword) {
            // no admin pass in db, server error
            return Response::serverError(['message' => 'internal server error, no admins']);
        }

        // validate password
        if (!(password_verify($password, $adminPassword['Password']))) {
            // password does not match
            return Response::unauthorizedResponse(['message' => 'no admin with that password']);
        } else {
            // validated admin account
            // begin session
            SessionHandler::startSession();
            SessionHandler::setSession(new SessionObject(null, true));
            return Response::okNoContent();
        }
    }

    private function validateCustomerLogin($data): Response
    {
        // validate request
        $rules = [
            'email' => [Validator::REQUIRED, Validator::TEXT],
            'password' => [Validator::REQUIRED, Validator::TEXT]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()) {
            // request is invalid
            return Response::badRequest($validator->error());
        }

        // request valid
        $email = $data['email'];
        $password = $data['password'];

        $data = $this->authRepo->getCustomerLogin($email);

        if (!$data){
            // password does not match
            return Response::unauthorizedResponse(['message' => 'email and/or password invalid']);
        }

        if (!(password_verify($password, $data['Password']))) {
            // password does not match
            return Response::unauthorizedResponse(['message' => 'email and/or password invalid']);
        } else {
            // validated admin account
            // begin session
            SessionHandler::startSession();
            SessionHandler::setSession(new SessionObject($data['CustomerId'], false));

            return Response::okNoContent();
        }
    }

    private function logout(): Response
    {
        SessionHandler::startSession();
        $session = SessionHandler::getSession();

        if ($session) {
            SessionHandler::clear();
            return Response::okNoContent();
        } else {
            return Response::unauthorizedResponse(['message' => 'No active session or not authorized']);
        }
    }

}