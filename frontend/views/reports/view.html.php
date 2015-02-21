<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
	
// no direct access
defined('_JEXEC') or die('Restricted access'); 
jimport( 'joomla.application.component.view');

class jgiveViewReports extends JViewLegacy
{
	function display($tpl = null)
	{		
		global $mainframe,$option;		
		$mainframe=JFactory::getApplication();
		$option=JFactory::getApplication()->input->get('option');	

		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$this->jomsocailToolbarHtml = $jgiveFrontendHelper->jomsocailToolbarHtml();

		//get params
		$params=JComponentHelper::getParams('com_jgive');
		$this->currency_code=$params->get('currency');
		
		//imp
		$this->issite=1;//this is frontend
		
		//default layout is default
		$layout=JFactory::getApplication()->input->get('layout','mypayouts');//die;
		$this->setLayout($layout);
		
		//get logged in user id
		$user=JFactory::getUser();
		$this->logged_userid=$user->id;		
		
		/*load language file for component backend*/
		$lang =  JFactory::getLanguage();
		$lang->load('com_jgive', JPATH_ADMINISTRATOR);
				
		if($layout=='mypayouts')
		{
			if(!$this->logged_userid)
			{				
				$msg=JText::_('COM_JGIVE_LOGIN_MSG');				
				$uri=JFactory::getApplication()->input->get('REQUEST_URI','','server','string');
				$url=base64_encode($uri);
				$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$url),$msg);
			}
			
			$payouts=$this->get('Payouts');		
			$this->payouts=$payouts;
			
			$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jgive.filter_order_Dir','filter_order_Dir','desc','word');
			$filter_type=$mainframe->getUserStateFromRequest('com_jgive.filter_order','filter_order','goal_amount','string');
			
			$lists['order_Dir']=$filter_order_Dir;
			$lists['order']=$filter_type;
			$this->lists=$lists;
			
			$total=$this->get('Total');
			$this->total=$total;

			$pagination=$this->get('Pagination');			
			$this->pagination=$pagination;	
					
		}	
		
		parent::display($tpl);
	}	
}
?>
