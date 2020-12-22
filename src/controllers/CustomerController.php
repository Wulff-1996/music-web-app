<?php


namespace Wulff\controllers;

define('LOG_FILE_NAME', 'log.htm');

use Wulff\config\Database;
use Wulff\entities\Customer;
use Wulff\entities\EntityMapper;
use Wulff\entities\Response;
use Wulff\repositories\CustomerRepo;
use Wulff\util\ConstrollerUtil;
use Wulff\util\SessionHandler;
use Wulff\util\Validator;

// TODO add json naming for customer and invoice
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


    // It debugs the received information to an HTML file
    function debug($info)
    {

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

    public function processRequest()
    {
        switch ($this->method) {

            case 'POST':

                $data = json_decode(file_get_contents('php://input'), true);

                if ($this->id){
                    $this->debug('update vustomer');
                    // check if user is owner of account
                    if (!ConstrollerUtil::validateOwnership($this->id)) {
                        // user not owner
                        Response::unauthorizedResponse(['message' => 'cannot modify an account you do not own.'])->send();
                        exit();
                    }
                    // user is owner
                    $response = $this->updateCustomer($this->id, $data);
                } else {
                    $response = $this->createCustomerInvoice($data);
                }

                break;

            case 'PATCH':
                $this->debug('patch');
                // check if user is owner of account
                if (!ConstrollerUtil::validateOwnership($this->id)) {
                    // user not owner
                    Response::unauthorizedResponse(['message' => 'cannot modify an account you do not own.'])->send();
                    exit();
                }

                // user is owner
                $data = json_decode(file_get_contents('php://input'), true);

                $response = $this->updateCustomer($this->id, $data);
                break;

            case 'DELETE':
                // check if user is owner of account
                if (!ConstrollerUtil::validateOwnership($this->id)) {
                    // user not owner
                    Response::unauthorizedResponse(['message' => 'cannot modify an account you do not own.'])->send();
                    exit();
                }

                $response = $this->deleteCustomer($this->id);
                break;
        }

        // send response
        $response->send();

        // close connection
        $this->customerRepo->closeConnection();
    }

    private function createCustomerInvoice(?array $data)
    {
        // validate request
        $rules = [
            'customer_id' => [Validator::REQUIRED, Validator::INTEGER],
            'address' => [Validator::TEXT, Validator::MAX_LENGTH => 70],
            'city' => [Validator::TEXT, Validator::MAX_LENGTH => 40],
            'state' => [Validator::TEXT, Validator::MAX_LENGTH => 40],
            'country' => [Validator::TEXT, Validator::MAX_LENGTH => 40],
            'postal_code' => [Validator::TEXT, Validator::MAX_LENGTH => 10],
            'tracks' => [Validator::REQUIRED, Validator::ARRAY]
        ];

        $validator = new Validator();
        $validator->validate($data, $rules);

        if ($validator->error()) {
            // invalid request
            return Response::badRequest($validator->error());
        }

        // check tracks array items if valid
        // check if any tracks
        if (empty($data['tracks'])) {
            // no tracks provided
            return Response::badRequest(['no tracks provided']);
        }

        $tracks = array();
        foreach ($data['tracks'] as $key => $value) {
            if (is_array($value)) {
                // validate track
                $rules = [
                    'id' => [Validator::REQUIRED, Validator::INTEGER]
                ];
                $validator = new Validator();
                $validator->validate($value, $rules);

                if ($validator->error()) {
                    // track invalid formatted
                    return Response::badRequest(['id (track id) required, integer']);
                } else {
                    $tracks[] = $value['id'];
                }
            } else {
                // tracks does not contain array eg json {id:value}
                return Response::badRequest(['tracks must be an array containing track ids']);
                break;
            }
        }

        // tracks provided and valid
        // get total of tracks
        $total = $this->customerRepo->calculateTracksTotal($tracks);

        // map to db names
        $invoice = [
            'CustomerId' => $data['customer_id'],
            'invoiceDate' => date("Y-m-d H:i:s"), // gives current in format: YYYY-MM-dd HH:mm:ss
            'Total' => $total['total'],
            'BillingAddress' => isset($data['address']) ? $data['address'] : null,
            'BillingCity' => isset($data['city']) ? $data['city'] : null,
            'BillingState' => isset($data['state']) ? $data['state'] : null,
            'BillingCountry' => isset($data['country']) ? $data['country'] : null,
            'BillingPostalCode' => isset($data['postal_code']) ? $data['postal_code'] : null
        ];


        // create invoice
        $invoiceId = $this->customerRepo->createCustomerInvoice($tracks, $invoice);

        if ($invoiceId == -1) {
            // error
            return Response::serverError('Error happend, please try again.');
        } else {
            // success, return created invoice
            $invoice = $this->customerRepo->findInvoice($invoiceId);
            $invoice['invoicelines'] = $this->customerRepo->findInvoicelinesByInvoiceId($invoiceId);
            return Response::success($invoice);
        }
    }

    private function updateCustomer($id, $data): Response
    {
        // validate request
        $rules = [
            'first_name' => [Validator::TEXT, Validator::MAX_LENGTH => 40],
            'last_name' => [Validator::TEXT, Validator::MAX_LENGTH => 20],
            'email' => [Validator::EMAIL, Validator::MAX_LENGTH => 60],
            'password' => [Validator::TEXT, Validator::MAX_LENGTH => 255],
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

        $data = $validator->data();

        // request valid
        // check if password is updated
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // create db entity
        $customer = array();
        isset($data['first_name']) ? $customer['FirstName'] = $data['first_name'] : null;
        isset($data['last_name']) ? $customer['LastName'] = $data['last_name'] : null;
        isset($data['email']) ? $customer['Email'] = $data['email'] : null;
        isset($data['password']) ? $customer['Password'] = $data['password'] : null;
        isset($data['phone']) ? $customer['Phone'] = $data['phone'] : null;
        isset($data['fax']) ? $customer['Fax'] = $data['fax'] : null;
        isset($data['company']) ? $customer['Company'] = $data['company'] : null;
        isset($data['address']) ? $customer['Address'] = $data['address'] : null;
        isset($data['city']) ? $customer['City'] = $data['city'] : null;
        isset($data['state']) ? $customer['State'] = $data['state'] : null;
        isset($data['postal_code']) ? $customer['PostalCode'] = $data['postal_code'] : null;
        isset($data['country']) ? $customer['Country'] = $data['country'] : null;

        if (empty($customer)){
            // all fields provided where set to null, and null values are removed
            return Response::badRequest(['No fields provided']);
        }

        // update customer
        $isSuccessful = $this->customerRepo->updateCustomer($id, (array)$customer);

        if (!$isSuccessful) {
            // failed due to FK constraint
            return Response::conflictFkFails();
        } else {
            // update success, send back updated resource
            $customer = $this->customerRepo->find($id);
            $customer = EntityMapper::toJsonCustomer($customer);
            return Response::success($customer);
        }
    }

    private function deleteCustomer(int $id): Response
    {
        if (!$this->customerRepo->deleteCustomer($id)) {
            // error, integrity violation
            return Response::conflictFkFails();
        } else {
            // delete success, clear session
            SessionHandler::startSession();
            SessionHandler::clear();
            return Response::okNoContent();
        }
    }
}