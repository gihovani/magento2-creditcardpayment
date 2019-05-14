<?php

namespace Gg2\CreditCardPayment\Gateway\Request\Builder;

use Gg2\CreditCardPayment\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Api\Data\OrderItemInterface;

class ProductItems implements BuilderInterface
{
    use Formatter;
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Address constructor.
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDataObject->getOrder();
        $items = [];
        /**
         * @var int $key
         * @var OrderItemInterface $item
         */
        foreach ($order->getItems() as $key => $item) {
            $items['item'][] = [
                'itemId'      => (string)$key,
                'name'        => substr($item->getName(), 0, 31),
                'description' => substr($item->getDescription(), 0, 255),
                'quantity'    => $item->getQtyOrdered(),
                'unitPrice'   => $this->formatPrice($item->getPrice())
            ];
        }
        return [
            'items' => $items
        ];
    }
}
