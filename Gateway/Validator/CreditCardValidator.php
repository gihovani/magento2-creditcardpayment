<?php

namespace Gg2\CreditCardPayment\Gateway\Validator;

use DateTime;
use Gg2\CreditCardPayment\Gateway\Request\Builder\Charge;
use Gg2\CreditCardPayment\Gateway\Request\Builder\Payment;
use Gg2\CreditCardPayment\Helper\Data;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Psr\Log\LoggerInterface;

class CreditCardValidator extends AbstractValidator
{
    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Data
     */
    private $helper;

    public function __construct(ResultInterfaceFactory $resultFactory, Data $helper, LoggerInterface $logger)
    {
        parent::__construct($resultFactory);
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $errorMessages = [];
        $errorCodes = [];
        $response = $validationSubject['response'];
        foreach ($this->getValidators() as $validator) {
            $validationResult = $validator($response);
            if (!$validationResult[0]) {
                $isValid = $validationResult[0];
                $errorMessages = array_merge($errorMessages, $validationResult[1]);
                $errorCodes = array_merge($errorCodes, $validationResult[2]);
            }
        }
        return $this->createResult($isValid, $errorMessages, $errorCodes);
    }

    /**
     * @return array
     */
    private function getValidators()
    {
        return [
            function ($response) {
                return $this->validateInstalments($response);
            },
            function ($response) {
                return $this->validateCcExpDate($response);
            },
            function ($response) {
                return $this->validateCcType($response);
            },
            function ($response) {
                return $this->validateCcNumber($response);
            },
            function ($response) {
                return $this->validateCcCode($response);
            }
        ];
    }

    private function validateCcCode($response)
    {
        $type = $this->getCreditCardCode($response);
        $len = strlen($type);
        return [
            (($len >= 3) && ($len <= 4)),
            [__('Credit Card Code Is Invalid.')],
            ['CcCodeNotValid']
        ];
    }

    private function validateCcType($response)
    {
        $type = $this->getCreditCardType($response);
        $ccTypes = $this->helper->getAllowedCcTypes();
        return [
            (in_array($type, $ccTypes)),
            [__('Credit Card Type Is Invalid.')],
            ['CcTypeNotValid']
        ];
    }

    private function validateCcExpDate($response)
    {
        $now = new DateTime();
        $expires = DateTime::createFromFormat('Y-m', $this->getCreditCardExpDate($response));
        $this->logger->debug('expdate: ' . $this->getCreditCardExpDate($response));
        $this->logger->debug('expires: ' . $expires->format('Y-m-d H:i:s') . ' now: ' . $now->format('Y-m-d H:i:s'));
        return [
            ($expires >= $now),
            [__('Credit Card Exp Date Is Invalid.')],
            ['CcExpDateNotValid']
        ];
    }

    private function validateCcNumber($response)
    {
        $validConfig = false;
        $type = $this->getCreditCardType($response);
        $number = $this->getCreditCardNumber($response);
        $len = strlen($number);
        $firstDigit = substr($number, 0, 1);
        $config = [
            'AURA' => ['len' => [16], 'firstDigit' => [5]],
            'ELO'  => ['len' => [16], 'firstDigit' => [3, 4, 5, 6]],
            'AE'   => ['len' => [15], 'firstDigit' => [3]],
            'VI'   => ['len' => [13, 16], 'firstDigit' => [4]],
            'MC'   => ['len' => [16], 'firstDigit' => [5]],
            'DN'   => ['len' => [14, 16], 'firstDigit' => [2, 3]],
        ];
        $hasConfig = (isset($config[$type])) ? $config[$type] : '';
        if ($hasConfig) {
            $validConfig = (in_array($len, $hasConfig['len']) && in_array($firstDigit, $hasConfig['firstDigit']));
        }
        return [
            ($validConfig && ($this->isLuhnAlgorithm($number))),
            [__('Credit Card Number Is Invalid.')],
            ['CcNumberNotValid']
        ];
    }

    private function isLuhnAlgorithm($number)
    {
        $number = strrev(preg_replace("/[^0-9]/", "", $number));
        $sum = 0;
        for ($i = 0; $i < strlen($number); $i++) {
            $digit = substr($number, $i, 1);

            // dobra os digitos nas posicoes par
            if ($i % 2 == 1) {
                $digit *= 2;
            }

            // Add digits of 2-digit numbers together
            if ($digit > 9) {
                $digit = ($digit % 10) + floor($digit / 10);
            }

            $sum += $digit;
        }

        // verifica o total se não tiver resto ta beleza
        return ($sum % 10 == 0);
    }

    private function validateInstalments($response)
    {
        $amount = $this->getTransactionValue($response, Charge::AMOUNT, 0);
        $instalments = $this->getCreditCardInstallments($response);
        $maxInstalments = $this->helper->getMaxNumberInstalmentsForAmount($amount);
        return [
            (($instalments > 0) && ($instalments <= $maxInstalments)),
            [__('Installment Payment Is Invalid.')],
            ['InstalmentsNotValid']
        ];
    }

    private function getTransactionValue($response, $field, $defaultValue = null)
    {
        $transaction = isset($response['transaction']) ? $response['transaction'] : [];
        return (isset($transaction[$field])) ? $transaction[$field] : $defaultValue;
    }

    private function getCreditCardData($response): array
    {
        return $this->getTransactionValue($response, Payment::TYPE_NAME);
    }

    private function getCreditCardInstallments($response): int
    {
        $creditCardData = $this->getCreditCardData($response);
        return (isset($creditCardData[Payment::CREDITCARD_INSTALLMENTS])) ?
            (int)$creditCardData[Payment::CREDITCARD_INSTALLMENTS] :
            1;
    }

    private function getCreditCardExpDate($response): string
    {
        $creditCardData = $this->getCreditCardData($response);
        return (isset($creditCardData[Payment::CREDITCARD_EXPIRATION_DATE])) ?
            $creditCardData[Payment::CREDITCARD_EXPIRATION_DATE] :
            '01-01';
    }

    private function getCreditCardType($response): string
    {
        $creditCardData = $this->getCreditCardData($response);
        return (isset($creditCardData[Payment::CREDITCARD_TYPE])) ?
            $creditCardData[Payment::CREDITCARD_TYPE] :
            '';
    }

    private function getCreditCardCode($response): string
    {
        $creditCardData = $this->getCreditCardData($response);
        return (isset($creditCardData[Payment::CREDITCARD_CODE])) ?
            $creditCardData[Payment::CREDITCARD_CODE] :
            '';
    }

    private function getCreditCardNumber($response): string
    {
        $creditCardData = $this->getCreditCardData($response);
        return (isset($creditCardData[Payment::CREDITCARD_NUMBER])) ?
            $creditCardData[Payment::CREDITCARD_NUMBER] :
            '';
    }
}
