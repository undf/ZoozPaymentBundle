<?php
namespace Undf\ZoozPaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Undf\ZoozPaymentBundle\Model\PaymentInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class BasePayment implements PaymentInterface
{

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    protected $amount = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="currencyCode", type="string", length=5, nullable=true)
     */
    protected $currencyCode = 'EUR';

    /**
     * @var FOS\UserBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="FOS\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(name="transactionToken", type="text", nullable=true)
     */
    protected $transactionToken;

    /**
     * @var string
     *
     * @ORM\Column(name="transactionId", type="text", nullable=true)
     */
    protected $transactionId;

    /**
     * @var string
     *
     * @ORM\Column(name="transactionStatus", type="string", length=20, nullable=true)
     */
    protected $transactionStatus;

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;
    }

    /**
     * @param \FOS\UserBundle\Model\UserInterface $user
     */
    public function setUser(\FOS\UserBundle\Model\UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return \FOS\UserBundle\Model\UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getTransactionToken()
    {
        return $this->transactionToken;
    }

    /**
     * @param string $transactionToken
     */
    public function setTransactionToken($transactionToken)
    {
        $this->transactionToken = $transactionToken;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return string
     */
    public function getTransactionStatus()
    {
        return $this->transactionStatus;
    }

    /**
     * @param string $transactionStatus
     */
    public function setTransactionStatus($transactionStatus)
    {
        $this->transactionStatus = trim($transactionStatus);
    }

    /**
     * @return integer
     */
    public function getUserId()
    {
        if($this->user) {
            return $this->user->getId();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getUserEmail()
    {
        if($this->user) {
            return $this->user->getEmail();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getUserFirstname()
    {
        if($this->user) {
            return $this->user->getFirstname();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getUserLastname()
    {
        if($this->user) {
            return $this->user->getLastname();
        }
        return null;
    }

    public function getInvoiceItemCollection()
    {
        return new \Doctrine\Common\Collections\ArrayCollection;
    }

}
