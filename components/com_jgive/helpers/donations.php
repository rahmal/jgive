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
// Component Helper
jimport('joomla.application.component.helper');
class donationsHelper
{
	// manoj - added for bill
	function processRefund($orderData)
	{
		//var_dump($orderData);
		//$data = (array)$orderData;
		/*[id] => 13
		[order_id] => JGOID-00013
		[fund_holder] => 0
		[status] => C
		[processor] => ewallet
		[amount] => 1.00
		[fee] => 1.10
		[cdate] => 2013-09-25 10:26:47
		[donor_id] => 641
		[cid] => 10
		[title] => Test 2 Campaigns11
		*/

		$data                        = array();
		$data['order_id']            = $orderData->id;
		$data['user_id']             = $orderData->donor_id;
		$data['total']               = $orderData->amount;
		$data['client']              = 'com_jgive';
		$data['payment_description'] = JText::_('COM_JGIVE_PROCESS_REFUND_DEFAULT_MSG') . ' ' . $orderData->title;
		$data['return']              = '';

		if($orderData->processor=='ewallet')
		{
			JPluginHelper::importPlugin('payment', $orderData->processor);
			$dispatcher = JDispatcher::getInstance();
			$result     = $dispatcher->trigger('onTP_ProcessRefund', array($data));

			if($result[0]['status']=='C')
			{
				$comment = JText::_('COM_JGIVE_PROCESS_REFUND_DEFAULT_MSG') . ' ' . $orderData->title;
				$this->updatestatus($result[0]['order_id'], 'RF', $comment, 1);

				// Start - Plugin trigger OnAfterJGivePaymentProcess.
				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('system');
				// Params - orderId, newStatus, comment, sendEmail
				$result = $dispatcher->trigger('OnAfterJGivePaymentStatusChange', array($result[0]['order_id'], 'RF', $comment, 1));

				return 1;
			}
		}
		else
		{
			return 0;
		}
	}

	// manoj - added for bill
	function getDonationsByStatus($cid, $orderStatus)
	{
		$db = JFactory::getDBO();
		$query = "SELECT i.id, i.order_id, i.fund_holder, i.status, i.processor, i.amount, i.fee, i.cdate,
		d.user_id AS donor_id,
		c.id AS cid, c.title
		FROM #__jg_orders AS i
		LEFT JOIN #__jg_campaigns AS c ON c.id=i.campaign_id
		LEFT JOIN #__jg_donors AS d on d.id=i.donor_id
		WHERE i.status = " . $db->Quote($orderStatus) ."
		AND c.id = " . $cid;
		$db->setQuery($query);
		$donations = $db->loadObjectList();

		return $donations;
	}

	function getPStatusArray()
	{
		$pstatus=array();
		$pstatus[]=JHtml::_('select.option','P',JText::_('COM_JGIVE_PENDING'));
		$pstatus[]=JHtml::_('select.option','C',JText::_('COM_JGIVE_CONFIRMED'));
		$pstatus[]=JHtml::_('select.option','RF',JText::_('COM_JGIVE_REFUND'));
		$pstatus[]=JHtml::_('select.option','E',JText::_('COM_JGIVE_CANCELED'));
		$pstatus[]=JHtml::_('select.option','D',JText::_('COM_JGIVE_DENIED'));
		return $pstatus;
	}

	function getSStatusArray()
	{
		$sstatus=array();
		$app = JFactory::getApplication();

		if($app->issite() OR JVERSION<3.0)
			$sstatus[]=JHtml::_('select.option','-1',JText::_('COM_JGIVE_APPROVAL_STATUS'));
		$sstatus[]=JHtml::_('select.option','E',JText::_('COM_JGIVE_CANCELED'));
		$sstatus[]=JHtml::_('select.option','D',JText::_('COM_JGIVE_DENIED'));
		$sstatus[]=JHtml::_('select.option','P',JText::_('COM_JGIVE_PENDING'));
		$sstatus[]=JHtml::_('select.option','C',JText::_('COM_JGIVE_CONFIRMED'));
		$sstatus[]=JHtml::_('select.option','RF',JText::_('COM_JGIVE_REFUND'));
		return $sstatus;
	}

	function sendOrderEmail($orders_key,$campid='')
	{
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$db=JFactory::getDBO();

		$session=JFactory::getSession();
		$guest_email='';
		$Itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=my');
		$params=JComponentHelper::getParams('com_jgive');
		JRequest::setVar('donationid',$orders_key);

		$params=JComponentHelper::getParams('com_jgive');
		$this->currency_code=$params->get('currency');
		$pstatus=array();
		$pstatus[]=JHtml::_('select.option','P', JText::_('COM_JGIVE_PENDING'));
		$pstatus[]=JHtml::_('select.option','C', JText::_('COM_JGIVE_CONFIRMED'));
		$pstatus[]=JHtml::_('select.option','RF', JText::_('COM_JGIVE_REFUND'));
		$pstatus[]=JHtml::_('select.option','E', JText::_('COM_JGIVE_CANCELED'));
		$pstatus[]=JHtml::_('select.option','D', JText::_('COM_JGIVE_DENIED'));

		$this->pstatus=$pstatus;

	 	$query= "SELECT o.id, o.order_id, d.email, d.first_name, d.last_name
		FROM #__jg_orders AS o
		LEFT JOIN #__jg_donors as d ON d.id=o.donor_id
		WHERE o.id =".$orders_key;
		$db->setQuery($query);
		$orderuser=$db->loadObjectList();

		$this->donation_details=$this->getSingleDonationInfo($orders_key);

		$this->donations_site=1;
		$this->donations_email=1;

		$mainframe=JFactory::getApplication();
		$site=$mainframe->getCfg('sitename');

		if($this->donation_details['campaign']->type=='donation'){
			$html='<br/><div>'.JText::sprintf('COM_JGIVE_ORDER_MAIL_MSG',$site).'</div>';
		}else{
			$html='<br/><div>'.JText::sprintf('COM_JGIVE_INVESTMENT_ORDER_MAIL_MSG',$site).'</div>';
		}

		$guest_email=$billemail=$this->donation_details['donor']->email;
		$guest_email=md5($guest_email);
		ob_start();
		include(JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'views'.DS.'donations'.DS.'tmpl'.DS.'details.php');
		$html.=ob_get_contents();
		ob_end_clean();
		$order_id=$this->getOrderIdFromOrderIdKey($orders_key);
		$body=$html;
		$link = JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=donations&layout=details&donationid='.$orders_key.'&email='.$guest_email.'&Itemid='.$Itemid),strlen(JUri::base(true))+1);
		$link='<a href="'.$link.'">'.$link.'</a>';
		$body.=$link ;
		//= JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=donations&layout=details&donationid='.$orders_key.'&email='.$guest_email.'&Itemid='.$Itemid),strlen(JUri::base(true))+1);


		if($this->donation_details['campaign']->type=='donation'){
			$subject=JText::sprintf('COM_JGIVE_ORDER_MAIL_SUB',$site,$order_id);
		}else{
			$subject=JText::sprintf('COM_JGIVE_INVESTMENT_ORDER_MAIL_SUB',$site,$order_id);
		}

		// Check if email is to be sent for new orders to donor.
		$mail_recipients_new_order = $params->get('mail_recipients_new_order');

		if(is_array($mail_recipients_new_order))
		{
			if(in_array('donor', $mail_recipients_new_order))
			{
				$this->sendmail($billemail, $subject, $body, '');
			}
		}

		//send email donation email to campaign promoter & site admin
	 	$query="SELECT u.email FROM #__jg_campaigns as camp
		LEFT JOIN #__users as u on u.id = camp.creator_id
		where camp.id=".$campid;
		$db->setQuery($query);
		$promoteremail=$db->loadResult();

		//email body
		if(!empty($promoteremail))
		{
			if($this->donation_details['campaign']->type=='donation'){
				$html='<br/><div>'.JText::sprintf('COM_JGIVE_ORDER_MAIL_PROMOTER_MSG',$site).'</div>';
			}else{
				$html='<br/><div>'.JText::sprintf('COM_JGIVE_INVESTMENT_ORDER_MAIL_PROMOTER_MSG',$site).'</div>';
			}
			include(JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'views'.DS.'donations'.DS.'tmpl'.DS.'details.php');
			$html.=ob_get_contents();
			ob_end_clean();
			$order_id=$this->getOrderIdFromOrderIdKey($orders_key);
			$body=$html;
			$link = JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=donations&layout=details&donationid='.$orders_key.'&email='.$guest_email.'&Itemid='.$Itemid),strlen(JUri::base(true))+1);
			$link='<a href="'.$link.'">'.$link.'</a>';
			$body.=$link;

			// Check if email is to be sent for new orders to promoter.
			if(is_array($mail_recipients_new_order))
			{
				if(in_array('promoter', $mail_recipients_new_order))
				{
					$this->sendmail($promoteremail,$subject,$body,$params->get('email'));
				}
			}
		}

	}


	function sendmail($recipient,$subject,$body,$bcc_string)
	{
		global $mainframe;
		$mainframe = JFactory::getApplication();
		$from = $mainframe->getCfg('mailfrom');
		$fromname = $mainframe->getCfg('fromname');
		$recipient = trim($recipient);
		$mode = 1;
		$cc = null;
		$bcc = explode(',',$bcc_string);
		$attachment = null;
		$replyto = null;
		$replytoname = null;
		//for joomla 2.5
		//JUtility::sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
		//for joomla 3.0
		JFactory::getMailer()->sendMail($from, $fromname, $recipient, $subject, $body, $mode, $cc, $bcc, $attachment, $replyto, $replytoname);
	}

	//used in donation details view
	function getSingleDonationInfo($order_id_key='')
	{
		//echo"before==". $order_id_key;
		$db=JFactory::getDBO();
		if(!$order_id_key){
			$order_id_key=JFactory::getApplication()->input->get('donationid');
		}
		if(!$order_id_key)
			return;

		//echo"After==". $order_id_key;die;
		$query="SELECT donation_id
		FROM `#__jg_orders`
		WHERE `id`=".$order_id_key;
		$db->setQuery($query);
		$donation_id=$db->loadResult();

		if($donation_id) //Since jGive version 1.6
		{
			$query="SELECT *
			FROM `#__jg_donations`
			WHERE `id`=".$donation_id;
			$db->setQuery($query);
			$donation=$db->loadObject();
		}
		else //support earlier version of jGive upto 1.5
		{
			$query="SELECT *
			FROM `#__jg_donations`
			WHERE `order_id`=".$order_id_key;
			$db->setQuery($query);
			$donation=$db->loadObject();
		}
		//var_dump($donation);

		$query="SELECT campaign_id
		FROM `#__jg_orders`
		WHERE `id`=".$order_id_key;
		$db->setQuery($query);
		$cid=$db->loadResult();

		$query="SELECT c.*
		FROM `#__jg_campaigns` AS c
		WHERE c.id=".$cid;
		$db->setQuery($query);
		$campaign=$db->loadObject();
		$cdata['campaign']=$campaign;

		$query="SELECT SUM(o.amount) AS amount_received
		FROM `#__jg_orders` AS o
		WHERE o.campaign_id=".$cid."
		AND o.status='C'";
		$db->setQuery($query);
		$cdata['campaign']->amount_received=$db->loadResult();

		//if no donations, set receved amount as zero
		if($cdata['campaign']->amount_received=='')
			$cdata['campaign']->amount_received=0;

		//calculate remaining amount
		$cdata['campaign']->remaining_amount=($cdata['campaign']->goal_amount)- ($cdata['campaign']->amount_received);
		//////////////////////////////////////////
		$donation_details=array();
		$donation_details['campaign']=$cdata['campaign'];
		if($donation->donor_id)
		{
			$donor=$this->getDonorDetails($donation->donor_id);
		}
		$donation_details['donor']=$donor;
		$payment=$this->getPaymentDetails($order_id_key);
		$donation_details['payment']=$payment;
		$donation_details['payment']->annonymous_donation=$donation->annonymous_donation;
		//print_r($donation_details);
		return $donation_details;
	}

	//B
	//used in donation info view
	function getDonorDetails($donor_id)
	{
		$db=JFactory::getDBO();
		$query="SELECT *
		FROM `#__jg_donors`
		WHERE `id`=".$donor_id;
		$db->setQuery($query);
		$donor=$db->loadObject();
		//print_r($donor);
		return $donor;
	}

	//B
	//used in donation info view
	function getPaymentDetails($order_id_key)
	{
		$db=JFactory::getDBO();
		$query="SELECT o.*, d.giveback_id
		FROM `#__jg_orders` as o
		LEFT JOIN `#__jg_donations` as d ON o.donation_id = d.id
		WHERE o.`id`=".$order_id_key;
		$db->setQuery($query);
		$donor=$db->loadObject();
		//print_r($donor);
		return $donor;
	}

	/*
	 * Function to update status of order
	 *
	    Parameters:
	    order_id_key : int id of order
	    status : string status of order
	    comment : string default='' comment added if any
	    $send_mail : int default=1 weather to send status change mail or not.
	*/
	//B
	//F
	public function updatestatus($order_id_key,$status,$comment='',$send_mail=1,$duplicate_response=0)
	{
		global $mainframe;
		$jgiveFrontendHelper =new jgiveFrontendHelper();
		$session=JFactory::getSession();
		$guest_email='';
		$guest_email=$session->get('order_link_guestemail');
		if(empty($guest_email))
		{
			$db=JFactory::getDBO();
			$query="SELECT email FROM `#__jg_donors` as d
					LEFT JOIN `#__jg_orders` as o ON o.donor_id =d.id
					WHERE o.id=".$order_id_key;
			$db->setQuery($query);
 			$guest_email=md5($db->loadResult());
		}
		$session->clear('order_link_guestemail');

		$mainframe=JFactory::getApplication();
		$db=JFactory::getDBO();
		$res=new stdClass();
		$res->id=$order_id_key;
		$res->status=$status;
		if(!$db->updateObject('#__jg_orders',$res,'id'))
		{
			return 2;
		}

		if($send_mail==1 AND ($duplicate_response==0))
		{
			$params=JComponentHelper::getParams('com_jgive');

			$query= "SELECT o.id, o.order_id,o.campaign_id,d.email, d.first_name, d.last_name
			FROM #__jg_orders AS o
			LEFT JOIN #__jg_donors as d ON d.id=o.donor_id
			WHERE o.id =".$order_id_key;
			$db->setQuery($query);
 			$orderuser=$db->loadObjectList();

			$input=JFactory::getApplication()->input;

			$orderuser=$orderuser[0];
			switch($status)
			{
				case 'C' :
					$orderstatus =  JText::_('COM_JGIVE_CONFIRMED');
				break;
				case 'RF' :
					$orderstatus = JText::_('COM_JGIVE_REFUND') ;
				break;

				case 'P' :
					$orderstatus = JText::_('COM_JGIVE_PENDING') ;
				break;
				case 'E' :
					$orderstatus = JText::_('COM_JGIVE_CANCELED') ;
				break;
				case 'D' :
					$orderstatus = JText::_('COM_JGIVE_DENIED') ;
				break;
			}
			$this->donation_details=$this->getSingleDonationInfo($order_id_key);
			if($this->donation_details['campaign']->type=='donation'){
				$body = JText::_('COM_JGIVE_STATUS_CHANGE_BODY');
			}else{
				$body = JText::_('COM_JGIVE_INVESTMENT_STATUS_CHANGE_BODY');
			}

			$site = $mainframe->getCfg('sitename');
			if($comment)
			{
				$comment	= str_replace('{COMMENT}', $comment, JText::_('COM_JGIVE_COMMENT_TEXT'));
				$find 	= array ('{ORDERNO}','{STATUS}','{SITENAME}','{NAME}', '{COMMENTTEXT}');
				$replace= array($orderuser->order_id,$orderstatus,$site,$orderuser->first_name,$comment);
			}
			else
			{
				$find 	= array ('{ORDERNO}','{STATUS}','{SITENAME}','{NAME}', '{COMMENTTEXT}');
				$replace= array($orderuser->order_id,$orderstatus,$site,$orderuser->first_name,'');
			}

			$body	= str_replace($find, $replace, $body);
			$Itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=my');
			$link = JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=donations&layout=details&donationid='.$orderuser->id.'&email='.$guest_email.'&Itemid='.$Itemid),strlen(JUri::base(true))+1);
			$order_link = '<a href="'.$link.'">'.$link.'</a>';
			$body	= str_replace('{LINK}', $order_link, $body);
			$body = nl2br($body);

			if($this->donation_details['campaign']->type=='donation'){
				$subject = JText::sprintf('COM_JGIVE_STATUS_CHANGE_SUBJECT',$orderuser->order_id);
			}else{
				$subject = JText::sprintf('COM_JGIVE_INVESTMENT_STATUS_CHANGE_SUBJECT',$orderuser->order_id);
			}

			//send mail to campaign donor
			// Check if email is to be sent for order status change.
			$send_mail_order_status_change = $params->get('send_mail_order_status_change', 1);
			if($send_mail_order_status_change)
			{
				// Check if email is to be sent for order status change to donor.
				$mail_recipients_order_status_change = $params->get('mail_recipients_new_order');

				if(is_array($mail_recipients_order_status_change))
				{
					if(in_array('donor', $mail_recipients_order_status_change))
					{
						$this->sendmail($orderuser->email,$subject,$body,$params->get('mail'));
					}
				}
			}

			//*************************send mail to campaig promoter *******************//

			//promoter campaign promoter email & Name
			$campaignHelper=new campaignHelper();
			$campaignDetails=$campaignHelper->getCampaignDetails($orderuser->campaign_id);
			$promoteremailId=JFactory::getUser($campaignDetails->creator_id)->email;
			$promoterName=JFactory::getUser($campaignDetails->creator_id)->name;
			//email subject
			if($this->donation_details['campaign']->type=='donation'){
				$subject = JText::sprintf('COM_JGIVE_STATUS_CHANGE_SUBJECT_PROMOTER',$orderuser->order_id);
			}else{
				$subject = JText::sprintf('COM_JGIVE_INVESTMENT_STATUS_CHANGE_SUBJECT_PROMOTER',$orderuser->order_id);
			}

			//email content
			if($this->donation_details['campaign']->type=='donation'){
				$body = JText::_('COM_JGIVE_STATUS_CHANGE_BODY_PROMOTER');
			}else{
				$body = JText::_('COM_JGIVE_INVESTMENT_STATUS_CHANGE_BODY_PROMOTER');
			}

			$site = $mainframe->getCfg('sitename');
			if($comment)
			{
				$comment	= str_replace('{COMMENT}', $comment, JText::_('COM_JGIVE_COMMENT_TEXT'));
				$find 	= array ('{ORDERNO}','{STATUS}','{SITENAME}','{NAME}', '{COMMENTTEXT}');
				$replace= array($orderuser->order_id,$orderstatus,$site,$promoterName,$comment);
			}
			else
			{
				$find 	= array ('{ORDERNO}','{STATUS}','{SITENAME}','{NAME}', '{COMMENTTEXT}');
				$replace= array($orderuser->order_id,$orderstatus,$site,$promoterName,'');
			}

			$body	= str_replace($find, $replace, $body);
			$body = nl2br($body);

			// Send mail to campaign promoter.
			// Check if email is to be sent for order status change.
			$send_mail_order_status_change = $params->get('send_mail_order_status_change', 1);
			if($send_mail_order_status_change)
			{
				// Check if email is to be sent for order status change to donor.
				$mail_recipients_order_status_change = $params->get('mail_recipients_new_order');

				if(is_array($mail_recipients_order_status_change))
				{
					if(in_array('promoter', $mail_recipients_order_status_change))
					{
						$this->sendmail($promoteremailId,$subject,$body,$params->get('mail'));
					}
				}
			}
		}
	}

	//used in plugin trigger
	public function getCidFromOrderId($orderid)
	{
		$db=JFactory::getDBO();
		$query="SELECT o.campaign_id
		FROM #__jg_orders AS o
		WHERE o.id=".$orderid;
		$db->setQuery($query);
		return $db->loadResult();
	}
	public function getDonorIdFromOrderId($orderid)
	{
		$db=JFactory::getDBO();
		$query="SELECT d.user_id
		FROM #__jg_orders AS o
		LEFT JOIN #__jg_donors AS d ON d.id=o.donor_id
		WHERE o.id=".$orderid;
		$db->setQuery($query);
		return $db->loadResult();
	}
	public function getOrderIdKeyFromOrderId($order_id)
	{
		$db=JFactory::getDBO();
		$query="SELECT o.id
		FROM #__jg_orders AS o
		WHERE o.order_id='".$order_id."'";
		$db->setQuery($query);
		return $db->loadResult();
	}
	public function getOrderIdFromOrderIdKey($order_id_key)
	{
		$db=JFactory::getDBO();
		$query="SELECT o.order_id
		FROM #__jg_orders AS o
		WHERE o.id='".$order_id_key."'";
		$db->setQuery($query);
		return $db->loadResult();
	}
	// Send email to site admin when campaigns is created
	function sendCmap_create_mail($camp_details,$camp_id)
	{
		//echo "<pre>";print_r($camp_details);echo"</pre>";die;
		$userid=JFactory::getUser()->id;
		$params=JComponentHelper::getParams('com_jgive');
		$body 	= JText::_('COM_JGIVE_CAMP_AAPROVAL_BODY');
		$body	= str_replace('{title}', $camp_details['title'], $body);
		$body	= str_replace('{campid}', ':'.$camp_id, $body);
		$body	= str_replace('{username}', $camp_details['first_name'], $body);
		$body	= str_replace('{userid}', $userid, $body);
		$body	= str_replace('{link}', JUri::base().'administrator/index.php?option=com_jgive&view=campaigns&layout=all_list&approve=1', $body);
		$billemail=$params->get('email');
		$subject=JText::sprintf('COM_JGIVE_CAMP_CREATED_EMAIL_SUBJECT',$camp_details['title']);
		$this->sendmail($billemail,$subject,$body,$params->get('email'));
	}
	/**
	This function Checks whether order user and current logged use is same or not
	*/
	function getorderAuthorization($orderuser)
	{
		$user=JFactory::getUser();
		if($user->id==$orderuser)
			return 1;
		return 0;
	}
	/** get Order details **/
	function getOrderTransactoionIdAndStatus($order_id_key)
	{
		$db=JFactory::getDBO();
		$query="SELECT o.transaction_id,o.status
		FROM #__jg_orders as o
		WHERE o.id=".$order_id_key;
		$db->setQuery($query);
		return $db->loadObject();
	}
	/** This function gives plugin name from plugin parameter
	*/
	function getPluginName($plgname)
	{
		$plugin = JPluginHelper::getPlugin('payment', $plgname);
		@$params=json_decode($plugin->params);
		return @$params->plugin_name;
	}

	/**
	 * Get administration fee form order id
	 */
	function getFee($order_id)
	{
		$db=JFactory::getDBO();
		$query="SELECT fee FROM #__jg_orders
		WHERE order_id='".$order_id."'";
		$db->setQuery($query);
		return $result=$db->loadResult();
	}

	//Added by Sneha
	function getSoldGivebacks($order_id_key,$status='')
	{

		//get Donation id from order id
		$donationid=$this->getDonationIdFromOrderId($order_id_key);

		$db=JFactory::getDBO();
		$query="SELECT giveback_id
		FROM #__jg_donations
		where id=".$donationid;
		$db->setQuery($query);
		$giveback_id=$db->loadResult();

		$query="SELECT g.quantity as quantity
		FROM `#__jg_campaigns_givebacks` AS g
		WHERE g.id=".$giveback_id;
		$db->setQuery($query);
		$quantity=$db->loadResult();

		$quantity= $quantity+1;

		$res=new stdClass();
		$res->id=$giveback_id;
		$res->quantity=$quantity;//$data['order_id'];
		if(!$db->updateObject( '#__jg_campaigns_givebacks', $res, 'id' ))
		{
			//return false;
		}
		return true;
	}
	function getDonationIdFromOrderId($order_id_key)
	{
		$db=JFactory::getDBO();
		$query="SELECT `donation_id`
		FROM `#__jg_orders`
		where id=".$order_id_key;
		$db->setQuery($query);
		return $donationid=$db->loadResult();
	}

}
?>
