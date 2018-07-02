<?php

class Ernet_Superpay_Model_Superpay_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Ernet_Superpay_Model_Superpay::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('superpay')->__('Authorize and Capture')
            ),
            array(
                'value' => Ernet_Superpay_Model_Superpay::ACTION_AUTHORIZE,
                'label' => Mage::helper('superpay')->__('Authorize')
            ),
        );
    }
}
