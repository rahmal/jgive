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

class jgiveViewDonations extends JViewLegacy
{
	function display($tpl = null)
	{
		global $mainframe,$option;
		$mainframe=JFactory::getApplication();
		$option=JFactory::getApplication()->input->get('option');

		//get params
		$params=JComponentHelper::getParams('com_jgive');
		$this->currency_code=$params->get('currency');
		$this->default_country=$params->get('default_country');

		//imp
		$this->donations_site=1;//this is frontend

		//default layout is paymentform
		$layout=JFactory::getApplication()->input->get('layout','paymentform');
		$this->setLayout($layout);

		//get logged in user id
		$user=JFactory::getUser();
		$this->logged_userid=$user->id;

		$donationid=JFactory::getApplication()->input->get('donationid');

		$input=JFactory::getApplication()->input;
		$guestemail=$input->get('email');

		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$this->jomsocailToolbarHtml = $jgiveFrontendHelper->jomsocailToolbarHtml();

		if(!(($layout=='details') AND $donationid  AND $guestemail))
		{
			$session=JFactory::getSession();
			$session->clear('JGIVE_order_id');

			if(!$this->logged_userid)
			{
				$this->guest_donation=$params->get('guest_donation');
				if($this->guest_donation)
				{
					$msg=JText::_('COM_JGIVE_LOGIN_MSG_SILENT');
					$uri=JFactory::getApplication()->input->get('REQUEST_URI','','server','string');
					$url=base64_encode($uri);
					$session=JFactory::getSession();
					$guest_login=$session->get('quick_reg_no_login');
					$session->clear('quick_reg_no_login');
				}
				else
				{
					$msg=JText::_('COM_JGIVE_LOGIN_MSG');
					$uri=$_SERVER["REQUEST_URI"];
					$url=base64_encode($uri);
					$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$url,false),$msg);
				}
			}

		}
		$path=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php';
		if(!class_exists('donationsHelper'))
		{
			JLoader::register('donationsHelper', $path );
			JLoader::load('donationsHelper');
		}
		$donationsHelper=new donationsHelper();
		$this->pstatus=$donationsHelper->getPStatusArray();
		$this->sstatus=$donationsHelper->getSStatusArray();//used on donations view for payment status filter

		if($layout=='paymentform')
		{
			//get country list options
			//use helper function
			$countries=$jgiveFrontendHelper->getCountries();
			$this->countries=$countries;
			//get campaign id
			$cid=$this->get('CampaignId');
			$this->cid=$cid;

			$cdata=$this->get('Campaign');
			$this->assignRef('cdata',$cdata);

			$params=JComponentHelper::getparams('com_jgive');
			//joomla profile import
			$session=JFactory::getSession();
			$nofirst='';
			$nofirst=$session->get('No_first_donation'); //if no user data set in session then only import the user profile or only first time after login
			if(empty($nofirst))
			{
				$profile_import=$params->get('profile_import');
				//if profie import is on the call profile import function
				if($profile_import)
				{
					$integrationsHelper=new integrationsHelper();
					$profiledata=$integrationsHelper->profileImport(1);
					if(!empty($profiledata))
					{
						if(!empty($profiledata['first_name']))
							$session->set('JGIVE_first_name', $profiledata['first_name']);
						if(!empty($profiledata['last_name']))
							$session->set('JGIVE_last_name', $profiledata['last_name']);
						if(!empty($profiledata['paypal_email']))
							$session->set('JGIVE_paypal_email', $profiledata['paypal_email']);
						if(!empty($profiledata['address']))
							$session->set('JGIVE_address', $profiledata['address']);
						if(!empty($profiledata['address2']))
							$session->set('JGIVE_address2', $profiledata['address2']);
						if(!empty($profiledata['city']))
							$session->set('JGIVE_city', $profiledata['city']);
						if(!empty($profiledata['state']))
							$session->set('JGIVE_state', $profiledata['state']);
						if(!empty($profiledata['country']))
							$session->set('JGIVE_country', $profiledata['country']);
						if(!empty($profiledata['zip']))
							$session->set('JGIVE_zip', $profiledata['zip']);
						if(!empty($profiledata['phone']))
							$session->set('JGIVE_phone', $profiledata['phone']);
					}
				}
			}
			$session=JFactory::getSession();
			$this->session=$session;
			//geteways
			$dispatcher=JDispatcher::getInstance();
			JPluginHelper::importPlugin('payment');

			$params=JComponentHelper::getParams('com_jgive');
			$this->gateways=$params->get('gateways');

			$gateways=array();
			if(!empty($this->gateways)){
				$gateways = $dispatcher->trigger('onTP_GetInfo',array((array)$this->gateways)); //array typecasting is imp
			}
			$this->assignRef('gateways',$gateways);

			//Recurring payment gateway

			$this->recurringGateways = array();

			foreach ($gateways as $gateway)
			{

				if($gateway->id=="paypal")
				{
					$this->recurringGateways[]=$gateway;
				}

			}

			//Get campaign givebacks
			$this->giveback_id = $session->get('JGIVE_giveback_id');
			$JGIVE_cid = $session->get('JGIVE_cid');

			//if ($this->giveback_id AND $JGIVE_cid)
			{
				$campaignHelper = new campaignHelper();
				$this->campaignGivebacks = $campaignHelper->getCampaignGivebacks($JGIVE_cid);
			}

		}

		if($layout=='confirm')
		{
			//@TODO save all posted data somewhere
			//echo "<pre>";print_r($_POST);echo "</pre>";
			$session=JFactory::getSession();
			$this->assignRef('session',$session);
			$cid=$this->get('CampaignId');
			$this->assignRef('cid',$cid);
			$cdata=$this->get('Campaign');
			$this->assignRef('cdata',$cdata);
			$dispatcher=JDispatcher::getInstance();
			JPluginHelper::importPlugin('payment');

			$params=JComponentHelper::getParams('com_jgive');
			$this->gateways=$params->get('gateways');

			$gateways=array();
			if(!empty($this->gateways)){
				$gateways = $dispatcher->trigger('onTP_GetInfo',array((array)$this->gateways)); //array typecasting is imp
			}
			$this->assignRef('gateways',$gateways);

			//print_r($gateways);
		}

		if($layout=='details')
		{
			$donation_id=JFactory::getApplication()->input->get('donationid');
			$donation_details=$this->get('SingleDonationInfo');
			// TO DO check the email id against orderid except below
			$this->logged_userid;
			$donation_details['donor']->user_id;
			$this->guest_donation=$params->get('guest_donation');
			if($this->guest_donation)
			{
				if(!$this->logged_userid)
				{
					$input=JFactory::getApplication()->input;
					$guest_email=$input->get('email');
					$donar_email=md5($donation_details['donor']->email);
					if($guest_email!=$donar_email)
					{

						$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=my');
						$link=JRoute::_('index.php?option=com_jgive&view=donations&layout=my&Itemid='.$itemid,false);
						$msg=JText::_('COM_JGIVE_NO_ACCESS_MSG');
						$mainframe->enqueueMessage($msg,'notice');
						$mainframe->redirect($link,'');
					}

				}
				else if($this->logged_userid!=$donation_details['donor']->user_id)
				{
				$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=my');
				$link=JRoute::_('index.php?option=com_jgive&view=donations&layout=my&Itemid='.$itemid,false);
				$msg=JText::_('COM_JGIVE_NO_ACCESS_MSG');
				$mainframe->enqueueMessage($msg,'notice');
				$mainframe->redirect($link,'');
				}


			}
			else if($this->logged_userid!=$donation_details['donor']->user_id)
			{
				$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=my');
				$link=JRoute::_('index.php?option=com_jgive&view=donations&layout=my&Itemid='.$itemid,false);
				$msg=JText::_('COM_JGIVE_NO_ACCESS_MSG');
				$mainframe->enqueueMessage($msg,'notice');
				$mainframe->redirect($link,'');
			}

			$this->assignRef('donation_details',$donation_details);

		}
		if($layout=='my' || $layout=='all')
		{
			if(!$this->logged_userid AND $layout=='my')
			{
				$msg=JText::_('COM_JGIVE_LOGIN_MSG');
				$uri=$_SERVER["REQUEST_URI"];
				$url=base64_encode($uri);
				$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$url,false),$msg);
			}
			$donations=$this->get('Donations');
			$this->donations=$donations;

			//get ordering filter
			$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jgive.filter_order_Dir','filter_order_Dir','desc','word');
			$filter_type=$mainframe->getUserStateFromRequest('com_jgive.filter_order','filter_order','id','string');

			//payment status filter
			$payment_status=$mainframe->getUserStateFromRequest('com_jgive'.'payment_status', 'payment_status','', 'string' );
			if($payment_status==null){
				$payment_status='-1';
			}

			//set filters
			$lists['payment_status']=$payment_status;
			$lists['order_Dir']=$filter_order_Dir;
			$lists['order']=$filter_type;
			$this->lists=$lists;

			$total=$this->get('Total');
			$this->total=$total;

			$pagination=$this->get('Pagination');
			$this->pagination=$pagination;

			$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=my');
			$this->Itemid=$itemid;
		}

		parent::display($tpl);
	}
}
?>
