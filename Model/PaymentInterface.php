<?php

namespace Undf\ZoozPaymentBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

interface PaymentInterface
{
    const STATUS_PENDING = 'Pending';
    const STATUS_TPC_PENDING = 'TPCPending';
    const STATUS_SUCCEED = 'Succeed';
    const STATUS_FAILED = 'Failed';

    public function getAmount();

//    public function setCurrencyCode($currency);

    public function getCurrencyCode();

//    public function setUserId($id);

    public function getUserId();

//    public function setUserEmail($email);

    public function getUserEmail();

//    public function setUserFirstname($firstname);

    public function getUserFirstname();

//    public function setUserLastname($lastname);

    public function getUserLastname();

//    public function setInvoiceItemCollection(ArrayCollection $collection);

    public function getInvoiceItemCollection();

//    public function addInvoiceItemCollection(InvoiceItemInterface $item);

    public function setTransactionToken($token);

    public function getTransactionToken();

    public function setTransactionId($transactionId);

    public function getTransactionId();

    public function setTransactionStatus($status);

    public function getTransactionStatus();
}
