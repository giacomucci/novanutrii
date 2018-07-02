<?php
class Ernet_Superpay_Model_Superpay_Source_PaymentViewAction
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => '1',
                'label' => Mage::helper('superpay')->__('Inline')
            ),
            array(
                'value' => '2',
                'label' => Mage::helper('superpay')->__('Popup')
            ),
        );
    }
}
