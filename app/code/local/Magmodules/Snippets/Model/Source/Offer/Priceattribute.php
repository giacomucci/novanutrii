<?php
/**
 * Magmodules.eu - http://www.magmodules.eu.
 *
 * NOTICE OF LICENSE
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.magmodules.eu/MM-LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magmodules.eu so we can send you a copy immediately.
 *
 * @category      Magmodules
 * @package       Magmodules_Richsnippets
 * @author        Magmodules <info@magmodules.eu>
 * @copyright     Copyright (c) 2017 (http://www.magmodules.eu)
 * @license       https://www.magmodules.eu/terms.html  Single Service License
 */

class Magmodules_Snippets_Model_Source_Offer_Priceattribute
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $exclude = array('price', 'tier_price');
        $entityTypeId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getEntityTypeId();
        $attributes = Mage::getModel('eav/entity_attribute')->getCollection()
            ->addFilter('entity_type_id', $entityTypeId)
            ->setOrder('attribute_code', 'ASC');

        foreach ($attributes as $attribute) {
            if ($attribute->getBackendType() == 'decimal') {
                if ($attribute->getFrontendLabel()) {
                    if (!in_array($attribute->getAttributeCode(), $exclude)) {
                        $options[] = array(
                            'value' => $attribute->getAttributeCode(),
                            'label' => $attribute->getFrontendLabel()
                        );
                    }
                }
            }
        }

        return $options;
    }

}