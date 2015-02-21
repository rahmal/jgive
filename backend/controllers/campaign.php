<?php
/**
 * @version		1.6 jgive $
 * @package		jgive
 * @copyright	Copyright © 2013 - All rights reserved.
 * @license		GNU/GPL
 * @author		TechJoomla
 * @author mail	extensions@techjoomla.com
 * @website		http://techjoomla.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
class jgiveControllerCampaign extends jgiveController
{
	//saves new campaign
	function save()
	{
		//check token
		JSession::checkToken() or jexit('Invalid Token');
		$session=JFactory::getSession();
		//get model
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$model=$this->getModel('campaign');
		$result=$model->save();
		$campaignid=$session->get('camapign_id');
		$session->set('camapign_id','');

		if($result)
		{
			$redirect=JRoute::_('index.php?option=com_jgive&view=campaigns&layout=all_list',false);
			$msg= JText::_('COM_JGIVE_CAMPAIGN_SAVED');
		}
		else
		{
			$redirect=JRoute::_('index.php?option=com_jgive&view=campaign&layout=create&cid='.$campaignid,false);
			$msg= JText::_('COM_JGIVE_CAMPAIGN_ERROR_SAVING');
		}
		$this->setRedirect($redirect,$msg);
	}

	//edits a campaign
	function edit()
	{
		JSession::checkToken() or jexit('Invalid Token');
		$post=JRequest::get('post');
		$campaignid=$post['cid'];
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		//get model

		$model=$this->getModel('campaign');
		$result=$model->edit();

		if($result)
		{
			$redirect=JRoute::_('index.php?option=com_jgive&view=campaigns&layout=all_list',false);
			$msg= JText::_('COM_JGIVE_CAMPAIGN_SAVED');
		}
		else
		{
			$redirect=JRoute::_('index.php?option=com_jgive&view=campaign&layout=create&cid='.$campaignid,false);
			$msg= JText::_('COM_JGIVE_CAMPAIGN_ERROR_SAVING');
		}

		$this->setRedirect($redirect,$msg);
	}
}
?>
