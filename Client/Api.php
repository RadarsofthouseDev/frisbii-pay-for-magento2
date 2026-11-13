<?php

namespace Radarsofthouse\Reepay\Client;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class Api
{
    const BASE_URI = 'https://api.reepay.com/v1/';
    const TIMEOUT = 30;
    const VERSION = '1.0.0';

    /**
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * @var string
     */
    private $privateKey;

    /**
     * @var boolean
     */
    private $requestSuccessful = false;

    /**
     * @var string
     */
    private $httpError = '';

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var array
     */
    private $lastResponse = [];

    /**
     * @var array
     */
    private $lastRequest = [];

    /**
     * Index constructor
     */
    public function __construct()
    {
        $this->initClient();
    }

    /**
     * Initialize
     */
    private function initClient()
    {
        $this->client = new Client([
            'base_uri' => static::BASE_URI,
            RequestOptions::TIMEOUT => self::TIMEOUT,
            RequestOptions::VERIFY => true,
        ]);
        $this->lastResponse = [
            'headers' => null,
            'body' => null,
        ];
    }

    /**
     * Set private key
     *
     * @param string $privateKey
     */
    public function setPrivateKey($privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * Get last error
     *
     * @return bool
     */
    public function getHttpError()
    {
        return $this->httpError ?: false;
    }

    /**
     * Get array of errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get last response
     *
     * @return mixed
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Get last request
     *
     * @return mixed
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * Get last request status
     *
     * @return bool
     */
    public function success()
    {
        return $this->requestSuccessful;
    }

    /**
     * Perform a DELETE request
     *
     * @param string $apiKey
     * @param string $endpoint
     * @param array $args
     * @param int $timeout
     * @return mixed
     * @throws \Exception
     */
    public function delete($apiKey, $endpoint, $args = [], $timeout = self::TIMEOUT)
    {
        $this->setPrivateKey($apiKey);

        return $this->request('delete', $endpoint, $args, $timeout);
    }

    /**
     * Perform a GET request
     *
     * @param string $apiKey
     * @param string $endpoint
     * @param array $args
     * @param int $timeout
     * @return mixed
     * @throws \Exception
     */
    public function get($apiKey, $endpoint, $args = [], $timeout = self::TIMEOUT)
    {
        $this->setPrivateKey($apiKey);

        return $this->request('get', $endpoint, $args, $timeout);
    }

    /**
     * Perform a PATCH request
     *
     * @param string $apiKey
     * @param string $endpoint
     * @param array $args
     * @param int $timeout
     * @return mixed
     * @throws \Exception
     */
    public function patch($apiKey, $endpoint, $args = [], $timeout = self::TIMEOUT)
    {
        $this->setPrivateKey($apiKey);

        return $this->request('patch', $endpoint, $args, $timeout);
    }

    /**
     * Perform a POST request
     *
     * @param string $apiKey
     * @param string $endpoint
     * @param array $args
     * @param int $timeout
     * @return mixed
     * @throws \Exception
     */
    public function post($apiKey, $endpoint, $args = [], $timeout = self::TIMEOUT)
    {
        $this->setPrivateKey($apiKey);

        return $this->request('post', $endpoint, $args, $timeout);
    }

    /**
     * Perform a PUT request
     *
     * @param string $apiKey
     * @param string $endpoint
     * @param array $args
     * @param int $timeout
     * @return mixed
     * @throws \Exception
     */
    public function put($apiKey, $endpoint, $args = [], $timeout = self::TIMEOUT)
    {
        $this->setPrivateKey($apiKey);

        return $this->request('put', $endpoint, $args, $timeout);
    }

    /**
     * Perform an API request request
     *
     * @param string $verb
     * @param string $endpoint
     * @param array $args
     * @param int $timeout
     * @return mixed
     * @throws \Exception
     */
    private function request($verb, $endpoint, $args = [], $timeout = self::TIMEOUT)
    {
        $url = static::BASE_URI . $endpoint;
        $response = $this->prepareStateForRequest($verb, $endpoint, $url, $timeout);
        $responseContent = null;
        $options = [
            RequestOptions::AUTH => [$this->privateKey, 'password'],
            RequestOptions::HEADERS => [
                'User-Agent' => 'magento/ba460eaaf82cf719170e3365f63094c4',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Client-Version' => self::VERSION,
                'X-Magento-Version' => $this->getMagentoVersion(),
                'X-Magento-Edition' => $this->getMagentoEdition(),
            ],
        ];

        try {
            switch ($verb) {
                case 'post':
                    $options[RequestOptions::JSON] = $args;
                    $this->lastRequest['body'] = json_encode($args);
                    $responseContent = $this->client->post($url, $options);

                    break;
                case 'get':
                    $options[RequestOptions::QUERY] = $args;
                    $responseContent = $this->client->get($url, $options);

                    break;
                case 'delete':
                    $options[RequestOptions::QUERY] = $args;
                    $responseContent = $this->client->delete($url, $options);

                    break;
                case 'put':
                    $options[RequestOptions::JSON] = $args;
                    $this->lastRequest['body'] = json_encode($args);
                    $responseContent = $this->client->put($url, $options);

                    break;
            }

            $response = $this->requestSuccess($responseContent);
        } catch (RequestException $e) {
            $response = $this->requestError($e);
        }
        $formattedResponse = $this->formatResponse($response);

        return $formattedResponse;
    }

    /**
     * Request Success
     *
     * @param ResponseInterface $response
     * @return array
     */
    private function requestSuccess(ResponseInterface $response)
    {
        $this->requestSuccessful = true;
        $this->lastResponse['headers'] = $this->getHeadersAsArray($response);
        $this->lastResponse['headers']['http_code'] = $response->getStatusCode();
        $this->lastResponse['headers']['http_reason'] = $response->getReasonPhrase();
        $this->lastResponse['body'] = (string)$response->getBody()->getContents();

        return $this->lastResponse;
    }

    /**
     * Request Error
     *
     * @param RequestException $e
     * @return array
     */
    private function requestError(RequestException $e)
    {
        $this->requestSuccessful = false;
        $this->lastRequest['headers'] = $this->getHeadersAsArray($e->getRequest());
        if (in_array($e->getRequest()->getMethod(), ['POST', 'PUT'])) {
            $this->lastRequest['body'] = $e->getRequest()->getBody();
        }
        if ($e->hasResponse()) {
            $this->lastResponse['headers'] = $this->getHeadersAsArray($e->getResponse());
            $this->lastResponse['headers']['http_code'] = $e->getResponse()->getStatusCode();
            $this->lastResponse['headers']['http_reason'] = $e->getResponse()->getReasonPhrase();
            $this->lastResponse['body'] = (string)$e->getResponse()->getBody();
            $this->httpError = sprintf(
                '%d: %s',
                $e->getResponse()->getStatusCode(),
                $e->getResponse()->getReasonPhrase()
            );
            if (!empty($this->lastResponse['body'])) {
                $formattedBody = json_decode($this->lastResponse['body'], true);
                if (is_array($formattedBody) && array_key_exists('error', $formattedBody)) {
                    $httpStatus = isset($formattedBody['http_status']) && is_numeric($formattedBody['http_status'])
                        ? (int)$formattedBody['http_status']
                        : $e->getResponse()->getStatusCode();
                    $httpReason = isset($formattedBody['http_reason']) && is_scalar($formattedBody['http_reason'])
                        ? (string)$formattedBody['http_reason']
                        : $e->getResponse()->getReasonPhrase();

                    $this->httpError = sprintf('%d: %s', $httpStatus, $httpReason);
                    $this->errors = [
                        'request_id' => isset($formattedBody['request_id']) && is_scalar($formattedBody['request_id'])
                            ? (string)$formattedBody['request_id']
                            : '',
                        'code' => isset($formattedBody['code']) && is_scalar($formattedBody['code'])
                            ? (string)$formattedBody['code']
                            : '',
                        'error' => is_scalar($formattedBody['error']) ? (string)$formattedBody['error'] : '',
                        'message' => isset($formattedBody['message']) && is_scalar($formattedBody['message'])
                            ? (string)$formattedBody['message']
                            : '',
                    ];
                }
            }
        }

        return $this->lastResponse;
    }

    /**
     * Reset state prior to request
     *
     * @param string $verb
     * @param string $endpoint
     * @param string $url
     * @param int $timeout
     * @return array
     */
    private function prepareStateForRequest($verb, $endpoint, $url, $timeout)
    {
        $this->httpError = '';
        $this->errors = [];

        $this->requestSuccessful = false;

        $this->lastResponse = [
            'headers' => null, // array of details from curl_getinfo()
            'body' => null, // content of the response
        ];

        $this->lastRequest = [
            'method' => strtoupper($verb),
            'endpoint' => $endpoint,
            'url' => $url,
            'body' => '',
            'timeout' => $timeout,
        ];

        return $this->lastResponse;
    }

    /**
     * Parse header string and return array of headers
     *
     * @param object $headerString
     * @return array
     */
    private function getHeadersAsArray($headerString)
    {
        $headers = [];
        foreach ($headerString->getHeaders() as $name => $values) {
            $headers[$name] = implode(', ', $values);
        }

        return $headers;
    }

    /**
     *  Format Response
     *
     * @param array $response
     * @return bool|mixed
     */
    private function formatResponse($response)
    {
        if (!empty($response['body'])) {
            return json_decode($response['body'], true);
        }

        return false;
    }

    /**
     * Get Magento Version
     *
     * @return string
     */
    private function getMagentoVersion()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get(\Magento\Framework\App\ProductMetadataInterface::class);

        return $productMetadata->getVersion();
    }

    /**
     * Get Magento Edition
     *
     * @return string
     */
    private function getMagentoEdition()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get(\Magento\Framework\App\ProductMetadataInterface::class);

        return $productMetadata->getEdition();
    }
}
