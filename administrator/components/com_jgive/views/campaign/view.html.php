<?php
/**
 * @version		1.0.0 jgive $
 * @package		jgive
 * @copyright	Copyright © 2012 - All rights reserved.
 * @license		GNU/GPL
 * @author		TechJoomla
 * @author mail	extensions@techjoomla.com
 * @website		http://techjoomla.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view' );
jimport( 'joomla.user.helper' );
jimport( 'joomla.utilities.arrayhelper' );
class jgiveViewCampaign extends JViewLegacy
{
	function display($tpl = null)
	{
		$mainframe=JFactory::getApplication();
		$path=JPATH_ROOT.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php';
			if(!class_exists('donationsHelper'))
			{
				JLoader::register('donationsHelper', $path );
				JLoader::load('donationsHelper');
			}
			
			$path=JPATH_ROOT.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'integrations.php';
			if(!class_exists('integrationsHelper'))
			{
				JLoader::register('integrationsHelper', $path );
				JLoader::load('integrationsHelper');
			}
		//get logged in user id
		$user=JFactory::getUser();
		$this->logged_userid=$user->id;

		//get params
		$params=JComponentHelper::getParams('com_jgive');
		$this->currency_code=$params->get('currency');
		$this->commission_fee=$params->get('commission_fee');
		$this->send_payments_to_owner=$params->get('send_payments_to_owner');
		$this->default_country=$params->get('default_country');
		$this->admin_approval=$params->get('admin_approval');

		//create is a default layout
		$layout=JFactory::getApplication()->input->get('layout','create');
		$this->setLayout($layout);
		//create jgive helper object
		$jgiveFrontendHelper  =new jgiveFrontendHelper;

		if($layout=='create')
		{
			if(!$this->logged_userid)
			{
				$msg=JText::_('COM_JGIVE_LOGIN_MSG');
				$uri=JFactory::getApplication()->input->get('REQUEST_URI','','server','string');
				$url=base64_encode($uri);
				$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$url),$msg);
			}

			//get country list options
			//use helper function
			$countries=$jgiveFrontendHelper->getCountries();
			$this->countries=$countries;
			//default task is save
			$this->task='save';
			$cid=JRequest::getInt('cid','');
			//get all campaigns email id
			$this->allCampsitemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
			$this->myCampaignsItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my');
			$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
			if($cid)//if cid is passed task is - edit
			{
				$this->task='edit';

				if(JVERSION>=3.0)
					$cdata['campaign']=new Stdclass();//imp
				else
					$cdata=array();//imp

				$cdata=$this->get('Campaign');
				$this->cdata=$cdata;

				
			}
			else
			{
				if(JVERSION>=3.0)
					$cdata['campaign']=new Stdclass();//imp
				else
					$cdata=array();//imp
			
				
			}

			// get the campaigns categeory
			$cats	= $this->get('CampaignsCats');
			$this->assignRef('cats', $cats);

			
		}
		if($layout=='single')//show a single campaign
		{

			$this->createCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=create');
			//get campaign details

			$cdata=$this->get('Campaign');
			$this->cdata=$cdata;
			
		}
		$this->_setToolBar($layout,$this->task);
		if(JVERSION>=3.0)
			$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}
	function _setToolBar($layout,$task)
	{	
		//Get the toolbar object instance
		$document=JFactory::getDocument();
		$document->addStyleSheet(JUri::base().'components/com_jgive/assets/css/jgive.css'); 
		$bar=JToolBar::getInstance('toolbar');
		$layout=JFactory::getApplication()->input->get('layout','');
		if($layout=='create')
		{
			JToolBarHelper::back('COM_JGIVE_BACK','index.php?option=com_jgive&view=campaigns&layout=all_list');
			JToolBarHelper::save('campaign.'.$task);
		}
	}	
}
?>
