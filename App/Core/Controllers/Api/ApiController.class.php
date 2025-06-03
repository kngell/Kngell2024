<?php

declare(strict_types=1);

class ApiController extends Controller
{
    public function __construct(private StripeApiGateway $apiGateway)
    {
    }

    public function index() : String
    {
        //curl_setopt($ch, CURLOPT_URL, "https://randomuser.me/api");
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // $this->apiGateway->get('https://api.agify.io', ['name' => $_GET['name']]);
        // if (! empty($_GET['name'])) {
        //     $api = $this->apiGateway->get('https://api.agify.io', ['name' => $_GET['name']]);
        //     $json = new JsonFile($api);

        //     $response = $json->getContentAsArray();
        //     return $this->render('index', [
        //         'response' => $response['age'],
        //     ]);
        // }
        $response = $this->apiGateway->post();
        $responseHeaders = $this->apiGateway->getResponseHeaders();
        $satusCode = $this->apiGateway->getStatusCode();
        // $json = new JsonFile($response);
        // $response = $json->getContentAsArray();
        return $this->render('index', [
            'response' => $response,
            'statusCode' => $satusCode,
            // 'statusCode' => $responseHeaders,
        ]);
    }

    public function call() : string
    {
        $data = $this->request->getPost()->getAll();

        return $this->render('index', [
            'response' => $data['name'],
        ]);
    }
}