<?php

namespace Gg2\CreditCardPayment\Helper;

use Gg2\CreditCardPayment\Gateway\Config\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const KEY_ACTIVE = 'active';
    const MAX_NUMBER_INSTALMENTS = 'max_number_instalments';
    const MAX_NUMBER_INSTALMENTS_WITH_INTEREST = 'max_number_instalments_with_interest';
    const MIN_INSTALLMENT_VALUE = 'min_installment_value';
    const INTEREST_RATE = 'interest_rate';
    const CCTYPES = 'cctypes';

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            sprintf(Config::DEFAULT_PATH_PATTERN, Config::CODE, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isActive($storeId = null): bool
    {
        return (bool)$this->getConfigValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnabledInterestRate($storeId = null): bool
    {
        return (bool)$this->getConfigValue(self::ENABLE_INTEREST_RATE, $storeId);
    }

    /**
     * @return int
     */
    public function getMaxNumberInstalments(): int
    {
        return (int)$this->getConfigValue(self::MAX_NUMBER_INSTALMENTS);
    }

    /**
     * @return int
     */
    public function getMaxNumberInstalmentsWithInterest(): int
    {
        return (int)$this->getConfigValue(self::MAX_NUMBER_INSTALMENTS_WITH_INTEREST);
    }

    /**
     * @param int $amount
     * @return int
     */
    public function getMaxNumberInstalmentsForAmount($amount = 0): int
    {
        $max = floor($amount / $this->getMinInstallmentValue());
        if ($max < 1) {
            $max = 1;
        }
        return ($this->getInterestRate()) ?
            min($max, $this->getMaxNumberInstalmentsWithInterest()) :
            min($max, $this->getMaxNumberInstalments());
    }

    /**
     * @return float
     */
    public function getMinInstallmentValue(): float
    {
        return (float)$this->getConfigValue(self::MIN_INSTALLMENT_VALUE);
    }

    /**
     * @return float
     */
    public function getInterestRate(): float
    {
        return (float)$this->getConfigValue(self::INTEREST_RATE);
    }

    /**
     * @param $total
     * @param $installmentNumber
     * @return float
     */
    public function calculateTotalWithInterest($total, $installmentNumber): float
    {
        $interestRate = (float)$this->getInterestRate();
        if ($interestRate && ($installmentNumber > $this->getMaxNumberInstalments())) {
            $total = (((floatval($interestRate / 100) * floatval($total)) * $installmentNumber) + floatval($total));
        }
        return (float)$total;
    }

    /**
     * @return array
     */
    public function getAllowedCcTypes(): array
    {
        $ccTypes = $this->getConfigValue(self::CCTYPES);
        return explode(',', $ccTypes);
    }
}
