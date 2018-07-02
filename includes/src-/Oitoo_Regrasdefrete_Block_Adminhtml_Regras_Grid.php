<?php

class Oitoo_Regrasdefrete_Block_Adminhtml_Regras_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("regrasGrid");
				$this->setDefaultSort("id_regra");
				$this->setDefaultDir("ASC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("regrasdefrete/regras")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id_regra", array(
				"header" => Mage::helper("regrasdefrete")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id_regra",
				));
                
				$this->addColumn("titulo", array(
				"header" => Mage::helper("regrasdefrete")->__("Título"),
				"index" => "titulo",
				));



				$this->addColumn("peso_de", array(
				"header" => Mage::helper("regrasdefrete")->__("Peso a partir de"),
				"index" => "peso_de",
				));
				$this->addColumn("peso_ate", array(
				"header" => Mage::helper("regrasdefrete")->__("Peso até"),
				"index" => "peso_ate",
				));
				$this->addColumn("pais", array(
				"header" => Mage::helper("regrasdefrete")->__("País"),
				"index" => "pais",
				));
				$this->addColumn("cidade", array(
				"header" => Mage::helper("regrasdefrete")->__("Cidade"),
				"index" => "cidade",
				));
				$this->addColumn("cep_de", array(
				"header" => Mage::helper("regrasdefrete")->__("Cep à partir de"),
				"index" => "cep_de",
				));
				$this->addColumn("cep_ate", array(
				"header" => Mage::helper("regrasdefrete")->__("Cep até"),
				"index" => "cep_ate",
				));
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id_regra');
			$this->getMassactionBlock()->setFormFieldName('id_regras');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_regras', array(
					 'label'=> Mage::helper('regrasdefrete')->__('Remove Regras'),
					 'url'  => $this->getUrl('*/adminhtml_regras/massRemove'),
					 'confirm' => Mage::helper('regrasdefrete')->__('Are you sure?')
				));
			return $this;
		}
			
		static public function getOptionArray6()
		{
            $data_array=array(); 
			$data_array[0]='estado1';
            return($data_array);
		}
		static public function getValueArray6()
		{
            $data_array=array();
			foreach(Oitoo_Regrasdefrete_Block_Adminhtml_Regras_Grid::getOptionArray6() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		
		static public function getOptionArray10()
		{
            $data_array=array(); 
			$data_array[0]='default';
            return($data_array);
		}
		static public function getValueArray10()
		{
            $data_array=array();
			foreach(Oitoo_Regrasdefrete_Block_Adminhtml_Regras_Grid::getOptionArray10() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		
		static public function getOptionArray11()
		{
            $data_array=array(); 
			$data_array[0]='default';
            return($data_array);
		}
		static public function getValueArray11()
		{
            $data_array=array();
			foreach(Oitoo_Regrasdefrete_Block_Adminhtml_Regras_Grid::getOptionArray11() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		
		static public function getOptionArray12()
		{
            $data_array=array(); 
			$data_array[0]='default';
            return($data_array);
		}
		static public function getValueArray12()
		{
            $data_array=array();
			foreach(Oitoo_Regrasdefrete_Block_Adminhtml_Regras_Grid::getOptionArray12() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		

}