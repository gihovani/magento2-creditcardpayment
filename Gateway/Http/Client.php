<?php

namespace Gg2\CreditCardPayment\Gateway\Http;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class Client implements ClientInterface
{
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * Client constructor.
     * @param Logger $logger
     * @param ConverterInterface|null $converter
     */
    public function __construct(Logger $logger, ConverterInterface $converter = null)
    {
        $this->logger = $logger;
        $this->converter = $converter;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array|string
     * @throws ConverterException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $log = [];
        try {
            $request = $this->converter
                ? $this->converter->convert($transferObject->getBody()) : $transferObject->getBody();
            $log['request'] = $request;
        } catch (ConverterException $exception) {
            $this->logger->debug(['error' => $exception->getMessage()]);
            throw $exception;
        } finally {
            $this->logger->debug($log);
        }
        return $request;
    }
}
