<?php
/**
 * TransferFactory
 *
 * @copyright Copyright © 2019 GG2 Solucoes. All rights reserved.
 * @author    gihovani@gmail.com
 */

namespace Gg2\CreditCardPayment\Gateway\Http;


use Gg2\CreditCardPayment\Gateway\Converter\Converter;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;
    /**
     * @var Converter
     */
    private $converter;

    public function __construct(TransferBuilder $transferBuilder, Converter $converter)
    {
        $this->transferBuilder = $transferBuilder;
        $this->converter = $converter;
    }

    public function create(array $request)
    {
        $body = $this->converter->convert($request);
        return $this->transferBuilder->setBody($body)->build();
    }
}
