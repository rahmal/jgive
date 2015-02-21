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
class jgiveController extends JControllerLegacy
{

	function __construct()
	{
		parent::__construct();
	}

	public function display($cachable = false, $urlparams = false)
	{
		$vName=JFactory::getApplication()->input->get('view','cp');
		$controllerName=JFactory::getApplication()->input->get('controller','cp');
		$cp='';
		$campaigns='';
		$donations='';
		$reports='';
		$payoutlayout='';
		$categories='';
		$ending_camp='';
		$queue=JFactory::getApplication()->input->get('layout');

		switch($vName)
		{
			case 'cp':
				$layout=JFactory::getApplication()->input->get('layout','default');
			break;

			case 'campaigns':
				$layout=JFactory::getApplication()->input->get('layout','all_list');
			break;

			case 'donations':
				$layout=JFactory::getApplication()->input->get('layout','all');
			break;

			case 'reports':
				$layout=JFactory::getApplication()->input->get('layout','default');
			break;

			case 'categories':
				$layout=JFactory::getApplication()->input->get('layout','default');
			break;
			case 'campaign':
				$layout=JFactory::getApplication()->input->get('layout','create');
			break;
			case 'ending_camp':
				$layout=JFactory::getApplication()->input->get('layout','default');
			break;
		}

		switch ($vName)
		{
			case 'cp':
				$mName='cp';
				$vLayout=JFactory::getApplication()->input->get('layout',$layout);
			break;

			case 'campaigns':
				$mName = 'campaigns';
				$vLayout = JFactory::getApplication()->input->get('layout',$layout);
			break;

			case 'donations':
				$mName = 'donations';
				$vLayout = JFactory::getApplication()->input->get('layout',$layout);
			break;

			case 'reports':
				$mName = 'reports';
				$vLayout = JFactory::getApplication()->input->get('layout',$layout);
			break;

			case 'campaign':
				$mName = 'campaign';
				$vLayout = JFactory::getApplication()->input->get('layout',$layout);
			break;

			case 'ending_camp':
				$mName = 'ending_camp';
				$vLayout = JFactory::getApplication()->input->get('layout',$layout);
			break;
		}

		$document=JFactory::getDocument();
		$vType=$document->getType();
		$view=$this->getView($vName,$vType);
		if($model=$this->getModel($mName))
		{
			$view->setModel($model,true);
		}

		if($mName=="donations")
		{
			switch($this->getTask())
			{
				case 'view':
				{
					JRequest::setVar( 'hidemainmenu', 1 );
					JRequest::setVar( 'layout', 'order'  );
					JRequest::setVar( 'view', 'donations' );
					$vLayout="details";
				}
				break;
			}
		}
		$view->setLayout($vLayout);
		$view->display();
	}// function

	function getVersion()
	{
		echo $recdata = file_get_contents('http://techjoomla.com/vc/index.php?key=abcd1234&product=jgive');
		jexit();
	}

	/*function to delete order*/
	function deleteDonations()
	{
		$model=$this->getModel('donations');
		$post=JRequest::get('post');
		$donationid=$post['cid'];
		if($model->deleteDonations($donationid)){
			$msg=JText::_('COM_JGIVE_DONATION_DELETED');
		}
		else{
			$msg=JText::_('COM_JGIVE_ERR_DONATION_DELETED');
		}
		$this->setRedirect(JUri::base()."index.php?option=com_jgive&view=donations",$msg);
	}

	/*Publish*/
	function publish()
	{
		$input=JFactory::getApplication()->input;
		$view=$input->get('view','campaigns');
		//Get some variables from the request
		$cid=$input->get('cid','', 'array');
		JArrayHelper::toInteger($cid);
		if($view=="campaigns")
		{
			$model=$this->getModel('campaigns');
			if($model->setItemState($cid,1)){
				$msg=JText::sprintf('Campaign(s) published ',count($cid));
			}else{
				$msg=$model->getError();
			}
			$this->setRedirect('index.php?option=com_jgive&view=campaigns&layout=all_list',$msg);
		}
	}

	/*Unpublish*/
	function unpublish()
	{
		$input=JFactory::getApplication()->input;
		$view=$input->get('view','campaigns');
		//Get some variables from the request
		$cid=$input->get('cid','', 'array');
		JArrayHelper::toInteger($cid);
		if($view=="campaigns")
		{
			$model=$this->getModel('campaigns');
			if($model->setItemState($cid,0)){
				$msg=JText::sprintf('Campaign(s) unpublished ',count($cid));
			}else{
				$msg=$model->getError();
			}
			$this->setRedirect('index.php?option=com_jgive&view=campaigns&layout=all_list',$msg);
		}
	}
	// Delete Campaigns
	function remove()
	{
		$input=JFactory::getApplication()->input;
		$view=$input->get('view','campaigns');
		$cid=$input->get('cid','', 'array');
		JArrayHelper::toInteger($cid);
		if($view=="campaigns")
		{
			$model=$this->getModel('campaigns');
			if($model->delete_campaigns($cid)){
				$msg=JText::sprintf('Campaign(s) deleted',count($cid));
			}
			else{
				$msg=$model->getError();
			}

			// Trigger - OnAfterJGiveCampaignDelete.
			$dispatcher=JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');
			$result=$dispatcher->trigger('OnAfterJGiveCampaignDelete',array($cid));
			// Trigger - OnAfterJGiveCampaignDelete end.

			$this->setRedirect('index.php?option=com_jgive&view=campaigns&layout=all_list',$msg);
		}
	}
	// Mark Campaigns as featured
	function feature()
	{
		$input=JFactory::getApplication()->input;
		$view=$input->get('view','campaigns');
		//Get some variables from the request
		$cid=$input->get('cid','', 'array');
		JArrayHelper::toInteger($cid);
		if($view=="campaigns")
		{
			$model=$this->getModel('campaigns');
			if($model->setFeatureUnfreature($cid,1)){
				$msg=JText::sprintf('Campaign(s) Featured ',count($cid));
			}else{
				$msg=$model->getError();
			}
			$this->setRedirect('index.php?option=com_jgive&view=campaigns&layout=all_list',$msg);
		}

	}
	// Mark Campaigns as unfeatured
	function unfeature()
	{
		$input=JFactory::getApplication()->input;
		$view=$input->get('view','campaigns');
		//Get some variables from the request
		$cid=$input->get('cid','', 'array');
		JArrayHelper::toInteger($cid);
		if($view=="campaigns")
		{
			$model=$this->getModel('campaigns');
			if($model->setFeatureUnfreature($cid,0)){
				$msg=JText::sprintf('Campaign(s) Unfeatured ',count($cid));
			}else{
				$msg=$model->getError();
			}
			$this->setRedirect('index.php?option=com_jgive&view=campaigns&layout=all_list',$msg);
		}
	}
	function deletePayouts()
	{
		$input=JFactory::getApplication()->input;
		$view=$input->get('view','reports');
		//Get some variables from the request
		$cid=$input->get('cid','', 'array');
		JArrayHelper::toInteger($cid);
		if($view=="reports")
		{
			$model=$this->getModel('reports');
			if($model->deletePayouts($cid)){
				$msg=JText::sprintf('Payout(s) deleted ',count($cid));
			}else{
				$msg=$model->getError();
			}
			$this->setRedirect('index.php?option=com_jgive&view=reports&layout=payouts',$msg);
		}

	}
	function save()
	{
		$model=$this->getModel('donations');
		$post=JRequest::get('post');
		$model->setState('request',$post);
		$result=$model->changeOrderStatus();
		if($result==1){
			$msg = JText::_('COM_JGIVE_SAVING_MSG');
		}elseif($result==3){
			$msg=JText::_('COM_JGIVE_REFUND_SAVING_MSG');
		}else{
			$msg=JText::_('COM_JGIVE_ERROR_SAVING_MSG');
		}
		$link='index.php?option=com_jgive&view=donations&layout=all';
		$this->setRedirect($link,$msg);
	}

	function SaveNewPayout()
	{
		JSession::checkToken() or jexit('Invalid Token');
		//get model
		$model=$this->getModel('reports');
		$result=$model->savePayout();
		$redirect=JRoute::_('index.php?option=com_jgive&view=reports&layout=payouts',false);
		if($result){
			$msg= JText::_('COM_JGIVE_PAYOUT_SAVED');
		}
		else{
			$msg= JText::_('COM_JGIVE_PAYOUT_ERROR_SAVING');
		}
		$this->setRedirect($redirect,$msg);
	}

	function editPayout()
	{
		JSession::checkToken() or jexit('Invalid Token');
		//get model
		$model=$this->getModel('reports');
		$result=$model->editPayout();
		$redirect=JRoute::_('index.php?option=com_jgive&view=reports&layout=payouts',false);
		if($result){
			$msg= JText::_('COM_JGIVE_PAYOUT_SAVED');
		}
		else{
			$msg= JText::_('COM_JGIVE_PAYOUT_ERROR_SAVING');
		}
		$this->setRedirect($redirect,$msg);
	}

	// manoj - added for bill
	function changeSuccessState()
	{
		$post  = JFactory::getApplication()->input->post;
		$model = $this->getModel('campaigns');
		$model->setState('request', $post);
		//print_r($post->get('hiddenCid'));	die;

		$cid = $post->get('hiddenCid');
		$successStatus = $post->get('hiddenSuccessStatus');

		$result = $model->changeSuccessState($cid, $successStatus);

		if($result==true)
		{
			$msg = JText::_('COM_JGIVE_MSG_SUCCESS_STATUS_CHANGED_SUCCESS');
		}
		else
		{
			$msg = JText::_('COM_JGIVE_MSG_SUCCESS_STATUS_CHANGED_ERROR');
		}

		$link='index.php?option=com_jgive&view=campaigns&layout=all_list';

		$this->setRedirect($link, $msg);
	}

	// Manoj.
	// Called from CP view.
	function updateAllCampaignsSuccessStatus()
	{

		$db   = JFactory::getDBO();
		$query = "SELECT id
		FROM #__jg_campaigns";
		$db->setQuery($query);
		$campaigns = $db->loadColumn();

		$helperPath = JPATH_SITE . DS . 'components' . DS . 'com_jgive' . DS . 'helpers' . DS . 'campaign.php';

		if(!class_exists('campaignHelper'))
		{
			JLoader::register('campaignHelper', $helperPath );
			JLoader::load('campaignHelper');
		}

		$campaignHelper = new campaignHelper();

		foreach($campaigns as $cid)
		{
			echo "<br/>" . JText::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_3') . $cid;
			$campaignHelper->updateCampaignSuccessStatus($cid, $campaignSuccessStatus=NULL, $orderId=0);
		}

		echo "<br/>" . JText::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_4');
	}

}
?>
