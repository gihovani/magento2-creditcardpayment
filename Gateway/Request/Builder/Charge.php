<?php

namespace Gg2\CreditCardPayment\Gateway\Request\Builder;

use Gg2\CreditCardPayment\Gateway\SubjectReader;
use Gg2\CreditCardPayment\Helper\Data;
use Gg2\CreditCardPayment\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

class Charge implements BuilderInterface
{
    use Formatter;
    const AMOUNT = 'amount';
    const TYPE = 'type';
    const TOTAL_WITH_INTEREST = 'totalWithInterest';
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Charge constructor.
     * @param SubjectReader $subjectReader
     * @param Data $helper
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
        $amount = $this->subjectReader->readAmount($buildSubject);
        $paymentDataObject = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $interestAmount = $payment->getAdditionalInformation(DataAssignObserver::INTEREST_AMOUNT);
        $total = $amount + $interestAmount;
        return [
            self::TYPE                => 'creditcard',
            self::AMOUNT              => $this->formatPrice($amount),
            self::TOTAL_WITH_INTEREST => $this->formatPrice($total)
        ];
    }
}
