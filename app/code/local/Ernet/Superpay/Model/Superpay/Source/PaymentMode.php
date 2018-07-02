<?php

class Ernet_Superpay_Model_Superpay_Source_PaymentMode
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'test',
                'label' => Mage::helper('superpay')->__('Homologação')
            ),
            array(
                'value' => 'live',
                'label' => Mage::helper('superpay')->__('Produção')
            ),
        );
    }
}
?>