<?php

namespace Gg2\CreditCardPayment\Gateway\Request\Builder;

use Gg2\CreditCardPayment\Gateway\SubjectReader;
use Gg2\CreditCardPayment\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Model\Order;

class Totals implements BuilderInterface
{
    use Formatter;
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Address constructor.
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
        $order = $paymentDataObject->getOrder();
        /** @var Order $orderModel */
        $orderModel = $payment->getOrder();

        $result = [
            'tax'      => [
                'amount' => $orderModel->getBaseTaxAmount()
            ],
            'duty'     => [
                'amount' => 0
            ],
            'shipping' => [
                'amount' => $orderModel->getBaseShippingAmount(),
                'name'   => $orderModel->getShippingMethod()
            ],
            'poNumber' => $order->getOrderIncrementId()
        ];
        if ($payment->getAdditionalInformation(DataAssignObserver::INTEREST_AMOUNT)) {
            $result['interest'] = [
                'rate'   => $payment->getAdditionalInformation(DataAssignObserver::INTEREST_RATE),
                'period' => $payment->getAdditionalInformation(DataAssignObserver::CC_INSTALLMENTS),
                'amount' => $payment->getAdditionalInformation(DataAssignObserver::INTEREST_AMOUNT)
            ];
        }
        return $result;
    }
}
