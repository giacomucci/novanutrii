<?php
class Ernet_Superpay_Model_Superpay_Source_PaymentCcTypes
{
	protected $_cctype = NULL;

    public function toOptionArray()
    {
	Mage::log('## PaymentCCTypes::toOptionArray called.',null,'ernet.log');
	$cc_types = $this->getCCType();
	$result = array();
	foreach ($cc_types as $key=>$value) {
		$result[] =  array(
        	        'value' => $key,
	                'label' => Mage::helper('superpay')->__("[$key] ".$value)
	            );
	}
	return $result;
    }
	
	public function getCCType($id = NULL) {
        if ($this->_cctype == NULL) {
            $this->_cctype = array(
                120 => "Visa (Cielo)",
                121 => "MasterCard (Cielo)",
                122 => "American Express (Cielo)",
                123 => "ELO (Cielo)",
                124 => "Diners (Cielo)",
                125 => "Discover (Cielo)",
                126 => "Aura (Cielo)",
                127 => "JCB (Cielo)",
                129 => "Visa Electron (Cielo)",
                90 => "Visa (Komerci)",
                91 => "MasterCard (Komerci)",
                92 => "Diners (Komerci)",
                93 => "Hipercard (Komerci)",
                94 => "Hiper (Komerci)",
                204 => "Visa (Elavon)",
                205 => "MasterCard (Elavon)",
                206 => "Diners (Elavon)",
                207 => "Discover (Elavon)",
                270 => "Visa (GetNet)",
                271 => "MasterCard (GetNet)",
                350 => "Visa (Stone)",
                351 => "MasterCard (Stone)"
            );
        }
        if (!empty($id))
            return $this->_cctype[$id];
        else
            return $this->_cctype;
    }
}
?>
