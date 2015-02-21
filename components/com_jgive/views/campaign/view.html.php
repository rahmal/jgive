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
		$jgiveFrontendHelper = new jgiveFrontendHelper;
		$this->jomsocailToolbarHtml = $jgiveFrontendHelper->jomsocailToolbarHtml();

		if($layout=='create')
		{
			if(!$this->logged_userid)
			{
				$msg=JText::_('COM_JGIVE_LOGIN_MSG');
				$uri=$_SERVER["REQUEST_URI"];
				$url=base64_encode($uri);
				$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$url,false),$msg);
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

				//only owner can edit campaign
				//if not owner redirect to all campaigns
				if($cdata['campaign']->creator_id!=$this->logged_userid)
				{
					$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
					$link=JRoute::_('index.php?option=com_jgive&view=campaigns&layout=all&Itemid='.$itemid,false);
					$msg=JText::_('COM_JGIVE_CAMPAIGN_NO_EDIT_PERMISSIONS');
					$mainframe->redirect($link,$msg);
				}
			}
			else
			{
				if(JVERSION>=3.0)
					$cdata['campaign']=new Stdclass();//imp
				else
					$cdata=array();//imp

				$params=JComponentHelper::getparams('com_jgive');
				$integration=$params->get('integration');
				// joomla profile import
					$profile_import=$params->get('profile_import');
					//if profie import is on the call profile import function
					$integrationsHelper=new integrationsHelper();
					if($profile_import){
						$cdata=$integrationsHelper->profileImport();
					}
					// check is user profile completed to allow create campaign ?
					$profile_complete=$params->get('profile_complete');
					$profile_check=array();
					if($profile_complete)
					{
						$profile_check=$integrationsHelper->profileChecking();
						if(!empty($profile_check))
						{
							if($integration=='joomla')
							{
								$msg=JText::_('COM_JGIVE_PROFILE_COMPLETE_MSG');
								$uri=JFactory::getApplication()->input->get('REQUEST_URI','','server','string');
								$url=base64_encode($uri);
								$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_users&view=profile&layout=edit');
								$mainframe->redirect(JRoute::_('index.php?option=com_users&view=profile&layout=edit&itemid='.$itemid.'&return='.$url),$msg);
							}
						}
					}
				$this->cdata=$cdata;
			}
			//get js group for loggend in user
			$this->js_groups=$this->get('JS_usergroup');
			// get the campaigns categeory
			$cats	= $this->get('CampaignsCats');
			$this->assignRef('cats', $cats);

			// organization_individual_type
			//$campaignHelper=new campaignHelper();
			//$OrgIndType=$campaignHelper->organization_individual_type();
			//$this->OrgIndType= $OrgIndType;
		}
		if($layout=='single')//show a single campaign
		{

			$this->allCampsitemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
			$this->createCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=create');
			//get campaign details

			$cdata=$this->get('Campaign');
			$this->cdata=$cdata;
			//do not show campaign if it is unpublished
			//and redirect to all campaigns
			if(!$cdata['campaign']->published)
			{
				$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
				$link=JRoute::_('index.php?option=com_jgive&view=campaigns&layout=all&Itemid='.$itemid,false);
				$msg=JText::_('COM_JGIVE_CAMPAIGN_NOT_PUBLISHED');
				$mainframe->redirect($link,$msg);
			}
		}

		parent::display($tpl);
	}
}
?>
