<?php

namespace Undf\ZoozPaymentBundle\Model;

interface PaymentManagerInterface
{
    public function update($payment);

    public function handlePaymentSuccess(PaymentInterface $payment);

    public function handlePaymentFail(PaymentInterface $payment);
}
