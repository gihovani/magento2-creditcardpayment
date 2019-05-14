<?php

namespace Gg2\CreditCardPayment\Plugin\Payment\Block;

use Gg2\CreditCardPayment\Gateway\Config\Config;
use Gg2\CreditCardPayment\Observer\DataAssignObserver;

class InfoPlugin
{
    private $labels = [
        DataAssignObserver::CC_INSTALLMENTS => 'Número de Parcelas',
        DataAssignObserver::CC_TYPE         => 'Bandeira',
        DataAssignObserver::CC_OWNER        => 'Nome do Proprietário',
        DataAssignObserver::CC_NUMBER       => 'Número do Cartão',
        DataAssignObserver::CC_LAST_4       => 'Final do Cartão',
        DataAssignObserver::CC_CID          => 'Código de Segurança',
        DataAssignObserver::CC_EXP_MONTH    => 'Mês Vencimento',
        DataAssignObserver::CC_EXP_YEAR     => 'Ano Vencimento'
    ];
    private $values = [
        DataAssignObserver::CC_TYPE => [
            'AE'  => 'American Express',
            'VI'  => 'Visa',
            'MC'  => 'MasterCard',
            'DI'  => 'Discover',
            'JCB' => 'JCB',
            'CUP' => 'Unionpay',
            'DN'  => 'Diners Club',
            'MI'  => 'Maestro'
        ]
    ];

    /**
     * @param \Magento\Payment\Block\Info $subject
     * @param $result
     * @return mixed
     */
    public function afterGetSpecificInformation(\Magento\Payment\Block\Info $subject, $result)
    {
        if (Config::CODE === $subject->getData('methodCode')) {
            if (isset($result[DataAssignObserver::CC_LAST_4])) {
                $result[DataAssignObserver::CC_LAST_4] = '************' . $result[DataAssignObserver::CC_LAST_4];
            }
            foreach ($this->labels as $key => $label) {
                if (array_key_exists($key, $result)) {
                    $value = $result[$key];
                    if (isset($this->values[$key][$value])) {
                        $value = $this->values[$key][$value];
                    }
                    $result[$label] = $value;
                    unset($result[$key]);
                }
            }
        }
        return $result;
    }
}
