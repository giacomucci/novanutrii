<?php
class Ernet_Superpay_Block_Superpaybanco_Redirect extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
	
		Mage::log('### Superpay_Block_Superpaybanco_Redirect _toHtml() called.',null,'ernet.log');
		$bankurl = $_SESSION["superpay_bankurl"];

		$html.= '<iframe src="'.$bankurl.'" width="100%" height="800" frameborder="0" />';

        return $html;
    }
}