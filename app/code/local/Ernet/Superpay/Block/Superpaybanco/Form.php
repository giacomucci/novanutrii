<?php

class Ernet_Superpay_Block_Superpaybanco_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $this->setTemplate('superpay/form.phtml');
        parent::_construct();
    }
}