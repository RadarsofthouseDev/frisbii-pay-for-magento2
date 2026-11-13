<?php

namespace Radarsofthouse\Reepay\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Logger extends AbstractHelper
{
    const CONFIG_PATH = 'log_level';
    const REDACTED_VALUE = '[REDACTED]';

    /**
     * @var \Monolog\Logger
     */
    private $loggerLevel = \Monolog\Logger::EMERGENCY;

    /**
     * @var \Radarsofthouse\Reepay\Logger\Debug
     */
    private $debugLogger;

    /**
     * @var \Radarsofthouse\Reepay\Logger\Info
     */
    private $infoLogger;

    /**
     * @var \Radarsofthouse\Reepay\Logger\Error
     */
    private $errorLogger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param Data $dataHelper
     * @param Radarsofthouse\Reepay\Logger\Debug $debug
     * @param Radarsofthouse\Reepay\Logger\Info $info
     * @param Radarsofthouse\Reepay\Logger\Error $error
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        \Radarsofthouse\Reepay\Logger\Debug $debug,
        \Radarsofthouse\Reepay\Logger\Info $info,
        \Radarsofthouse\Reepay\Logger\Error $error,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->debugLogger = $debug;
        $this->infoLogger = $info;
        $this->errorLogger = $error;
        $this->loggerLevel = \Monolog\Logger::DEBUG;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Log debug
     *
     * @param string $message
     * @param array $context
     * @param boolean $isApi
     * @return void
     */
    public function addDebug($message, $context = [], $isApi = false)
    {
        if ($this->loggerLevel <= \Monolog\Logger::DEBUG) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
            $logConfig = $this->scopeConfig->getValue('payment/reepay_payment/log', $storeScope);
            $message = $this->sanitizeMessage($message);
            $context = $this->sanitizeContext($context, (int)$logConfig === 2);

            if ($logConfig == 1 && $isApi) {
                $this->debugLogger->debug($message, $context);
            } elseif ($logConfig == 2) {
                $this->debugLogger->debug($message, $context);
            }
        }
    }

    /**
     * Log info
     *
     * @param string $message
     * @param array $context
     * @param boolean $isApi
     * @return void
     */
    public function addInfo($message, $context = [], $isApi = false)
    {
        if ($this->loggerLevel <= \Monolog\Logger::INFO) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
            $logConfig = $this->scopeConfig->getValue('payment/reepay_payment/log', $storeScope);
            $message = $this->sanitizeMessage($message);
            $context = $this->sanitizeContext($context, (int)$logConfig === 2);

            if ($logConfig == 1 && $isApi) {
                $this->infoLogger->info($message, $context);
            } elseif ($logConfig == 2) {
                $this->infoLogger->info($message, $context);
            }
        }
    }

    /**
     * Log error
     *
     * @param string $message
     * @param array $context
     * @param boolean $isApi
     * @return void
     */
    public function addError($message, $context = [], $isApi = false)
    {
        if ($this->loggerLevel <= \Monolog\Logger::ERROR) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
            $logConfig = $this->scopeConfig->getValue('payment/reepay_payment/log', $storeScope);
            $message = $this->sanitizeMessage($message);
            $context = $this->sanitizeContext($context, (int)$logConfig === 2);

            if ($logConfig == 1 && $isApi) {
                $this->errorLogger->error($message, $context);
            } elseif ($logConfig == 2) {
                $this->errorLogger->error($message, $context);
            }
        }
    }

    /**
     * Remove request/response bodies and sensitive values before logging.
     *
     * @param mixed $context
     * @param bool $debugMode
     * @param string $parentKey
     * @return array
     */
    private function sanitizeContext($context, $debugMode = false, $parentKey = '')
    {
        if (!is_array($context)) {
            return [];
        }

        $sanitizedContext = [];
        foreach ($context as $key => $value) {
            $normalizedKey = $this->normalizeKey($key);
            if ($this->isOmittedKey($normalizedKey)) {
                if ($debugMode && $this->isStructuredDebugKey($normalizedKey) && is_array($value)) {
                    $sanitizedContext[$key] = $this->sanitizeContext($value, true, $normalizedKey);
                } elseif ($debugMode && $normalizedKey === 'exception_error' && is_scalar($value)) {
                    $sanitizedContext[$key] = $this->sanitizeMessage($value);
                }
                continue;
            }
            if ($this->isSessionReferenceKey($normalizedKey, $parentKey) && is_scalar($value)) {
                $sanitizedContext[$key] = $this->maskSessionField($normalizedKey, $value);
                continue;
            }
            if ($this->isCardReferenceKey($normalizedKey, $parentKey) && is_scalar($value)) {
                $sanitizedContext[$key] = $this->maskCardReference($normalizedKey, $value);
                continue;
            }
            if ($this->isSafeKey($normalizedKey) && (is_scalar($value) || $value === null)) {
                $sanitizedContext[$key] = $value;
                continue;
            }
            if ($this->isEmailKey($normalizedKey) && is_scalar($value)) {
                $sanitizedContext[$key] = $this->maskEmail($value);
                continue;
            }
            if ($this->isMessageKey($normalizedKey) && is_scalar($value)) {
                $sanitizedContext[$key] = $this->sanitizeMessage($value);
                continue;
            }
            if ($debugMode && $this->isDebugContainerKey($normalizedKey) && is_array($value)) {
                $sanitizedContext[$key] = $this->sanitizeContext($value, true, $normalizedKey);
                continue;
            }
            if ($this->isSafeCustomerHandle($normalizedKey, $value)) {
                $sanitizedContext[$key] = $value;
                continue;
            }
            if ($this->isSensitiveKey($normalizedKey)) {
                $sanitizedContext[$key] = self::REDACTED_VALUE;
                continue;
            }
            if (is_array($value)) {
                $sanitizedContext[$key] = $this->sanitizeContext($value, $debugMode, $normalizedKey);
                continue;
            }
            if (is_scalar($value) || $value === null) {
                $sanitizedContext[$key] = $value;
            }
        }

        return $sanitizedContext;
    }

    /**
     * @param mixed $key
     * @return string
     */
    private function normalizeKey($key)
    {
        $key = preg_replace('/([a-z])([A-Z])/', '$1_$2', (string)$key);
        return strtolower(str_replace(['-', ' '], '_', $key));
    }

    /**
     * @param string $key
     * @return bool
     */
    private function isOmittedKey($key)
    {
        return in_array(
            $key,
            [
                'body',
                'payload',
                'request',
                'response',
                'trace',
                'exception',
                'exception_error',
                'error',
                'errors',
                'response_errors',
            ],
            true
        );
    }

    /**
     * Keep structured API diagnostics in debug mode, while still sanitizing
     * every nested field.
     *
     * @param string $key
     * @return bool
     */
    private function isStructuredDebugKey($key)
    {
        return in_array($key, ['payload', 'request', 'response', 'errors', 'response_errors'], true);
    }

    /**
     * @param string $key
     * @return bool
     */
    private function isDebugContainerKey($key)
    {
        return in_array($key, ['charge', 'customer', 'order', 'order_lines', 'session'], true);
    }

    /**
     * Identifiers used to correlate payment activity are safe to retain.
     *
     * @param string $key
     * @return bool
     */
    private function isSafeKey($key)
    {
        return in_array(
            $key,
            [
                'order_id',
                'order_increment_id',
                'invoice',
                'invoice_id',
                'webhook_id',
                'event_id',
                'event_type',
                'handle',
                'transaction',
                'transaction_id',
                'session_id',
                'payment_method',
                'payment_methods',
                'is_reepay_payment_method',
                'payment_context',
                'http_errors',
            ],
            true
        );
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    private function isSafeCustomerHandle($key, $value)
    {
        return $key === 'customer'
            && is_scalar($value)
            && preg_match('/^cust-[a-z0-9_-]+$/i', (string)$value);
    }

    /**
     * @param string $key
     * @return bool
     */
    private function isEmailKey($key)
    {
        return $key === 'email' || substr($key, -6) === '_email';
    }

    /**
     * @param string $key
     * @return bool
     */
    private function isMessageKey($key)
    {
        return in_array($key, ['error_message', 'message'], true);
    }

    /**
     * Identify session fields by their context key rather than token prefix.
     *
     * @param string $key
     * @param string $parentKey
     * @return bool
     */
    private function isSessionReferenceKey($key, $parentKey)
    {
        if (in_array($key, ['session_id', 'active_url', 'parent_url', 'uri'], true)) {
            return true;
        }

        return $parentKey === 'response' && in_array($key, ['id', 'url'], true);
    }

    /**
     * @param string $key
     * @param string $parentKey
     * @return bool
     */
    private function isCardReferenceKey($key, $parentKey = '')
    {
        return in_array($key, ['card_id', 'recurring_payment_method'], true)
            || ($parentKey === 'cards' && $key === 'id');
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return string
     */
    private function maskCardReference($key, $value)
    {
        if ($key === 'card_id') {
            return $this->maskCardIdentifier($value);
        }

        return $this->maskPaymentMethodIdentifier($value);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return string
     */
    private function maskSessionField($key, $value)
    {
        if (in_array($key, ['active_url', 'parent_url', 'uri', 'url'], true)) {
            return $this->maskSessionUrl($value);
        }

        return $this->maskSessionIdentifier($value);
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function maskSessionIdentifier($value)
    {
        $value = (string)$value;
        return substr($value, 0, 10) . 'xxxxxxxxxx';
    }

    /**
     * Keep a card identifier prefix without exposing the full identifier.
     *
     * @param mixed $value
     * @return string
     */
    private function maskCardIdentifier($value)
    {
        $value = (string)$value;
        if ($value === '') {
            return self::REDACTED_VALUE;
        }

        return substr($value, 0, 5) . 'xxx';
    }

    /**
     * Keep payment method references consistent with session identifier logs.
     *
     * @param mixed $value
     * @return string
     */
    private function maskPaymentMethodIdentifier($value)
    {
        $value = (string)$value;
        return substr($value, 0, 10) . 'xxxxxxxxxx';
    }

    /**
     * Mask session identifiers embedded in known URL fields.
     *
     * @param mixed $value
     * @return string
     */
    private function maskSessionUrl($value)
    {
        $value = (string)$value;
        $value = preg_replace_callback(
            '/([?&](?:id|session_id|session)=)([^&#]+)/i',
            function ($matches) {
                return $matches[1] . $this->maskSessionIdentifier($matches[2]);
            },
            $value
        );
        $value = preg_replace_callback(
            '/([?&]payment_method=)([^&#]+)/i',
            function ($matches) {
                return $matches[1] . $this->maskPaymentMethodIdentifier($matches[2]);
            },
            $value
        );
        $value = preg_replace_callback(
            '/(#\/checkout\/|#\/|\/checkout\/)([^\/?&#]+)/i',
            function ($matches) {
                return $matches[1] . $this->maskSessionIdentifier($matches[2]);
            },
            $value
        );

        return $value === null ? (string)$value : $value;
    }

    /**
     * Keep a short email prefix and domain for log correlation.
     *
     * @param mixed $email
     * @return string
     */
    private function maskEmail($email)
    {
        $email = (string)$email;
        if (!preg_match('/^([^@\s]+)@([^@\s]+)$/', $email, $matches)) {
            return self::REDACTED_VALUE;
        }

        $localPart = $matches[1];
        $prefix = substr($localPart, 0, min(3, strlen($localPart)));
        if (strlen($localPart) > 3 && substr($localPart, 3, 1) === '_') {
            $prefix .= '_';
        }

        return $prefix . '***@' . $matches[2];
    }

    /**
     * @param string $key
     * @return bool
     */
    private function isSensitiveKey($key)
    {
        return (bool)preg_match(
            '/(^|_)(api_key|private_key|password|secret|signature|authorization|access_token|'
                . 'refresh_token|token|card|card_id|card_on_file|fingerprint|gw_ref|exp_date|cvv|cvc|email|phone|first_name|'
                . 'last_name|name|address|address2|company|customer|customer_id|payment_url|source|'
                . 'charge|session|comment|reference|search|order)(_|$)/',
            $key
        );
    }

    /**
     * @param mixed $message
     * @return string
     */
    private function sanitizeMessage($message)
    {
        $message = (string)$message;
        $sanitizedMessage = preg_replace(
            '/((?:api[_ -]?key|private[_ -]?key|authorization|signature|password|secret|token)'
                . '\s*[:=]\s*)\S+/i',
            '$1' . self::REDACTED_VALUE,
            $message
        );
        if ($sanitizedMessage === null) {
            $sanitizedMessage = $message;
        }
        $sanitizedMessage = preg_replace_callback(
            '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i',
            function ($matches) {
                return $this->maskEmail($matches[0]);
            },
            $sanitizedMessage
        );
        $sanitizedMessage = preg_replace(
            '/\b(?:\d[ -]?){13,19}\b/',
            self::REDACTED_VALUE,
            $sanitizedMessage
        );
        return $sanitizedMessage === null ? $message : $sanitizedMessage;
    }
}
