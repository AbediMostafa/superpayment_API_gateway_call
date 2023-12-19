<?php
require 'CurlPost.php';

class Payment
{
    /**
     * @var array $config
     *
     * Payment's config including API key
     */
    private $config;

    /**
     * The total price of the given line item in minor units (pence).
     *
     * @var int|mixed $minorUnitAmount
     */
    private $minorUnitAmount;

    /**
     * Cart Id
     *
     * @var int $cartId
     */
    private $cartId;

    /**
     * Curl order data
     *
     * @var array $data
     */
    private $data;

    /**
     * Curl payment data
     *
     * @var array $paymentData
     */
    private $paymentData;

    /**
     * Curl request header
     *
     * @var array $header
     */
    private $header;

    /**
     * The ID of the cash reward offer created as a result of this call.
     *
     * @var string cashbackOfferId
     */
    private $cashbackOfferId;


    /**
     * Id of order
     *
     * @var string $orderId
     */
    private $orderId;


    /**
     * @var string $currency
     */
    private $currency;

    public function __construct($minorUnitAmount, $cartId, $orderId, $currency = "GBP")
    {
        $this->config = require 'config.php';
        $this->minorUnitAmount = $minorUnitAmount;
        $this->cartId = $cartId;
        $this->orderId = $orderId;
        $this->currency = $currency;

        $this->setHeader()
            ->initData();
    }

    /**
     * Set curl request's header
     *
     * @return $this
     */
    public function setHeader()
    {
        $this->header = [
            "accept:application/json",
            "checkout-api-key:{$this->config['checkout_api_key']}",
            "content-type:application/json",
            "referer:{$this->config['referer']}",
        ];

        return $this;
    }

    /**
     * Initialize cart infos
     *
     * @return $this
     */
    public function initData()
    {
        $this->data = [
            'cart' => [
                'items' => [],
                'id' => strval($this->cartId),
            ],
            'minorUnitAmount' => $this->minorUnitAmount,
            'page' => 'Checkout',
            'output' => 'both',
            'test' => $this->config['test'],
        ];

        $this->paymentData = [
            "currency" => $this->currency,
            "cashbackOfferId" => '',
            "successUrl" => $this->config['success_url'],
            "cancelUrl" => $this->config['cancel_url'],
            "failureUrl" => $this->config['failure_url'],
            "minorUnitAmount" => $this->minorUnitAmount,
            "externalReference" => $this->orderId,
            "test" => $this->config['test']
        ];

        return $this;
    }

    /**
     * Adds item to the cart
     *
     * @param string $name The name of an item in the cart.
     * @param string $url The URL to your product detail page, for the given line item.
     * @param int $quantity The quantity of the given item in the cart.
     * @return static
     */
    public function addCartItems(string $name, string $url, int $quantity)
    {
        $this->data['cart']['items'][] = [
            'name' => $name,
            'url' => $url,
            'quantity' => $quantity,
            'minorUnitAmount' => $this->minorUnitAmount,
        ];

        return $this;
    }

    /**
     * Execute curl
     *
     * @return $this
     */
    public function send($redirect = true)
    {
        $curl = new CurlPost($this->config['offer_url']);

        try {
            // Offers
            $response = json_decode($curl($this->data, $this->header));
            $this->handleErrors($response);

            // Payment
            $this->paymentData['cashbackOfferId'] = $response->cashbackOfferId;
            $curl = new CurlPost($this->config['payment_url']);
            $paymentResponse = json_decode($curl($this->paymentData, $this->header));

            $this->handleErrors($paymentResponse);

            if ($redirect)
                header("Location: {$paymentResponse->redirectUrl}");

            return $paymentResponse;

        } catch (Throwable $ex) {
            die(sprintf('Http error %s with code %d', $ex->getMessage(), $ex->getCode()));
        }
    }

    /**
     * Handles possible errors
     *
     * @throws Exception
     */
    public function handleErrors($response)
    {
        if ($response->statusCode === 400 || $response->statusCode === 401)
            throw new \Exception($response->errorMessage, $response->statusCode);
    }
}