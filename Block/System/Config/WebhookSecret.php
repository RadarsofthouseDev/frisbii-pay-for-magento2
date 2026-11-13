<?php

namespace Radarsofthouse\Reepay\Block\System\Config;

class WebhookSecret extends Webhook
{
    /**
     * @var string
     */
    protected $_template = 'Radarsofthouse_Reepay::system/config/webhook_secret.phtml';

    /**
     * Return ajax url for secret synchronization.
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('radarsofthouse_reepay/system_config/webhookSecret');
    }

    /**
     * Generate sync button html.
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'webhook_secret_button',
                'label' => __('Sync Webhook Secrets'),
            ]
        );

        return $button->toHtml();
    }
}
