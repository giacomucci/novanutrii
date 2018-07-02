<?php
class Oitoo_Regrasdefrete_Block_Adminhtml_Regras_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("regras_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("regrasdefrete")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("regrasdefrete")->__("Item Information"),
				"title" => Mage::helper("regrasdefrete")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("regrasdefrete/adminhtml_regras_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
