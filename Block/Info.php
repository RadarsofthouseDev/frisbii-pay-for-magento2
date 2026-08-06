<?php

namespace Radarsofthouse\Reepay\Block;

use Magento\Payment\Block\Info as MagentoPaymentInfo;

class Info extends MagentoPaymentInfo
{
    /**
     * Override Magento\Payment\Block\Info
     *
     * @param null|\Magento\Framework\DataObject|array $transport
     * @return \Magento\Framework\DataObject $transport
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];
        if ($additionalInformation = $this->getInfo()->getAdditionalInformation()) {
            if (isset($additionalInformation['raw_details_info']['source_type']) &&
                !empty($additionalInformation['raw_details_info']['source_type'])
            ) {
                $data['Type'] = $additionalInformation['raw_details_info']['source_type'];
            }
            if (isset($additionalInformation['raw_details_info']['source_card_type']) &&
                !empty($additionalInformation['raw_details_info']['source_card_type'])
            ) {
                $data['Card type'] = $additionalInformation['raw_details_info']['source_card_type'];
            }
            if (isset($additionalInformation['raw_details_info']['source_masked_card']) &&
                !empty($additionalInformation['raw_details_info']['source_masked_card'])
            ) {
                $data['Card'] = $additionalInformation['raw_details_info']['source_masked_card'];
            }
            if (isset($additionalInformation['raw_details_info']['source_exp_date']) &&
                !empty($additionalInformation['raw_details_info']['source_exp_date'])
            ) {
                $data['Exp date'] = $additionalInformation['raw_details_info']['source_exp_date'];
            }
            if (isset($additionalInformation['raw_details_info']['state']) &&
                !empty($additionalInformation['raw_details_info']['state'])
            ) {
                $data['State'] = $additionalInformation['raw_details_info']['state'];
            }
            if (isset($additionalInformation['age_verification_required']) &&
                !empty($additionalInformation['age_verification_required'])
            ) {
                $key = __('Age verification required')->render();
                $value = $additionalInformation['age_verification_required'] . ' ' . __('Years') . '.';
                $data[$key] = $value;
            }
            if (isset($additionalInformation['age_verification_result']) &&
                !empty($additionalInformation['age_verification_result'])
            ) {
                $key = __('Age verification result')->render();
                if (is_numeric($additionalInformation['age_verification_result'])) {
                    $value = $additionalInformation['age_verification_result'] . ' ' . __('Years') . '.';
                } else {
                    $value = __($additionalInformation['age_verification_result']);
                }
                $data[$key] = $value;
            }
        }

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
