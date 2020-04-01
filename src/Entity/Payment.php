<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use MiPago\Bundle\Model\Payment as BasePayment;
use MiPago\Bundle\Model\PaymentInterface;

/**
 * @ORM\Table(name="payment")
 * @ORM\Entity(repositoryClass="App\Repository\PaymentRepository")
 */
class Payment extends BasePayment implements PaymentInterface
{
    const SOURCE_MIPAGO = 0;
    const SOURCE_OTHER = 1;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime")
     */
    protected $timestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="reference_number", type="string", length=12)
     */
    protected $referenceNumber;

    /**
     * Reference Number with control digits.
     *
     * @var string
     *
     * @ORM\Column(name="reference_number_dc", type="string", length=12, nullable=true)
     */
    protected $referenceNumberDC;

    /**
     * @var string
     *
     * @ORM\Column(name="suffix", type="string", length=3)
     */
    protected $suffix;

    /**
     * @var float
     *
     * @ORM\Column(name="quantity", type="decimal", precision=6, scale=2 )
     */
    protected $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="registered_payment_id", type="string", length=42, nullable=true, unique=true)
     */
    protected $registeredPaymentId;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=true)
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="status_message", type="string", nullable=true)
     */
    protected $statusMessage;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $nrc;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $operationNumber;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $entity;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $office;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentDate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentHour;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="surname_1", type="string", nullable=true)
     */
    protected $surname1;

    /**
     * @var string
     *
     * @ORM\Column(name="surname_2", type="string", nullable=true)
     */
    protected $surname2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", nullable=true)
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="nif", type="string", nullable=true)
     */
    protected $nif;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", nullable=true)
     */
    protected $address;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", nullable=true)
     */
    protected $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="territory", type="string", nullable=true)
     */
    protected $territory;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", nullable=true)
     */
    protected $country;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="response", type="string", length=4000, nullable=true)
     */
    protected $mipagoResponse;

    /**
     * @ORM\Column(type="integer")
     */
    protected $source = self::SOURCE_MIPAGO;

    public function createPaymentFromJson($json)
    {
        $payment_response = json_decode($json, true);
        $payment = new self();
        $payment->setSource(self::SOURCE_OTHER);
        $payment->setReference_number(str_pad($payment_response['reference_number'], 10, '0', STR_PAD_LEFT));
        $payment->setSuffix($payment_response['suffix']);
        $payment->setNif($payment_response['dni']);
        $payment->setQuantity($payment_response['Ds_Amount'] / 100);
        $datetime_str = $payment_response['Ds_Date'].' '.$payment_response['Ds_Hour'];
        $payment->setTimestamp(date_create_from_format('d/m/Y h:i', $datetime_str));
        $payment->setPaymentDate(str_replace('/', '', $payment_response['Ds_Date']));
        $payment->setPaymentHour(str_replace(':', '', $payment_response['Ds_Hour']));
        $payment->setResponse($json);
        $payment->setStatus(self::PAYMENT_STATUS_OK);

        return $payment;
    }

    public function setResponse($response)
    {
        $this->setMiPagoResponse($response);

        return $this;
    }

    public function getResponse()
    {
        return $this->getMiPagoResponse();
    }
}
