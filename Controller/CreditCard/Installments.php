<?php

namespace Gg2\CreditCardPayment\Controller\CreditCard;

use Gg2\CreditCardPayment\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Payment\Helper\Formatter;

class Installments extends Action
{
    use Formatter;
    /**
     * @var Context
     */
    private $context;
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var Data
     */
    private $helper;

    public function __construct(Context $context, JsonFactory $jsonFactory, Data $helper)
    {
        parent::__construct($context);
        $this->context = $context;
        $this->jsonFactory = $jsonFactory;
        $this->helper = $helper;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        $total = (float)$this->getRequest()->getParam('total', 0);
        $maxInstallments = $this->helper->getMaxNumberInstalmentsForAmount($total);
        $minInstallmentValue = $this->helper->getMinInstallmentValue();

        $installments = [];
        foreach (range(1, $maxInstallments) as $number) {
            $totalWithInterest = $this->helper->calculateTotalWithInterest($total, $number);
            $installmentValue = $totalWithInterest / $number;
            if (($installmentValue < $minInstallmentValue) && ($number > 1)) {
                continue;
            }
            $installments[] = [
                'id'            => $number,
                'value'         => $this->formatPrice($installmentValue),
                'total'         => $this->formatPrice($totalWithInterest),
                'interest_rate' => ($totalWithInterest > $total) ? (float)$this->helper->getInterestRate() : 0
            ];
        }

        return $result->setData($installments);
    }
}
