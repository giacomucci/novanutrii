<?php

class Oitoo_Regrasdefrete_Model_Convert_Adapter_Regrasimport
    extends Mage_Dataflow_Model_Convert_Adapter_Abstract
{

    public function load() {

    }

    public function save() {

    }



    public function saveRow(array $importData)
    {
        mage::log($_REQUEST);
        $cidade     = $importData['cidade'];
        $estado     = $importData['estado'];
        $pais       = $importData['pais'];
        $valor      = $importData['valor'];
        $custo      = $importData['custo'];
        $cep_de     = $importData['cep_de'];
        $cep_ate    = $importData['cep_ate'];
        $peso_de    = $importData['peso_de'];
        $peso_ate   = $importData['peso_ate'];
        $titulo     = $importData['titulo'];
        $website    = $importData['website'];

        $regra = mage::getModel('regrasdefrete/regras');
        $regra->setData(array(
            'cidade'    => $cidade,
            'estado'    => $estado,
            'pais'      => $pais,
            'valor'     => $valor,
            'custo'     => $custo,
            'cep_de'    => $cep_de,
            'cep_ate'   => $cep_ate,
            'peso_de'   => $peso_de,
            'peso_ate'  => $peso_ate,
            'titulo'    => $titulo,
            'website'   => $website
        ));
        if($regra->save()){
            return true;
        } else {
            Mage::throwException('Ocorreu um erro ao importar a regra ' . $titulo);
            return false;
        }



        return true;

    }



}