<?php
$this->addAttribute('customer', 'flag_protheus', array(
    'type' => 'static',
        'label' => 'Flag Protheus',
        'input' => 'datetime',
        'visible' => TRUE,
	   'back_end
        'required' => FALSE,
        'default_value' => '0000-00-00 00:00:00',
        'adminhtml_only' => '1',
    'is_system' => 0,
));
$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'flag_protheus');
$attribute->setData('used_in_forms', array(
    'adminhtml_customer',
));
$attribute->setData('is_user_defined', 0);
$attribute->save();