<?php

namespace Gg2\CreditCardPayment\Gateway\Request\Builder;

use Gg2\CreditCardPayment\Gateway\SubjectReader;
use Gg2\CreditCardPayment\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\InfoInterface;

class Payment implements BuilderInterface
{
    const TYPE_NAME = 'creditCard';
    const CREDITCARD_TYPE = 'type';
    const CREDITCARD_NUMBER = 'number';
    const CREDITCARD_EXPIRATION_DATE = 'expirationDate';
    const CREDITCARD_CODE = 'code';
    const CREDITCARD_OWNER = 'owner';
    const CREDITCARD_INSTALLMENTS = 'installments';
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Payment constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        return [
            self::TYPE_NAME => [
                self::CREDITCARD_TYPE            => $payment->getAdditionalInformation(DataAssignObserver::CC_TYPE),
                self::CREDITCARD_NUMBER          => $payment->getAdditionalInformation(DataAssignObserver::CC_NUMBER),
                self::CREDITCARD_EXPIRATION_DATE => $this->getCardExpirationDate($payment),
                self::CREDITCARD_CODE            => $payment->getAdditionalInformation(DataAssignObserver::CC_CID),
                self::CREDITCARD_OWNER           => $payment->getAdditionalInformation(DataAssignObserver::CC_OWNER),
                self::CREDITCARD_INSTALLMENTS    => $payment->getAdditionalInformation(DataAssignObserver::CC_INSTALLMENTS)
            ]
        ];
    }

    /**
     * @param InfoInterface $payment
     * @return string
     */
    private function getCardExpirationDate(InfoInterface $payment)
    {
        return sprintf(
            '%04d-%02d',
            $payment->getAdditionalInformation(DataAssignObserver::CC_EXP_YEAR),
            $payment->getAdditionalInformation(DataAssignObserver::CC_EXP_MONTH)
        );
    }
}
