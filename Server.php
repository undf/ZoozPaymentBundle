<?php

namespace Undf\ZoozPaymentBundle;

use Undf\ZoozPaymentBundle\Model\PaymentInterface;
use Undf\ZoozPaymentBundle\Exception\ZoozServerException;

class Server
{

    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @var string
     */
    private $appKey;

    /**
     * @var string
     */
    private $responseType;
    private $sandboxMode;

    public function __construct($uniqueId, $appKey, $responseType, $sandboxMode = true)
    {
        $this->uniqueId = $uniqueId;
        $this->appKey = $appKey;
        $this->responseType = $responseType;
        $this->sandboxMode = $sandboxMode;
    }

    /**
     *
     * @param \Undf\ZoozPaymentBundle\Model\PaymentInterface $payment
     * @return type
     * @throws ZoozServerException
     */
    public function openTransaction(PaymentInterface $payment)
    {
        $postFields = 'cmd=openTrx';
        foreach ($this->mapPayment($payment) as $param => $value) {
            $postFields .= '&' . $param . '=' . $value;
        }

        $response = $this->sendRequest($postFields);

        //Parse result string to get the variables from the response:
        //$statusCode, $errorMessage and $sessionToken
        parse_str($response);

        if ($statusCode != 0) {
            throw new ZoozServerException(sprintf('Error to open transaction to ZooZ server: "%s"', isset($errorMessage) ? $errorMessage : 'Unknown error'));
        }
        // Return token from ZooZ server
        return rtrim($sessionToken, "\n");
    }

    /**
     * Verify the transaction to make sure transaction indeed succeeded.
     *
     * @param string $transactionId Is meant to be used with the ZooZ Extended Server API (See www.zooz.com for more details
     * @param string Status of the verified transaction
     */
    public function verifyTransaction($transactionId)
    {
        $postFields = 'cmd=verifyTrx';
        $postFields .= '&transactionID=' . $transactionId;

        $response = $this->sendRequest($postFields);
        //Parse result string to get the variables from the response:
        //$statusCode, $paymentStatus and $errorMessage
        parse_str($response);

        if ($statusCode != 0) {
            throw new ZoozServerException(sprintf('Error veryfing the transaction to ZooZ server: "%s"', isset($errorMessage) ? $errorMessage : 'Unknown error'));
        }
        return $paymentStatus;
    }

    /**
     * Send a request to the Zooz server
     * @param string $postFields
     * @return string Zooz server response
     */
    protected function sendRequest($postFields)
    {
        $remoteSession = $this->initRemoteSession();

        curl_setopt($remoteSession, CURLOPT_POSTFIELDS, $postFields);

        ob_start();
        curl_exec($remoteSession);
        $result = ob_get_contents();
        ob_end_clean();

        // Close the cURL resource, and free system resources
        curl_close($remoteSession);

        return $result;
    }

    protected function getZoozServerUrl()
    {
        if ($this->sandboxMode) {
            return 'https://sandbox.zooz.co/mobile/SecuredWebServlet';
        }
        return 'https://app.zooz.com/mobile/SecuredWebServlet';
    }

    /**
     * @return resource cUrl session
     * @throws \Exception
     */
    protected function initRemoteSession()
    {
        // is cURL installed yet?
        if (!function_exists('curl_init')) {
            throw new \Exception('Sorry cURL is not installed!');
        }

        // OK cool - then let's create a new cURL resource handle
        $resource = curl_init();

        // Now set some options
        // Set URL
        curl_setopt($resource, CURLOPT_URL, $this->getZoozServerUrl());

        //Header fields: ZooZUniqueID, ZooZAppKey, ZooZResponseType
        curl_setopt($resource, CURLOPT_HTTPHEADER, array(
            'ZooZUniqueID: ' . $this->uniqueId,
            'ZooZAppKey: ' . $this->appKey,
            'ZooZResponseType: ' . $this->responseType
        ));

        // If it is a post request
        curl_setopt($resource, CURLOPT_POST, 1);

        // Timeout in seconds
        curl_setopt($resource, CURLOPT_TIMEOUT, 10);

        // If you are experiencing issues recieving the token on the sandbox environment, please set this option
        if ($this->sandboxMode) {
            curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, 0);
        }

        return $resource;
    }

    protected function mapPayment(PaymentInterface $payment)
    {
        $map = array(
            'amount' => $payment->getAmount(),
            'currencyCode' => $payment->getCurrencyCode(),
            'user.idNumber' => $payment->getUserId(),
            'user.email' => $payment->getUserEmail(),
            'user.firstName' => $payment->getUserFirstname(),
            'user.lastName' => $payment->getUserLastname(),
        );
        foreach ($payment->getInvoiceItemCollection() as $key => $invoiceItem) {
            /* @var $invoiceItem \Undf\ZoozPaymentBundle\Model\InvoiceItemInterface */
            $map["invoice.items($key).id"] = $invoiceItem->getId();
            $map["invoice.items($key).name"] = $invoiceItem->getName();
            $map["invoice.items($key).quantity"] = $invoiceItem->getQuantity();
            $map["invoice.items($key).price"] = $invoiceItem->getPrice();
        }

        $this->validateMandatoryParams($map);
        return $map;
    }

    protected function validateMandatoryParams(array $mappedParams)
    {
        $mandatoryParams = array(
            'amount',
            'currencyCode'
        );
        foreach ($mandatoryParams as $mandatoryParam) {
            if (!$mappedParams[$mandatoryParam]) {
                throw new \Exception(sprintf('Missed mandatory param "%s" for the Zooz payment', $mandatoryParam));
            }
        }
    }

}
