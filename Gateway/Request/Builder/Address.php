<?php

namespace Gg2\CreditCardPayment\Gateway\Request\Builder;


use Gg2\CreditCardPayment\Gateway\SubjectReader;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class Address implements BuilderInterface
{
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

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $result = [
            'billTo' => $this->getFormattedAddress($billingAddress)
        ];
        if ($shippingAddress instanceof AddressAdapterInterface) {
            $result['shipTo'] = $this->getFormattedAddress($shippingAddress);
        }
        return $result;
    }

    /**
     * @param AddressAdapterInterface $address
     * @return array
     */
    private function getFormattedAddress(AddressAdapterInterface $address)
    {
        return [
            'firstName' => $address->getFirstname(),
            'lastName'  => $address->getLastname(),
            'company'   => $address->getCompany(),
            'address'   => trim($address->getStreetLine1() . ' ' . $address->getStreetLine2()),
            'city'      => $address->getCity(),
            'state'     => $address->getRegionCode(),
            'zip'       => $address->getPostcode(),
            'country'   => $address->getCountryId()
        ];
    }
}
