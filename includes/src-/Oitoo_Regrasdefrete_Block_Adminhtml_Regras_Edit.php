<?php
	
class Oitoo_Regrasdefrete_Block_Adminhtml_Regras_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id_regra";
				$this->_blockGroup = "regrasdefrete";
				$this->_controller = "adminhtml_regras";
				$this->_updateButton("save", "label", Mage::helper("regrasdefrete")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("regrasdefrete")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("regrasdefrete")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("regras_data") && Mage::registry("regras_data")->getId() ){

				    return Mage::helper("regrasdefrete")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("regras_data")->getId()));

				} 
				else{

				     return Mage::helper("regrasdefrete")->__("Add Item");

				}
		}
}