<?php
class Oitoo_Regrasdefrete_Model_Mysql4_Regras extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("regrasdefrete/regras", "id_regra");
    }
}