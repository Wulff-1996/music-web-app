<?php


namespace Wulff\controllers;


use Wulff\config\Database;
use Wulff\entities\Customer;
use Wulff\entities\Response;
use Wulff\repositories\CustomerRepo;
use Wulff\util\ConstrollerUtil;
use Wulff\util\Validator;

class CustomerController
{
    private string $useCase;
    private Database $db;
    private string $method;
    private CustomerRepo $customerRepo;
    private ?string $id;

    public function __construct(string $useCase, $method, $id)
    {
        $this->useCase = $useCase;
        $this->db = new Database();
        $this->method = $method;
        $this->id = $id;
        $this->customerRepo = new CustomerRepo($this->db);
    }

    public function processRequest()
    {
        switch ($this->method) {
            case 'PATCH':
                // check if user is owner of account
                if (!ConstrollerUtil::validateOwnership($this->id)){
                    // user not owner
                    Response::unauthorizedResponse(['message' => 'cannot modify an account you do not own.'])->send();
                    exit();
                }
                
                // user is owner
                $data = json_decode(file_get_contents('php://input'), true);
                $response = $this->updateCustomer($this->id, $data);
                break;
        }

        // send response
        $response->send();
    }

    private function updateCustomer($id, $data): Response
    {
        // validate request
        $rules = [
            'first_name' => [Validator::TEXT, Validator::MAX_LENGTH => 40],
            'last_name' => [Validator::TEXT, Validator::MAX_LENGTH => 20],
            'email' => [Validator::EMAIL, Validator::MAX_LENGTH => 60],
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

        // request valid
        // check if password is updated
        if (isset($data['password'])){
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // update customer
        $isSuccessful = $this->customerRepo->updateCustomer($id, $data);

        if (!$isSuccessful){
            // failed due to FK constraint
            return Response::conflictFkFails();
        } else {
            // update success, send back updated resource
            $updatedCustomer = $this->customerRepo->find($id);
            return Response::success($updatedCustomer);
        }
    }
}