$installer = $this;
$installer->startSetup();
$entity = $installer->getEntityTypeId('customer');

if(!$installer->attributeExists($entity, 'flag_protheus')) {
    $installer->removeAttribute($entity, 'flag_protheus');
}

$installer->addAttribute($entity, 'flag_protheus', array(
        'type' => 'static',
        'label' => 'Flag Protheus',
        'input' => 'datetime',
        'visible' => TRUE,
	   'back_end
        'required' => FALSE,
        'default_value' => '0000-00-00 00:00:00',
        'adminhtml_only' => '1',
));

$forms = array(
    'adminhtml_customer',
    'customer_account_edit'
);
$attribute = Mage::getSingleton('eav/config')->getAttribute($installer->getEntityTypeId('customer'), 'flag_protheus');
$attribute->setData('used_in_forms', $forms);
$attribute->save();

$installer->endSetup();