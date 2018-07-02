<?php


class Oitoo_Regrasdefrete_Block_Adminhtml_Regras extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_regras";
	$this->_blockGroup = "regrasdefrete";
	$this->_headerText = Mage::helper("regrasdefrete")->__("Regras Manager");
	$this->_addButtonLabel = Mage::helper("regrasdefrete")->__("Add New Item");
	parent::__construct();
	
	}

}