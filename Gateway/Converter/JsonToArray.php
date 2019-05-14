<?php

namespace Gg2\CreditCardPayment\Gateway\Converter;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;

class JsonToArray implements ConverterInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * JsonToArray constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param mixed $response
     * @return array|bool|float|int|string|null
     */
    public function convert($response)
    {
        return $this->serializer->unserialize($response);
    }
}
