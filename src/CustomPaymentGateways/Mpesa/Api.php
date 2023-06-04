<?php
namespace App\CustomPaymentGateways\Mpesa;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;

class Api
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */

    private $sandbox_options = [
        "shortcode" => "174379",
        "passkey" => "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919",
        "api_host" => "https://sandbox.safaricom.co.ke",
        "consumer_key"=>"pRYr6zjq0i8L1zzwQDMLpZB3yPCkhMsc",
        "consumer_secret" => "Rf22BfZog0qQGlV9"
    ];

    private $prod_options = [
        "shortcode" => "693155",
        "passkey" => "60783cb7c6822d3ab77b47e54d95619b7f8082175cdd7c38a737c376054927e0",
        "api_host" => "https://api.safaricom.co.ke",
        "consumer_key"=>"FABcY1zfGPNMMngUmyWSaHKD4lwiZ1qC",
        "consumer_secret" => "6VvB7QIoVp71EAJB"
    ];

    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
        // print_r($options);
        // die;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest($method, array $fields)
    {
        $headers = [];

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return json_decode($response->getBody()->getContents());
    }

    public function getAuthToken(){

        $endPoint = $this->getApiEndpoint().'/oauth/v1/generate?grant_type=client_credentials';
        $options = $this->getOptions();

        $request = $this->messageFactory->createRequest("GET", $endPoint, [
            "Authorization" => "Basic " . base64_encode($options["consumer_key"] . ":" . $options["consumer_secret"]),
            "Content-Type" => "application/json"
        ]);

        $response = $this->client->send($request);

        return $response->getBody()->getContents();

    }

    public function stkPushCharge($params, $auth){

        $options = $this->getOptions();
        $timestamp = date("YmdHis");
        $ipnHost = $this->options['sandbox'] ? "https://5b2e-196-202-223-242.eu.ngrok.io" : "https://events.myuzinc.com";
        
        $requestBody = [
            "BusinessShortCode" => $options["shortcode"],
            "Password" => base64_encode($options["shortcode"].$options["passkey"].$timestamp),
            "Timestamp" => $timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $this->options['sandbox'] ? 1 : $params["amount"],
            "PartyA" => $params["MSISDN"],
            "PartyB" => $options["shortcode"],
            "PhoneNumber" => $params["MSISDN"],
            "CallBackURL" => $ipnHost."/mpesa/ipn",
            "AccountReference" => $params["ref"],
            "TransactionDesc" => $params["description"]
        ];
        
        $endPoint = $this->getApiEndpoint().'/mpesa/stkpush/v1/processrequest';
        $request = $this->messageFactory->createRequest("POST", $endPoint, [
            "Authorization" => "Bearer ".$auth->access_token,
            "Content-Type" => "application/json"
        ], json_encode($requestBody));

        $response = $this->client->send($request);

        return $response->getBody()->getContents();
    }

    public function getOptions(){
        return $this->options['sandbox'] ? $this->sandbox_options : $this->prod_options;
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ? $this->sandbox_options["api_host"] : $this->prod_options["api_host"];
    }
}
