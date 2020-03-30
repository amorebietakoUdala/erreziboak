<?php

namespace App\Entity;

use MiPago\Bundle\Entity\Payment as BasePayment;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Payment extends BasePayment
{
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
