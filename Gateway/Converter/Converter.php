<?php

namespace Gg2\CreditCardPayment\Gateway\Converter;

use Magento\Payment\Gateway\Http\ConverterInterface;

class Converter
{
    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * Converter constructor.
     * @param ConverterInterface $converter
     */
    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @param array $request
     * @return array|string
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function convert(array $request)
    {
        return $this->converter->convert($request);
    }
}
