<?php
/**
 * @version		1.5 jgive $
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
			$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
			$redirect=JRoute::_('index.php?option=com_jgive&view=campaigns&layout=my&Itemid='.$itemid,false);
			$msg= JText::_('COM_JGIVE_CAMPAIGN_SAVED');
		}
		else
		{
			$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=create');
			$redirect=JRoute::_('index.php?option=com_jgive&view=campaign&layout=create&cid='.$campaignid.'&Itemid='.$itemid,false);
			$msg= JText::_('COM_JGIVE_CAMPAIGN_ERROR_SAVING');
		}
		$this->setRedirect($redirect,$msg);
	}

	//edits a campaign
	function edit()
	{
		//$paypal_model=$this->getModel('paypal');
		//$paypal_model->splipPay();
		/*$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'paypal.php';
		if(!class_exists('paypal'))
		{
		//require_once $path;
			JLoader::register('paypal', $helperPath );
			JLoader::load('paypal');
		}
		$paypal=new paypal();
		$paypal->splipPay();*/
		//check token
		JSession::checkToken() or jexit('Invalid Token');
		$post=JRequest::get('post');
		$campaignid=$post['cid'];
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		//get model

		$model=$this->getModel('campaign');
		$result=$model->edit();
		if($result){
			$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my');
			$redirect=JRoute::_('index.php?option=com_jgive&view=campaigns&layout=my&Itemid='.$itemid,false);
			$msg= JText::_('COM_JGIVE_CAMPAIGN_SAVED');
		}else{
			$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=create');
			$redirect=JRoute::_('index.php?option=com_jgive&view=campaign&layout=create&cid='.$campaignid.'&Itemid='.$itemid,false);
			$msg= JText::_('COM_JGIVE_CAMPAIGN_ERROR_SAVING');
		}
		$this->setRedirect($redirect,$msg);
	}

	function viewMoreDonorReports()
	{
		$input = JFactory::getApplication()->input;
		$post = $input->post;

		$cid = $post->get('cid','','INT');
		$jgive_index = $post->get('jgive_index','','INT');

		$model=$this->getModel('campaign');
		$result = $model->viewMoreDonorReports($cid, $jgive_index);

		echo json_encode($result);
		jexit();
	}

	function viewMoreDonorProPic()
	{
		$input = JFactory::getApplication()->input;
		$post = $input->post;

		$cid = $post->get('cid','','INT');
		$jgive_index = $post->get('jgive_index','','INT');

		$model=$this->getModel('campaign');
		$result = $model->viewMoreDonorProPic($cid, $jgive_index);

		echo json_encode($result);
		jexit();
	}
}
?>
