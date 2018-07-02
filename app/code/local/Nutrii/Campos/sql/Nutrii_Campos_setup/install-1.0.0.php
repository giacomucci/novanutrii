<?php
$this->addAttribute('customer', 'flag_protheus', array(
    'type'      => 'timestamp',
    'label'     => 'Flag Protheus',
    'input'     => 'text',
    'required'  => false,//or true
    'is_system' => 0,
));
$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'flag_protheus');
$attribute->setData('used_in_forms', array(
    'adminhtml_customer',
));
$attribute->setData('is_user_defined', 0);
$attribute->save();