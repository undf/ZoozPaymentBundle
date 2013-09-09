<?php

namespace Undf\ZoozPaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Undf\ZoozPaymentBundle\Exception\ZoozServerException;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{

    /**
     * @Route("/return", name="zooz_transaction_return")
     */
    public function transactionReturnAction()
    {
        $request = $this->getRequest();

        $sessionToken = $request->query->get('sessionToken');
        $transactionId = $request->query->get('transactionID');

        $class = $this->container->getParameter('undf_zooz_payment.payment.entity');
        $repo = $this->getDoctrine()->getEntityManagerForClass($class)->getRepository($class);
        $payment = $repo->findOneByTransactionToken($sessionToken);
        if (!$payment) {
            throw new BadRequestHttpException(sprintf('Payment with token "%s" not found.', $sessionToken));
        }

        $paymentManager = $this->get($this->container->getParameter('undf_zooz_payment.payment.manager.service'));
        $server = $this->get('undf_zooz_payment.server');
        try {
            $transactionStatus = $server->verifyTransaction($transactionId);

            $payment->setTransactionId($transactionId);
            $payment->setTransactionStatus($transactionStatus);

            $paymentManager->handlePaymentSuccess($payment);
            $paymentManager->update($payment);

            return $this->redirect($this->generateUrl('zooz_transaction_success'));

        } catch (ZoozServerException $e) {

            $payment->setTransactionStatus(\Undf\ZoozPaymentBundle\Model\PaymentInterface::STATUS_FAILED);
            $paymentManager->handlePaymentFail($payment);
            $paymentManager->update($payment);

            return $this->redirect($this->generateUrl('zooz_transaction_error'), 400);
        }

    }

    /**
     * @Route("/cancel", name="zooz_transaction_cancel")
     */
    public function transactionCancelAction()
    {
        return $this->redirect($this->generateUrl('zooz_transaction_error'));
    }

    /**
     * @Route("/success", name="zooz_transaction_success")
     */
    public function transactionSuccessAction()
    {
        $template = $this->container->getParameter('undf_zooz_payment.success.template');
        return $this->render($template);
    }

    /**
     * @Route("/error", name="zooz_transaction_error")
     */
    public function transactionErrorAction()
    {
        $template = $this->container->getParameter('undf_zooz_payment.error.template');
        return $this->render($template);;
    }

}
