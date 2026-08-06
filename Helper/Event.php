<?php

namespace Radarsofthouse\Reepay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Radarsofthouse\Reepay\Client\Checkout;

class Event extends AbstractHelper
{
    const ENDPOINT = 'event';

    /**
     * @var \Radarsofthouse\Reepay\Client\Checkout
     */
    protected $client = null;

    /**
     * @var \Radarsofthouse\Reepay\Helper\Logger
     */
    protected $logger = null;

    /**
     * constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Radarsofthouse\Reepay\Helper\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Radarsofthouse\Reepay\Helper\Logger $logger
    ) {
        parent::__construct($context);
        $this->client = new Checkout();
        $this->logger = $logger;
    }

    /**
     * Get event
     *
     * @param string $apiKey
     * @param string $sessionId
     * @return bool|mixed
     * @throws \Exception
     */
    public function get($apiKey, $sessionId)
    {
        $log = ['param' => ['session_id' => $sessionId]];
        $response = $this->client->get($apiKey, self::ENDPOINT . '/' . $sessionId);
        if ($this->client->success()) {
            $log ['response'] = $response;
            $this->logger->addInfo(__METHOD__, $log, true);

            return $response;
        } else {
            $log['http_errors'] = $this->client->getHttpError();
            $log['response_errors'] = $this->client->getErrors();
            $this->logger->addError(__METHOD__, $log, true);

            return false;
        }
    }
}
