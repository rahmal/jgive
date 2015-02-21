<?php
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');
class jGiveViewCp extends JViewLegacy
{
	function display($tpl = null)
	{
		//load submenu
		$JgiveHelper=new JgiveHelper();
		$JgiveHelper->addSubmenu('cp');
		
		$this->_setToolBar();
		
		if(JVERSION>=3.0)
			$this->sidebar = JHtmlSidebar::render();
					
		if(!JFactory::getApplication()->input->get('layout')){
			$this->setLayout('default');
		}
		parent::display($tpl);
	}
	
	function _setToolBar()
	{	
		$document=JFactory::getDocument();
		$document->addStyleSheet(JUri::base().'components/com_jgive/assets/css/jgive.css'); 
		$bar=JToolBar::getInstance('toolbar');
		JToolBarHelper::title(JText::_('COM_JGIVE'),'icon-48-jgive.png');
		JToolBarHelper::preferences( 'com_jgive' );
	}
}
?>
