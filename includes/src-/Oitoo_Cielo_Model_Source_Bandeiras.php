<?php
/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @title      Cielo pagamento com cartão de crédito (Brazil)
 * @category   payment
 * @package    Oitoo_Cielo
 * @copyright  Copyright (c) 2014 Oitoo (www.oitoo.com.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Oitoo <www.oitoo.com.br>
 */

class Oitoo_Cielo_Model_Source_Bandeiras
{
	public function toOptionArray ()
	{
		$options = array();
        
        $options[] = array('value' => 'visa',                'label' => Mage::helper('adminhtml')->__('Visa'));
        $options[] = array('value' => 'mastercard',          'label' => Mage::helper('adminhtml')->__('Mastercard'));
        $options[] = array('value' => 'diners',              'label' => Mage::helper('adminhtml')->__('Diners'));
        $options[] = array('value' => 'discover',            'label' => Mage::helper('adminhtml')->__('Discover'));
        $options[] = array('value' => 'elo',                 'label' => Mage::helper('adminhtml')->__('Elo'));
        $options[] = array('value' => 'amex',                'label' => Mage::helper('adminhtml')->__('Amex'));
        $options[] = array('value' => 'jcb',                 'label' => Mage::helper('adminhtml')->__('JCB'));
        $options[] = array('value' => 'aura',                'label' => Mage::helper('adminhtml')->__('Aura'));
        
		return $options;
	}

}
