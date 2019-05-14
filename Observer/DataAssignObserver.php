<?php

namespace Gg2\CreditCardPayment\Observer;

use Gg2\CreditCardPayment\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Payment;
use Psr\Log\LoggerInterface;

class DataAssignObserver extends AbstractDataAssignObserver
{
    const CC_NUMBER = 'cc_number';
    const CC_CID = 'cc_cid';
    const CC_TYPE = 'cc_type';
    const CC_EXP_MONTH = 'cc_exp_month';
    const CC_EXP_YEAR = 'cc_exp_year';
    const CC_INSTALLMENTS = 'cc_installments';
    const CC_OWNER = 'cc_owner';
    const CC_LAST_4 = 'cc_last_4';
    const INTEREST_RATE = 'interest_rate';
    const INTEREST_AMOUNT = 'interest_amount';

    /**
     * @var array
     */
    private $additionalInformationList = [
        self::CC_NUMBER, self::CC_CID, self::CC_TYPE, self::CC_EXP_MONTH, self::CC_EXP_YEAR,
        self::CC_INSTALLMENTS, self::CC_OWNER, self::CC_LAST_4
    ];
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * DataAssignObserver constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper, LoggerInterface $logger)
    {
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }
        /** @var Payment $paymentInfo */
        $paymentInfo = $this->readPaymentModelArgument($observer);

        if ($additionalData[DataAssignObserver::CC_INSTALLMENTS]) {
            $this->setInterestInfo($paymentInfo, $additionalData);
        }
        if (isset($additionalData[self::CC_NUMBER])) {
            $this->setCcNumberLast4($paymentInfo, $additionalData);
        }
        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setData(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }

    private function setInterestInfo(Payment $paymentInfo, array $additionalData)
    {

        $quote = $paymentInfo->getQuote();
        $amount = $quote->getGrandTotal();
        $total = $this->helper->calculateTotalWithInterest($amount, $additionalData[DataAssignObserver::CC_INSTALLMENTS]);
        if ($amount < $total) {
            $interestAmount = $total - $amount;
            $paymentInfo->setAdditionalInformation(
                self::INTEREST_RATE,
                $this->helper->getInterestRate()
            );
            $paymentInfo->setAdditionalInformation(
                self::INTEREST_AMOUNT,
                $interestAmount
            );
        }
    }

    private function setCcNumberLast4(Payment $paymentInfo, array $additionalData)
    {
        $additionalData[self::CC_LAST_4] = substr($additionalData[self::CC_NUMBER], -4);
        $ccNumber = $paymentInfo->encrypt($additionalData[self::CC_NUMBER]);
        $paymentInfo->setCcNumberEnc($ccNumber);
    }
}
