<?php


class Ernet_Superpay_Model_Superpay_Source_PaymentBank
{
    public function toOptionArray()
    {
	$banks = Mage::getModel('superpay/superpaybanco')->getBanks();
	$result = array();
	foreach ($banks as $key=>$value) {
		$result[] =  array(
        	        'value' => $key,
	                'label' => Mage::helper('superpay')->__("[$key] ".$value)
	            );
	}
	return $result;
    }
}
