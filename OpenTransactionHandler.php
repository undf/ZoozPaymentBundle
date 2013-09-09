<?php

namespace Undf\ZoozPaymentBundle;

use Undf\ZoozPaymentBundle\Server;
use Undf\ZoozPaymentBundle\Model\PaymentInterface;
use Undf\ZoozPaymentBundle\Model\PaymentManagerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class OpenTransactionHandler
{
    /**
     * @var \Undf\ZoozPaymentBundle\Server
     */
    protected $paymentServer;

    /**
     * @var \Undf\ZoozPaymentBundle\Model\PaymentManagerInterface
     */
    protected $paymentManager;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $appId;

    /**
     * @var string
     */
    protected $sandboxMode;

    /**
     * @var string
     */
    protected $ajaxMode;

    /**
     * @var string
     */
    protected $language;

    public function __construct(PaymentManagerInterface $paymentManager, Server $paymentServer, RouterInterface $router, $appId, $sandboxMode, $ajaxMode, $defaultLanguage)
    {
        $this->paymentManager = $paymentManager;
        $this->paymentServer = $paymentServer;
        $this->router = $router;
        $this->appId = $appId;
        $this->sandboxMode = $sandboxMode;
        $this->ajaxMode = $ajaxMode;
        $this->language = $defaultLanguage;
    }

    /**
     * Start a transaction on the Zooz server for the given payment
     *
     * @param \Undf\ZoozPaymentBundle\PaymentInterface $payment
     * @throws \Undf\ZoozPaymentBundle\Exception\ZoozServerException If the Zooz server returns an error
     * @return \Undf\ZoozPaymentBundle\JsonResponse
     */
    public function openTransaction(PaymentInterface $payment)
    {
        $token = $this->paymentServer->openTransaction($payment);

        $payment->setTransactionToken($token);
        $this->paymentManager->update($payment);

        return new JsonResponse(array(
            'ajaxMode' => $this->ajaxMode,
            'token' => $token,
            'preferredLanguage' => $this->getLanguage(),
            'uniqueId' => $this->appId,
            'isSandbox'=> $this->sandboxMode,
            'returnUrl' => $this->router->generate('zooz_transaction_return', array(), true),
            'cancelUrl'=> $this->router->generate('zooz_transaction_cancel', array(), true)
        ));
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        $parts = explode('_', $this->language);
        return strtoupper($parts[0]);
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }


}