<?php
class Oitoo_Regrasdefrete_Model_Convert_Adapter_Io extends Mage_Dataflow_Model_Convert_Adapter_Io
{
    public function load()
    {
        //apaga todas as linhas antes de importar
        $regras = mage::getModel('regrasdefrete/regras')->getResourceCollection();
        foreach($regras as $regra){
            $regra->delete();
        }
        parent::load();
    }
}