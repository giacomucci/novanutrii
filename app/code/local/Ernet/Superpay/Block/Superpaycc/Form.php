<?php
class Ernet_Superpay_Block_Superpaycc_Form extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
		parent::_construct();  
		$this->setTemplate('superpay/cc.phtml');
		Mage::log('### Ernet_Superpay_Block_Superpaycc_Form contructed.',null,'ernet.log');
          
    }
}
