<?php

namespace Gg2\CreditCardPayment\Gateway\Converter;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;

class ArrayToJson implements ConverterInterface
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
     * @return array|bool|string
     */
    public function convert($response)
    {
        return $this->serializer->serialize($response);
    }
}
