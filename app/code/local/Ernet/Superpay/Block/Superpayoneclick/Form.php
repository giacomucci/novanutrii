<?php
class Ernet_Superpay_Block_Superpayoneclick_Form extends Mage_Payment_Block_Form_Cc
{
    protected function _construct()
    {
		parent::_construct();  
		$this->setTemplate('superpay/cc_oneclick.phtml');
		Mage::log('### Ernet_Superpay_Block_Superpayoneclick_Form contructed.',null,'ernet.log');
          
    }
}
