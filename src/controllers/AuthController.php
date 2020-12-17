<?php


namespace Wulff\controllers;


use mysql_xdevapi\Session;
use Wulff\config\Database;
use Wulff\entities\Customer;
use Wulff\entities\Response;
use Wulff\repositories\AuthRepo;
use Wulff\util\HttpCode;
use Wulff\util\SessionHandler;
use Wulff\util\SessionObject;
use Wulff\util\Validator;

// TODO return json naming for customer
// TODO return customer/admin info on login
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

                    case CUSTOMER_SIGN_UP_PATH:
                        $data = json_decode(file_get_contents('php://input'), true);
                        $response = $this->signup($data);
                        break;

                    case LOGOUT_PATH:
                        $data = json_decode(file_get_contents('php://input'), true);
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

        // close connection
        $this->authRepo->closeConnection();
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

        if (!$data) {
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
            // TODO return logged in customer
            return Response::success(['customer_id' => $data['CustomerId']]);
        }
    }

    private function signup($data)
    {
        // validate request
        $rules = [
            'first_name' => [Validator::REQUIRED, Validator::TEXT, Validator::MAX_LENGTH => 40],
            'last_name' => [Validator::REQUIRED, Validator::TEXT, Validator::MAX_LENGTH => 20],
            'email' => [Validator::REQUIRED, Validator::EMAIL, Validator::MAX_LENGTH => 60],
            'password' => [Validator::REQUIRED, Validator::TEXT, Validator::MAX_LENGTH => 255],
            'phone' => [Validator::TEXT, Validator::MAX_LENGTH => 24],
            'fax' => [Validator::TEXT, Validator::MAX_LENGTH => 24],
            'company' => [Validator::TEXT, Validator::MAX_LENGTH => 80],
            'address' => [Validator::TEXT, Validator::MAX_LENGTH => 70],
            'city' => [Validator::TEXT, Validator::MAX_LENGTH => 40],
            'state' => [Validator::TEXT, Validator::MAX_LENGTH => 40],
            'postal_code' => [Validator::TEXT, Validator::MAX_LENGTH => 10],
            'country' => [Validator::TEXT, Validator::MAX_LENGTH => 40]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()) {
            // request is invalid
            return Response::badRequest($validator->error());
        }

        // check if email is unique
        if (!$this->authRepo->isEmailUnique($data['email'])){
            // email exists
            return Response::badRequest(['email is not unique']);
        }

        // email is unique proceed
        // hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $customer = new Customer(
            null,
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $hashedPassword,
            $data['phone'],
            $data['fax'],
            $data['company'],
            $data['address'],
            $data['city'],
            $data['state'],
            $data['postal_code'],
            $data['country']
        );

        try {
            // add track
            $customerId = $this->authRepo->createCustomer($customer);

        } catch (PDOException $e) {
            // integrity error
            return Response::conflictFkFails();
        }

        // TODO make to json naming
        // get inserted track
        $customer->setId($customerId);

        return Response::success((array) $customer);
    }

    private function logout(): Response
    {
        SessionHandler::startSession();
        if (SessionHandler::hasSession()){
            SessionHandler::clear();
            return Response::okNoContent();
        } else {
            return Response::unauthorizedResponse(['not logged in']);
        }
    }

}