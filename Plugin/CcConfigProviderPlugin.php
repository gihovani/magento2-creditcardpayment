<?php

namespace Gg2\CreditCardPayment\Plugin;

use Gg2\CreditCardPayment\Model\Source\Cctype;
use Magento\Framework\View\Asset\Repository;
use Magento\Payment\Model\CcConfigProvider;

class CcConfigProviderPlugin
{
    /**
     * @var Repository
     */
    private $repository;
    /**
     * @var Cctype
     */
    private $cctype;

    public function __construct(Repository $repository, Cctype $cctype)
    {
        $this->repository = $repository;
        $this->cctype = $cctype;
    }

    public function afterGetIcons(CcConfigProvider $subject, $result)
    {
        foreach ($this->cctype->getAllowedTypes() as $allowedType) {
            if (isset($result[$allowedType])) {
                continue;
            }
            $result[$allowedType] = [
                'url'    => $this->repository->getUrl('Gg2_CreditCardPayment::images/cc/' . strtolower($allowedType) . '.png'),
                'width'  => 46,
                'height' => 40
            ];
        }
        return $result;
    }
}
