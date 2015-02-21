<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

class jgiveModelDonations extends JModelLegacy
{
 	var $_data;
 	var $_total = null;
 	var $_pagination = null;
	/* Constructor that retrieves the ID from the request*/
	function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		// Get the pagination request variables
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );
		$this->setState('limit', $limit); // Set the limit variable for query later on
		$this->setState('limitstart', $limitstart);
	}

	////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////
	//B=>used in backend
	//B
	//F
	function confirmpayment($pg_plugin,$oid)
	{

		$post= JRequest::get('post');
		$comment_present=array_key_exists('comment',$post);
		if($comment_present)
		{
			$this->saveComment($pg_plugin,$oid,$post['comment']);
		}
		$vars = $this->getPaymentVars($pg_plugin,$oid);
		if(!empty($post) && !empty($vars) ){
			JPluginHelper::importPlugin('payment', $pg_plugin);
			$dispatcher = JDispatcher::getInstance();

			if($vars->is_recurring==1)
				$result = $dispatcher->trigger('onTP_ProcessSubmitRecurring', array($post,$vars));
			else
				$result = $dispatcher->trigger('onTP_ProcessSubmit', array($post,$vars));
		}
		else{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JGIVE_SOME_ERROR_OCCURRED'), 'error');
		}
		return true;
	}
	function saveComment($pg_plugin,$oid,$comment)
	{
		if($oid)
		{
			$obj = new stdClass();
			$db=JFactory::getDBO();
			$query="SELECT donation_id FROM #__jg_orders WHERE id =".$oid;
			$db->setQuery($query);

			$obj->id=$db->loadResult();
			$obj->comment=$comment;
			if($obj->id)
			{
				if(!$db->updateObject( '#__jg_donations',$obj,'id'))
				{
					echo $db->stderr();
				}
			}
		}
	}
	function getDonations()
	{
		if(empty($this->_data))
		{
			$query=$this->_buildQuery();
			$this->_data=$this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_data;
	}

	//B
	//F
	function _buildQuery()
	{
		$db=JFactory::getDBO();
		global $mainframe,$option;
		$mainframe=JFactory::getApplication();
		$option=JFactory::getApplication()->input->get('option');

		//Get the WHERE and ORDER BY clauses for the query
		$where='';
		$where=$this->_buildContentWhere();

		$query="SELECT i.id, i.order_id, i.fund_holder, i.status, i.processor, i.amount, i.fee, i.cdate, d.user_id AS donor_id, c.id AS cid, c.title,dona.comment
		FROM #__jg_orders AS i
		LEFT JOIN #__jg_campaigns AS c ON c.id=i.campaign_id
		LEFT JOIN #__jg_donors AS d on d.id=i.donor_id
		LEFT JOIN #__jg_donations AS dona ON dona.id=i.donation_id".
		$where;

		$filter_order=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','cdate','cmd');
		$filter_order_Dir=$mainframe->getUserStateFromRequest($option.'filter_order_Dir','filter_order_Dir','desc','word');

		if($filter_order)
		{
			$qry1="SHOW COLUMNS FROM #__jg_orders";
			$db->setQuery($qry1);
			$exists1=$db->loadobjectlist();
			foreach($exists1 as $key1=>$value1)
			{
				$allowed_fields[]=$value1->Field;
			}
			if(in_array($filter_order,$allowed_fields)){
				$query.=" ORDER BY i.$filter_order $filter_order_Dir";
			}
		}
		return $query;
	}

	//B
	//F
	function _buildContentWhere()
	{
		global $mainframe,$option;
		$mainframe=JFactory::getApplication();
 		$option=JFactory::getApplication()->input->get('option');
 		$layout=JFactory::getApplication()->input->get('layout','all');
 		$cid=JFactory::getApplication()->input->get('cid',0);

		$db=JFactory::getDBO();
		$payment_status=$mainframe->getUserStateFromRequest($option.'payment_status','payment_status','','string');
		$where=array();
		//add filter for showing only logged in users donations
		if($layout=='my')
		{
			$me=JFactory::getuser();
			$where[]=' d.user_id='.$me->id;
		}
		if($payment_status=='P' || $payment_status=='C' || $payment_status=='RF')
		{
			$where[]=' i.status = '.$this->_db->Quote($payment_status);
		}
		if($layout=='all')
		{
			if($cid!=0){//this is used when redirected from other view to this view
				$where[]=" c.id=".$cid;
			}
			else
			{
				$filter_campaign=$mainframe->getUserStateFromRequest($option.'filter_campaign','filter_campaign','','string');
				if($filter_campaign!=0){
					$where[]=" c.id=".$filter_campaign;
				}
				$filter_campaign_type=$mainframe->getUserStateFromRequest($option.'filter_campaign_type','filter_campaign_type','','string');
				if(!empty($filter_campaign_type))
				{
					$where[]=" c.type='$filter_campaign_type'";
				}
			}
		}

		return $where=(count($where)?' WHERE '. implode(' AND ',$where ):'');
	}

	//B
	//F
	function getTotal()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	//B
	//F
	function getPagination()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}


	#########################################################
	#################single donation details#################
	#########################################################
	//B
	//F
	//used in donation details view
	function getSingleDonationInfo()
	{
		$order_id_key=JFactory::getApplication()->input->get('donationid');
		//$order_id_key=base64_decode($order_id_key);
		$path=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php';
		if(!class_exists('donationsHelper'))
		{
			JLoader::register('donationsHelper', $path );
			JLoader::load('donationsHelper');
		}
		$donationsHelper=new donationsHelper();
		$donation_details=$donationsHelper->getSingleDonationInfo($order_id_key);
		//print_r($donation_details);
		return $donation_details;
	}


	/////////////////////////////////////
	//frontend functions
	/////////////////////////////////////

	//sets campaign & giveback id in session
	function setSessionCampaignId($cid,$giveback_id='')
	{
		$session=JFactory::getSession();
		$this->clearSessionCampaignId();
		$session->set('JGIVE_cid', $cid);

		if(!empty($giveback_id))
		{
			$session->set('JGIVE_giveback_id', $giveback_id);
		}

		return true;
	}

	//clears campaign id from session
	function clearSessionCampaignId()
	{
		$session = JFactory::getSession();
		//$session->set('JGIVE_amount','');
		$session->set('JGIVE_cid','');
		return true;
	}

	//get campaign id from session
	function getCampaignId()
	{
		$session=JFactory::getSession();
		//$input=JFactory::getApplication()->input;
		//$post=
		$post=JRequest::get('post');
		//@TODO
		if(empty($post['cid']))
			$cid=$session->get('JGIVE_cid');
		else
			$cid=$post['cid'];
		return $cid;
	}

	//sets donor details in session
	function setSessionDonorData($post)
	{
		$session=JFactory::getSession();
		$session->set('JGIVE_cid', $post->get('cid','','INT'));
		//donor data
		$session->set('JGIVE_first_name', $post->get('first_name','','STRING'));
		$session->set('JGIVE_last_name', $post->get('last_name','','STRING'));
		$session->set('JGIVE_paypal_email', $post->get('paypal_email','','STRING'));
		$session->set('JGIVE_address', $post->get('address','','STRING'));
		$session->set('JGIVE_address2', $post->get('address2','','STRING'));
		$session->set('JGIVE_city', $post->get('city','','STRING'));
		$session->set('JGIVE_other_city', $post->get('other_city','','STRING'));
		$session->set('JGIVE_state', $post->get('state','','STRING'));
		$session->set('JGIVE_country', $post->get('country','','STRING'));
		$session->set('JGIVE_zip', $post->get('zip','','STRING'));
		$session->set('JGIVE_phone', $post->get('phone','','STRING'));
		$session->set('JGIVE_donation_amount', $post->get('donation_amount','','STRING'));
		$session->set('No_first_donation',1);
		return true;
	}

	//adds order in order table
	function addOrder($post)
	{

		//get params
		$session=JFactory::getSession();
		$campaignHelper=new campaignHelper();
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$params=JComponentHelper::getParams('com_jgive');
		$commission_fee=$params->get('commission_fee');
		$fixed_commission_fee=$params->get('fixed_commissionfee');
		if(empty($fixed_commission_fee))
		{
			$fixed_commission_fee=0;
		}
		$send_payments_to_owner=$params->get('send_payments_to_owner');
		$db=JFactory::getDBO();
		$user=JFactory::getUser();

		//get the user groupwise commision form params
		$params_usergroup=$params->get('usergroup');

		//get campaign type donation/Investment & its creator
		$camp_details=$campaignHelper->getCampaignType($post->get('cid','','INT'));
		//echo "<pre>";print_r($camp_details);echo "</pre>";die;
		//get Campaign creator groups ids
		$campaign_creator =JFactory::getUser($camp_details->creator_id);
		$camp_creator_groups_ids=$campaign_creator->groups;

		//get logged user id
		$userid=$user->id;

		$this->guest_donation=$params->get('guest_donation');

		if($this->guest_donation)
		{
			if(!$userid)
			{
				$userid=0;
				$donor_registration=$post->get('account','','STRING');
				if($donor_registration=='register')
				{
					$regdata['user_name']=$post->get('paypal_email','','STRING');
					$regdata['user_email']=$post->get('paypal_email','','STRING');
					JLoader::import('registration', JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'models');
					$jgiveModelregistration =  new jgiveModelregistration();
					$mesage=$jgiveModelregistration->store($regdata);

					if($mesage){
						$user = JFactory::getUser();
						$userid=$user->id;
					}
					else{
						return -1;
					}
				}
				else if(!($donor_registration=='guest'))
				{
					return false;
				}
				$session->set('quick_reg_no_login','1');
			}
		}
		else if(!$userid)
		{
			$userid=0;
			return false;
		}

		//*************************START save donor details***********************************

		//save donor details
		$obj = new stdClass();

		$JGIVE_order_id=$session->get('JGIVE_order_id');

		$obj->id='';
		if(!empty($JGIVE_order_id))
		{
			$db=JFactory::getDBO();
			$query="SELECT donor_id
				FROM #__jg_orders
				WHERE id=".$JGIVE_order_id;

			$db->setQuery($query);
			$obj->id=$db->loadResult();
		}
		$obj->user_id=$userid;
		$obj->campaign_id=$post->get('cid','','INT');

		$obj->email=$post->get('paypal_email','','STRING');
		$obj->first_name=$post->get('first_name','','STRING');
		$obj->last_name=$post->get('last_name','','STRING');
		$obj->address=$post->get('address','','','STRING');
		$obj->address2=$post->get('address2','','STRING');

		$other_city_check=$post->get('other_city_check','','STRING');
		if(!empty($other_city_check))
		{
			$obj->city=$post->get('other_city','','STRING');
		}
		else if(($post->get('city','','STRING')) && ($post->get('city','','STRING')!=''))
		{
			$obj->city=$jgiveFrontendHelper->getCityNameFromId($post->get('city','','STRING'),$post->get('country','','STRING'));
		}
		//use helper to save country and state
		//Condition added by Sneha, allow only when country is present(giving error)
		if(($post->get('country','','STRING')) && ($post->get('country','','STRING')!=''))
		{
			$obj->country=$jgiveFrontendHelper->getCountryNameFromId($post->get('country','','STRING'));
		}
		if(($post->get('state','','STRING')) && ($post->get('state','','STRING')!=''))
		{
			$obj->state=$jgiveFrontendHelper->getRegionNameFromId($post->get('state','','STRING'),$post->get('country','','STRING'));
		}

		$obj->zip=$post->get('zip','','STRING');
		$obj->phone=$post->get('phone','','STRING');

		if($obj->id)
		{
			if(!$db->updateObject( '#__jg_donors',$obj,'id'))
			{
				echo $db->stderr();
				return false;
			}
		}
		else if(!$db->insertObject( '#__jg_donors',$obj,'id'))
		{
			echo $db->stderr();
			return false;
		}

		//get last insert id
		if($obj->id)
			$donors_key=$obj->id;
		else
			$donors_key=$db->insertid();

		//*************************EOF save donor details***********************************



		//*************************START save donations details***********************************
		$obj = new stdClass();
		$obj->id='';
		if($JGIVE_order_id)
		{
			$db=JFactory::getDBO();
			$query="SELECT donation_id FROM #__jg_orders WHERE id =".$JGIVE_order_id;
			$db->setQuery($query);
			$obj->id=$db->loadResult();
		}
		$obj->campaign_id=$post->get('cid');
		$obj->donor_id=$donors_key;
		//$obj->order_id=$orders_key;
		if($post->get('donation_type','','INT'))
		{
			$obj->is_recurring=1;
			$obj->recurring_frequency=$post->get('recurring_freq','','STRING');
			$obj->recurring_count=$post->get('recurring_count','','INT');
		}
		else
		{
			$obj->is_recurring=0;
			$obj->recurring_frequency='';
			$obj->recurring_count='';
		}

		$obj->annonymous_donation=$post->get('publish','','INT');

		$no_giveback = $post->get('no_giveback','','INT');

		//Check donor not checked no giveback option
		if(!$no_giveback)
		{
			$obj->giveback_id = $post->get('givebacks','','INT');
		}
		else
		{
			$obj->giveback_id = 0;
		}


		if($obj->id)
		{
			if(!$db->updateObject( '#__jg_donations',$obj,'id'))
			{
				echo $db->stderr();
				return false;
			}
		}
		else if(!$db->insertObject( '#__jg_donations',$obj,'id'))
		{
			echo $db->stderr();
			return false;
		}

		if($obj->id)
		{
			$donation_id=$obj->id;
		}
		else
		{
			$donation_id=$db->insertid();
		}
		//************************EOF save donations details***********************************


		//*************************START save order details************************************

		//save order details
		$obj = new stdClass();
		$obj->id='';
		if($JGIVE_order_id)
			$obj->id=$JGIVE_order_id;
		/*##############################################################*/
		// Lets make a random char for this order
		//take order prefix set by admin
		$order_prefix=(string)$params->get('order_prefix');
		$order_prefix=substr($order_prefix,0,5);//string length should not be more than 5
		//take separator set by admin
		$separator=(string)$params->get('separator');
		$obj->order_id=$order_prefix.$separator;
		//check if we have to add random number to order id
		$use_random_orderid=(int)$params->get('random_orderid');
		if($use_random_orderid)
		{
			$random_numer=$this->_random(5);
			$obj->order_id.=$random_numer.$separator;
			//this length shud be such that it matches the column lenth of primary key
			//it is used to add pading
			$len=(23-5-2-5);//order_id_column_field_length - prefix_length - no_of_underscores - length_of_random number
		}else{
			//this length shud be such that it matches the column lenth of primary key
			//it is used to add pading
			$len=(23-5-2);//order_id_column_field_length - prefix_length - no_of_underscores
		}
		/*##############################################################*/
		$obj->campaign_id=$post->get('cid','','INT');
		$obj->donor_id=$donors_key;
		$obj->donation_id=$donation_id;
		$obj->cdate=date("Y-m-d H:i:s");
		$obj->mdate=date("Y-m-d H:i:s");
		$obj->transaction_id='';//$post->get('address');

		$donation_amount = $post->get('donation_amount','','STRING');

		$amount_separator = $params->get('amount_separator');

		if(!empty($amount_separator))
		{
			$donation_amount=str_replace($amount_separator,'.',$donation_amount);
		}

		$obj->original_amount=$donation_amount;
		$obj->amount=$donation_amount;

		$obj->fee=0;

		if(!$send_payments_to_owner)
		{
			if(!empty($params_usergroup))
			{
				$count=count($params_usergroup);
				for($l=0;$l<$count;$l=$l+3)
				{
					if(in_array($params_usergroup[$l],$camp_creator_groups_ids))
					{
						if($camp_details->type=='donation')
						{
							$commission_fee=(int)($params_usergroup[$l+1]);
						}
						else if($camp_details->type=='investment')
						{
							$commission_fee=(int)($params_usergroup[$l+2]);
						}
						break;
					}
				}
			}

			if($commission_fee>0){
				$obj->fee=(($obj->amount*$commission_fee)/100)+$fixed_commission_fee;
			}
		}
		$obj->fund_holder=0;
		$obj->vat_number=$post->get('vat_number','','STRING');
		if($send_payments_to_owner)
		{
			$obj->fund_holder=1;//money for this order will goto campaign promoters account
		}
		$obj->status='P';//by default pending status
		$obj->processor=$post->get('gateways','','STRING');

		//Get the IP Address
		if(!empty($_SERVER['REMOTE_ADDR'])){
			$ip=$_SERVER['REMOTE_ADDR'];
		}else{
			$ip='unknown';
		}
		$obj->ip_address=$ip;

		if($obj->id)
		{
			if(!$db->updateObject( '#__jg_orders',$obj,'id'))
			{
				echo $db->stderr();
				//return false;
			}
		}
		else if(!$db->insertObject( '#__jg_orders',$obj,'id'))
		{
			echo $db->stderr();
			//return false;
		}

		//get last insert id
		if($JGIVE_order_id)
		{
			$orders_key=$JGIVE_order_id;
		}
		else
		{
			$orders_key=$db->insertid();
		}

		//*************************EOF save order details************************************

		//set order id in session
		$session=JFactory::getSession();
		$session->set('JGIVE_order_id',$orders_key);

		/*##############################################################*/
		$db->setQuery('SELECT order_id FROM #__jg_orders WHERE id='.$orders_key);
		$order_id=(string)$db->loadResult();
		$maxlen=23-strlen($order_id)-strlen($orders_key);
		$padding_count=(int)$params->get('padding_count');
		//use padding length set by admin only if it is les than allowed(calculate) length
		if($padding_count>$maxlen){
			$padding_count=$maxlen;
		}
		if(strlen((string)$orders_key)<=$len)
		{
			$append='';
			for($z=0;$z<$padding_count;$z++){
				$append.='0';
			}
			$append=$append.$orders_key;
		}

		$res=new stdClass();
		$res->id=$orders_key;
		$order_id=$res->order_id=$order_id.$append;//imp

		if(!$db->updateObject( '#__jg_orders', $res, 'id' ))
		{
			//return false;
		}
		/*##############################################################*/

		// Check if email is to be sent for new orders.
		$send_mail_new_order = $params->get('send_mail_new_order', 1);
		if($send_mail_new_order)
		{
			require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."donations.php");
			$donationsHelper = new donationsHelper();
			$donationsHelper->sendOrderEmail($orders_key, $post->get('cid', '', 'INT'));
		}
		return true;
	}

	/**
	 * Params @
	 * pg_plugin => plugin name
	 * tid = order id primary key
	*/

	function getPaymentVars($pg_plugin, $tid)
	{
		$campaignHelper=new campaignHelper();
		$vars=new stdclass();
		$params=JComponentHelper::getParams('com_jgive');
		$currency_code=$params->get('currency');
		$send_payments_to_owner=$params->get('send_payments_to_owner');

		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."campaign.php");

		$pass_data=$this->getdetails($tid);
		//var_dump($pass_data);

		$vars->order_id=$pass_data->order_id;
		//get the campaign promoter paypal email id
		$CampaignPromoterPaypalId=$campaignHelper->getCampaignPromoterPaypalId($tid);

		$session=JFactory::getSession();
		$session->set('order_id',$tid);

		$vars->client='com_jgive';

		if($pg_plugin=='paypal')
		{
			// Lets set the paypal email if admin is not handling transactions
			if($send_payments_to_owner){
				$vars->business=$campaignHelper->getCampaignPromoterPaypalId($tid);
			}
			//in case of donations
			if($pass_data->is_recurring==1)
				$vars->cmd='_xclick-subscriptions';
			else
				$vars->cmd='_donations';
		}

		$vars->user_firstname=$pass_data->first_name;
		$vars->user_id=JFactory::getUser()->id;
		$vars->user_email=$pass_data->email;//JFactory::getUser()->email;//@TODO which email to use exactly?

		$this->guest_donation=$params->get('guest_donation');
		$guest_email='';
		if($this->guest_donation)
		{
			if(!$vars->user_id)
			{
				$vars->user_id=0;
				$session=JFactory::getSession();
				$session->set('quick_reg_no_login','1');
				$guest_email='';
				$guest_email= md5($vars->user_email);
				$session=JFactory::getSession();
				$session->set('guest_email',$guest_email);
			}
		}

		$vars->item_name=$campaignHelper->getCampaignTitle($tid);

		// Added for payment description.
		$donationsHelper = new donationsHelper();
		$cid  = $donationsHelper->getCidFromOrderId($tid);
		$link = '<a href="' . JUri::root() .substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid=' . $cid), strlen(JUri::base(true))+1) . '">' . $vars->item_name . '</a>';
		$vars->payment_description = JText::sprintf('COM_JGIVE_PAYMENT_DESCRIPTION', $link);

//$vars->return=JRoute::_(JUri::root()."index.php?option=com_jgive&view=donations&layout=details&donationid=".md5($tid)."&processor={$pg_plugin}");
		$vars->submiturl = JRoute::_("index.php?option=com_jgive&controller=donations&task=confirmpayment&processor={$pg_plugin}&order_id=".$vars->order_id);
		$vars->return=JRoute::_(JUri::root()."index.php?option=com_jgive&view=donations&layout=details&donationid=".$pass_data->id."&processor=".$pg_plugin."&email=".$guest_email);
		$vars->cancel_return=JRoute::_(JUri::root()."index.php?option=com_jgive&view=donations&layout=details&donationid=".$pass_data->id."&processor=".$pg_plugin."&email=".$guest_email);
		$vars->url=$vars->notify_url=JRoute::_(JUri::root()."index.php?option=com_jgive&controller=donations&task=processPayment&processor={$pg_plugin}&order_id=".$vars->order_id);
		$vars->campaign_promoter=$CampaignPromoterPaypalId;
		$vars->currency_code=$currency_code;

		//$vars->amount=$session->get('JGIVE_donation_amount');
		$amount_separator = $params->get('amount_separator');
		if(!empty($amount_separator))
		{
			$donation_amount = $session->get('JGIVE_donation_amount');
			$donation_amount = str_replace($amount_separator,'.',$donation_amount);
			$vars->amount=$donation_amount;
		}
		else
		{
			$vars->amount=$session->get('JGIVE_donation_amount');
		}

		$vars->is_recurring=$pass_data->is_recurring;
		$vars->recurring_frequency	=$pass_data->recurring_frequency;
		$vars->recurring_count	=$pass_data->recurring_count;

		//For language specific paypal
		$user_data=JFactory::getUser();
		$vars->country_code='';
		$user_language=$user_data->getParam('language');
		if(!empty($user_language)) // User language available in db
		{
			$user_language=str_replace('-','_',$user_language);
			$vars->country_code=$user_language;
		}
		else // Pass location if language not available for user in db
		{
			$vars->country_code=$pass_data->country_code;
		}

		$vars->adaptiveReceiverList = $this->getReceiverList($vars,$pg_plugin);

		return $vars;
	}

	/**
	 * @params
	 * 	Payment vars => payment variables
	 * 	$pg_plugin 	=> plugin name e.g adapative_paypal
	 *
	 */

	function getReceiverList($vars,$pg_plugin)
	{
	// GET BUSINESS EMAIL
		$plugin = JPluginHelper::getPlugin( 'payment', $pg_plugin);
		$pluginParams = json_decode( $plugin->params );
		$businessPayEmial = "";
		if (property_exists($pluginParams, 'business')) {
			$businessPayEmial= trim($pluginParams->business);
		}
		else {
			return array();
		}
		$params=JComponentHelper::getParams('com_jgive');

		$paymentsToOwnerWithoutApplyCommission=$params->get('send_payments_to_owner',0);

		if ($pg_plugin == 'adaptive_paypal')
		{
			//send payment to campaign promoter without charging any commision

			if($paymentsToOwnerWithoutApplyCommission)
			{
				$AmountToPayToPromoter=$vars->amount;
			}
			else
			{
				$donationsHelper = new donationsHelper;
				$fee = $donationsHelper->getFee($vars->order_id);
				$AmountToPayToPromoter=$vars->amount-$fee;
			}

			// Get site admin paypal bussiness account email address
			$plugin = JPluginHelper::getPlugin( 'payment', $pg_plugin);
			$pluginParams = json_decode( $plugin->params );
			$siteAdminBussineessAcountEmail = "";

			if (property_exists($pluginParams, 'business'))
			{
				$siteAdminBussineessAcountEmail= trim($pluginParams->business);
			}

			$receiverList = array();
			$receiverList[0]=array();
			$receiverList[1]=array();
			// admin has his own products
			$receiverList[0]['receiver'] = $siteAdminBussineessAcountEmail;
			//$receiverList[0]['amount'] = $vars->amount;
			$receiverList[0]['amount'] = $fee;
			$receiverList[0]['primary'] = false;

			// add other receivers
			$receiverList[1]['receiver'] = $vars->campaign_promoter;
			//$receiverList[1]['amount'] = $AmountToPayToPromoter;
			$receiverList[1]['amount'] = $vars->amount;
			$receiverList[1]['primary'] = true;

		return $receiverList;
		}
		return ;
	}

	//loads payment plugin gateway html
	function getHTML($pg_plugin,$tid)
	{
		$vars = $this->getPaymentVars($pg_plugin,$tid);
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('payment', $pg_plugin);
		$html=$dispatcher->trigger('onTP_GetHTML',array($vars));
		return $html;
	}

	function getdetails($tid)
	{
		$user_id=JFactory::getUser()->id;

		if(!$user_id)
		{
			$query="SELECT o.id, o.order_id, d.first_name, d.email, d.phone,
			 ds.is_recurring, ds.recurring_frequency, ds.recurring_count,
			 country.country_code
			 FROM #__jg_orders AS o
			 LEFT JOIN #__jg_donors AS d ON d.id=o.donor_id
			 LEFT JOIN #__jg_donations as ds ON ds.id=o.donation_id
			 LEFT JOIN #__tj_country as country ON country.country=d.country
			 WHERE o.id='" . $tid . "'";
			 //*WHERE o.id='" . $tid . "' AND d.user_id=" . $user_id;

			$this->_db->setQuery($query);
			$orderdetails=$this->_db->loadObjectlist();

			return $orderdetails['0'];
		}

		$query="SELECT o.id, o.order_id, d.first_name, d.email, d.phone,
		 ds.is_recurring, ds.recurring_frequency, ds.recurring_count,
		 country.country_code
		 FROM #__jg_orders AS o
		 LEFT JOIN #__jg_donors AS d ON d.id=o.donor_id
		 LEFT JOIN #__jg_donations as ds ON ds.id=o.donation_id
		 LEFT JOIN #__tj_country as country ON country.country=d.country
		 WHERE o.id='" . $tid . "' AND d.user_id=" . $user_id;

		$this->_db->setQuery($query);
		$orderdetails=$this->_db->loadObjectlist();

		return $orderdetails['0'];
	}

	//load campaign data for campaign id stored in session
	function getCampaign()
	{
		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."campaign.php");
		$campaignHelper=new campaignHelper();
		$cid=$this->getCampaignId();

		$query="SELECT c.*
		FROM `#__jg_campaigns` AS c
		WHERE c.id=".$cid;
		$this->_db->setQuery($query);
		$campaign=$this->_db->loadObject();
		$cdata['campaign']=$campaign;

		//get campaign amounts
		$amounts=$campaignHelper->getCampaignAmounts($cid);
		$cdata['campaign']->amount_received=$amounts['amount_received'];
		$cdata['campaign']->remaining_amount=$amounts['remaining_amount'];

		//get campaign images
		$cdata['images']=$campaignHelper->getCampaignImages($cid);
		//get campaign givebacks
		$cdata['givebacks']=$campaignHelper->getCampaignGivebacks($cid);
		return $cdata;
	}

	function processPayment($post,$pg_plugin,$order_id)
	{
		//$filename = JPATH_SITE.'/'.'response.txt';
		//file_put_contents (  $filename ,json_encode($post),FILE_APPEND);

		//$filename = JPATH_SITE.'/'.'response.txt';
		//file_put_contents (  $filename ,json_encode($pg_plugin),FILE_APPEND);

		//$filename = JPATH_SITE.'/'.'response.txt';
		//file_put_contents (  $filename ,json_encode($order_id),FILE_APPEND);

		//get id for orders table using order_id
		require_once(JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php');
		$donationsHelper=new donationsHelper();
		$order_id_key=$donationsHelper->getOrderIdKeyFromOrderId($order_id);

		$comment_present=array_key_exists('comment',$post);
		if($comment_present)
		{
			$this->saveComment($pg_plugin,$order_id_key,$post['comment']);
		}

		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$session=JFactory::getSession();
		$guest_email='';
		$guest_email=$session->get('guest_email');
		$session->clear('guest_email');
		$session->set('order_link_guestemail',$guest_email);
		//load donations helper

		JRequest::setVar('remote',1);
		$donationItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations');

		$return_resp=array();

		//Authorise Post Data
		if($post['plugin_payment_method']=='onsite'){
			$plugin_payment_method=$post['plugin_payment_method'];
		}

		//get id for orders table using order_id
		$order_id_key=$donationsHelper->getOrderIdKeyFromOrderId($order_id);

		$vars = $this->getPaymentVars($pg_plugin,$order_id_key);

		//trigger payment plugins- onTP_Processpayment
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('payment',$pg_plugin);

		$data=$dispatcher->trigger('onTP_Processpayment',array($post,$vars));

		$data=$data[0];

		//store log
		$res=@$this->storelog($pg_plugin,$data);

		//get order id
		if(empty($order_id)){
			$order_id=$data['order_id'];
		}

		//get id for orders table using order_id
		$order_id_key=$donationsHelper->getOrderIdKeyFromOrderId($order_id);

		$OrderStatusAndTransactoinId=$donationsHelper->getOrderTransactoionIdAndStatus($order_id_key);

		//check is repetative same response from paypal & if yes then don't send email notification
		$duplicate_response=0;
		if($OrderStatusAndTransactoinId)
		{
			if(($OrderStatusAndTransactoinId->status==$data['status']) AND ($OrderStatusAndTransactoinId->transaction_id==$data['transaction_id']) )
			{
				$duplicate_response=1;
			}
		}


		//$order_id_key=48; //To remove
		//gateway used
		$data['processor']=$pg_plugin;
		//payment status
		$data['status']=trim($data['status']);

		//get order amount
		$query="SELECT o.original_amount
				FROM #__jg_orders as o
				where o.id=".$order_id_key;
		$this->_db->setQuery($query);
		$order_amount=$this->_db->loadResult();
		//return url
		$return_resp['return']=$data['return'];

		if($data['status']=='C' && $order_amount == $data['total_paid_amt'])//if payment status is confirmed
		{
			//if($processed==0)
			//{
			//update order details
			$this->updateOrder($data);
			//update order status, send email,
			//$donationsHelper->updatestatus($data['order_id'],$data['status'],$comment='',$notify_chk=1);
			$donationsHelper->updatestatus($order_id_key,$data['status'],$comment='',$notify_chk=1,$duplicate_response);
			if($data['status'] == 'C')
			{
				$donationsHelper->getSoldGivebacks($order_id_key);
			}
			//Trigger plugins
			//OnAfterJGivePaymentSuccess
			$dispatcher=JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');
			$result=$dispatcher->trigger('OnAfterJGivePaymentSuccess',array($order_id_key));//Call the plugin and get the result
			if($result===false){
				//return false;
			}
			//END plugin triggers

			// Added guest email in url for payment processs on site
			$return_resp['return']=JUri::root().substr(JRoute::_("index.php?option=com_jgive&view=donations&layout=details&donationid=".$order_id_key."&processor={$pg_plugin}&email=".$guest_email."&Itemid=".$donationItemid,false),strlen(JUri::base(true))+1);
			//}
			$return_resp['status']='1';
		}
		else if($order_amount != $data['total_paid_amt']){
			$data['status'] = 'E';
			$return_resp['status']='0';

		}
		else if(!empty($data['status']))
		{
			// Added guest email in url for payment processs on site
		 	if($plugin_payment_method &&  $data['status']=='P'){
		 		$return_resp['return']=JUri::root().substr(JRoute::_("index.php?option=com_jgive&view=donations&layout=details&donationid=".$order_id_key."&processor={$pg_plugin}&email=".$guest_email."&Itemid=".$donationItemid,false),strlen(JUri::base(true))+1);
		 	}/*
		 	else{
				$return_resp['return']=JUri::root().substr(JRoute::_("index.php?option=com_jgive&view=donations&layout=confirm&Itemid=".$chkoutItemid,false),strlen(JUri::base(true))+1);
			}*/

			if($data['status']!='C' )
			{
				$this->updateOrder($data);
				//comquick2cartHelper::updatestatus($data['order_id'],$data['status']);
			}
			else if($data['status']=='C'){
				// Added guest email in url for payment processs on site
				$return_resp['return']=JUri::root().substr(JRoute::_("index.php?option=com_jgive&view=donations&layout=cancel&processor={$pg_plugin}&email=".$guest_email."&Itemid=".$chkoutItemid),strlen(JUri::base(true))+1);
			}
			$return_resp['status']='0';
			$res->processor=$data['processor'];  //@TODO where is this  used ???
			$return_resp['msg']=$data['error']['code']." ".$data['error']['desc'];
		}
		$res=trim($return_resp['msg']);
		if(!$res AND $pg_plugin=='bycheck')
		{
			$return_resp['msg']=JText::_('COM_JGIVE_ORDER_PLACED_NOTIFICATION');
		}

		// Update campaign success status.
		$campaignHelper = new campaignHelper();
		$campaignHelper->updateCampaignSuccessStatus($cid=0, $campaignSuccessStatus=NULL, $order_id_key);

		// Update campaign processed flag.
		$campaignHelper->updateCampaignProcessedFlag($cid=0, $campaignProcessedFlag=NULL, $order_id_key);

		return $return_resp;
	}

	function storelog($name,$data)
	{
		$data1=array();
		$data1['raw_data']=$data['raw_data'];
		$data1['JT_CLIENT']="com_jgive";
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('payment',$name);
		$data=$dispatcher->trigger('onTP_Storelog',array($data1));
	}

	function updateOrder($data)
	{
		//load donations helper
		require_once(JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php');
		$donationsHelper=new donationsHelper();

		//get id for orders table using order_id

		//$data['order_id']="JGOID-00048"; //@ To remove

		$order_id_key=$donationsHelper->getOrderIdKeyFromOrderId($data['order_id']);

		//get donation id
		$db=JFactory::getDBO();
		$query="SELECT donation_id
		FROM #__jg_orders where id=".$order_id_key;
		$db->setQuery($query);
		$donation_id=$db->loadResult();

		$db=JFactory::getDBO();
		$query="SELECT subscr_id,is_recurring
		FROM #__jg_donations where id=".$donation_id;
		$db->setQuery($query);
		$donation_details=$db->loadObject();

		//if subscriber id is not exist then it is first response from paypal
		//hence update donation table insert subsriber id & also update transaction id

		if($donation_details->is_recurring)
		{
			if($data['txn_type']=='subscr_payment') // For recurring payment
			{
				if(empty($donation_details->subscr_id)) //insert subscriber id in donation table if not exits (Recurring donations)
				{
					$db=JFactory::getDBO();
					$res=new stdClass();
					$res->id=$donation_id;
					$res->subscr_id=$data['subscriber_id'];
					if(!$db->updateObject( '#__jg_donations', $res, 'id' ))
					{
						//return false;
					}

					// Update First order status & transaction id

					$db=JFactory::getDBO();
					$res=new stdClass();
					$res->id=$order_id_key;//$data['order_id'];
					$res->mdate=date("Y-m-d H:i:s");
					$res->transaction_id=$data['transaction_id'];
					$res->status=$data['status'];
					$res->processor=$data['processor'];
					//$res->payee_id=$data['buyer_email'];
					$res->extra=json_encode($data['raw_data']);
					if(!$db->updateObject( '#__jg_orders', $res, 'id' ))
					{
						//return false;
					}
				}
				else // For recurring payment for more responses other than first
				{
					//check the transaction id is present in the order table
					//--------- get transaction id

					$db=JFactory::getDBO();
					$query="SELECT transaction_id
					FROM #__jg_orders where donation_id=".$donation_id;
					$db->setQuery($query);
					$transaction_ids=$db->loadColumn();

					//$data['transaction_id']=1234; //To remove

					//check is transaction id exist in array
					if($transaction_ids[0])
					{
						$flag=0;
						for($i=0;$i<count($transaction_ids);$i++)
						{
							if($transaction_ids[$i]==$data['transaction_id']) // if same transaction id then update the order
							{
								$flag=1; //transaction id already in table
								break;
							}
						}

						if($flag==0) //New transaction
						{
							//order_id campaign_id  donor_id donation_id fund_holder cdate mdate
							//transaction_id transaction_id original_amount amount  fee  status processor ip_address extra
							$this->_addNewRecurringOrder($data,$order_id_key,$donation_id);
						}//update existing transaction
						else {
							$db=JFactory::getDBO();
							$res=new stdClass();
							//$res->id=$order_id_key;//$data['order_id'];
							$res->mdate=date("Y-m-d H:i:s");
							$res->transaction_id=$data['transaction_id'];
							$res->status=$data['status'];
							$res->processor=$data['processor'];
							//$res->payee_id=$data['buyer_email'];
							$res->extra=json_encode($data['raw_data']);
							if(!$db->updateObject( '#__jg_orders', $res, 'transaction_id' ))
							{
								//return false;
							}
						}
					}
				}
			}
		}
		else{
			$db=JFactory::getDBO();
			$res=new stdClass();
			$res->id=$order_id_key;//$data['order_id'];
			$res->mdate=date("Y-m-d H:i:s");
			$res->transaction_id=$data['transaction_id'];
			$res->status=$data['status'];
			$res->processor=$data['processor'];
			//$res->payee_id=$data['buyer_email'];
			$res->extra=json_encode($data['raw_data']);
			if(!$db->updateObject( '#__jg_orders', $res, 'id' ))
			{
				//return false;
			}

			if($data['txn_type']=='Adaptive Payment PAY')// for adaptive payment add entry in payout report
			{
				$this->addPayout($data['raw_data']['paymentInfoList']['paymentInfo'][1], $data['order_id']);
			}
		}
	}
	//add payout entry after adaptive payment from paypal
	function addPayout($data, $order_id)
	{
		//@Amol DO no delete it // sample response form paypal (second receiver (campaign promoter))
		/*$data=array(
		'transactionId' => '111J75396ML51041',
		'transactionStatus' => 'COMPLETED',
		'receiver' => Array
			(
            'amount' => '9.78',
            'email' => 'ahghatol@gmail.com',
            'primary' => 'false',
            'paymentType' => 'SERVICE',
            'accountId' => 'K48UJGXSU3S48'
			),
		'refundedAmount' => '0.00',
		'pendingRefund' => 'false',
		'senderTransactionId' => '648606593G164884A',
		'senderTransactionStatus' => 'COMPLETED'
		);*/
		$camp_promoter_email=$data['receiver']['email'];
		if(!$camp_promoter_email)
			return;

		$db=JFactory::getDBO();
		$query="SELECT creator_id FROM #__jg_campaigns WHERE paypal_email='".$camp_promoter_email."'";
		$db->setQuery($query);
		$camp_promoter_id=$db->loadResult();

		$db=JFactory::getDBO();
		$query="SELECT id FROM #__jg_payouts WHERE transaction_id='".$data['transactionId']."'";
		$db->setQuery($query);
		$payout_id=$db->loadResult();

		$res=new stdClass();

		$db=JFactory::getDBO();
		if($payout_id)
			$res->id=$payout_id;
		else
			$res->id='';

		$res->user_id=$camp_promoter_id;
		$res->payee_name=JFactory::getUser($camp_promoter_id)->name;
		$res->date=date("Y-m-d H:i:s");
		$res->transaction_id=$data['transactionId'];
		$res->email_id=$camp_promoter_email;

		require_once(JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php');
		$donationsHelper = new donationsHelper;
		$fee = $donationsHelper->getFee($order_id);
		$amountPaid=($data['receiver']['amount'])-$fee;

		$res->amount=$amountPaid;

		if($data['transactionStatus']=='COMPLETED')
			$res->status=1;
		else
			$res->status=0;

		$res->ip_address='';
		$res->type='adaptive_paypal';

		if($res->id)
		{
			if(!$db->updateObject( '#__jg_payouts', $res, 'id' ))
			{
			}

		}
		else
		{
			if(!$db->insertObject( '#__jg_payouts', $res, 'id' ))
			{
			}
		}
		return true;
	}
	function _addNewRecurringOrder($data,$order_id_key)
	{
		$donationsHelper=new donationsHelper();
		$donationInfo=$donationsHelper->getSingleDonationInfo($order_id_key);
		//print_r($donationInfo);
		//order_id campaign_id  donor_id donation_id fund_holder cdate mdate
		//transaction_id transaction_id original_amount amount  fee  status processor ip_address extra
		//save order details
		$db=JFactory::getDBO();
		$obj = new stdClass();
		$obj->id='';

		/*##############################################################*/
		// Lets make a random char for this order
		//take order prefix set by admin
		$obj->id='';
		$params=JComponentHelper::getParams('com_jgive');
		$order_prefix=(string)$params->get('order_prefix');
		$order_prefix=substr($order_prefix,0,5);//string length should not be more than 5
		//take separator set by admin
		$separator=(string)$params->get('separator');
		$obj->order_id=$order_prefix.$separator;
		//check if we have to add random number to order id
		$use_random_orderid=(int)$params->get('random_orderid');
		if($use_random_orderid)
		{
			$random_numer=$this->_random(5);
			$obj->order_id.=$random_numer.$separator;
			//this length shud be such that it matches the column lenth of primary key
			//it is used to add pading
			$len=(23-5-2-5);//order_id_column_field_length - prefix_length - no_of_underscores - length_of_random number
		}else{
			//this length shud be such that it matches the column lenth of primary key
			//it is used to add pading
			$len=(23-5-2);//order_id_column_field_length - prefix_length - no_of_underscores
		}
		/*##############################################################*/

		$obj->campaign_id=$donationInfo['campaign']->id;
		$obj->donor_id=$donationInfo['donor']->id;
		$obj->donation_id=$donationInfo['payment']->donation_id;
		$obj->cdate=date("Y-m-d H:i:s");
		$obj->mdate=date("Y-m-d H:i:s");
		$obj->original_amount=$donationInfo['payment']->original_amount;
		$obj->amount=$donationInfo['payment']->amount;

		//Need To Modify
		$obj->fee=$donationInfo['payment']->fee;


		$obj->fund_holder=$donationInfo['payment']->fund_holder;

		$obj->processor=$donationInfo['payment']->processor;

		//Get the IP Address
		$obj->ip_address=$donationInfo['payment']->ip_address;

		$obj->transaction_id=$data['transaction_id'];
		$obj->status==$data['status'];
		$obj->extra=json_encode($data['raw_data']);

		if(!$db->insertObject( '#__jg_orders',$obj,'id'))
		{
			echo $db->stderr();
			//return false;
		}

		$orders_key=$db->insertid();

		if(!$orders_key)
			return 'Error in saving order details';

		$db->setQuery('SELECT order_id FROM #__jg_orders WHERE id='.$orders_key);
		$order_id=(string)$db->loadResult();
		$maxlen=23-strlen($order_id)-strlen($orders_key);
		$padding_count=(int)$params->get('padding_count');
		//use padding length set by admin only if it is les than allowed(calculate) length
		if($padding_count>$maxlen){
			$padding_count=$maxlen;
		}
		if(strlen((string)$orders_key)<=$len)
		{
			$append='';
			for($z=0;$z<$padding_count;$z++){
				$append.='0';
			}
			$append=$append.$orders_key;
		}

		$res=new stdClass();
		$res->id=$orders_key;
		$res->order_id=$order_id.$append;//imp

		if(!$db->updateObject( '#__jg_orders', $res, 'id' ))
		{
			//return false;
		}
	}
	/* function changeOrderStatus start*/
	/*return
	if 1 = success
		2= error
		3 = refund order
	*/
	//b
	function changeOrderStatus()
	{
		$returnvaule=1;
		$data=JRequest::get('post');

		if(isset($data['notify_chk'])){//for email
			$notify_chk=1;
		}else{
			$notify_chk=0;
		}
		if(isset($data['comment']) && $data['comment'] ){
			$comment=$data['comment'];
		}else{
			$comment='';
		}
		//require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php');
		$path=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php';
		if(!class_exists('donationsHelper'))
		{
			JLoader::register('donationsHelper', $path );
			JLoader::load('donationsHelper');
		}
		$donationsHelper=new donationsHelper();
		$donationsHelper->updatestatus($data['id'],$data['status'],$comment,$notify_chk,0);
		$status=$data['status'];   //assign order status
		//Send mail when goal amount for campaign has been reached
		$db=JFactory::getDBO();

		$query = "SELECT campaign_id FROM `#__jg_donations` WHERE id = ".$data['id'];
		$db->setQuery($query);
		$campaign_id = $db->loadColumn();
		$jg_params=JComponentHelper::getParams('com_jgive');
		$send_mail=$jg_params->get('goal_amount_reach');
		if($send_mail == 1 && ($status=='C' || $status=='S'))
		{
			$query = " SELECT SUM( amount ) FROM `#__jg_orders` WHERE campaign_id = ".$campaign_id[0];
			$db->setQuery($query);
			$total = $db->loadColumn();

			$query = " SELECT `goal_amount` FROM `#__jg_campaigns` WHERE `id` = ".$campaign_id[0];
			$db->setQuery($query);
			$goal_amount = $db->loadColumn();
			if($total > $goal_amount)
			{
				$mail_to_admin = $this->MailAdminOnExceedingGoalAmount($campaign_id[0]);
				$mail_to_campaigner = $this->MailCampaignerOnExceedingGoalAmount($campaign_id[0]);
			}
		}

		if($data['status'] == 'C')
		{
			$donationsHelper->getSoldGivebacks($data['id']);
		}
		if($status=='RF')
		{
			$returnvaule=3;
		}

		// Update campaign success status.
		$campaignHelper = new campaignHelper();
		$campaignHelper->updateCampaignSuccessStatus($cid=0, $campaignSuccessStatus=NULL, $data['id']);

		return $returnvaule;
	}//function store ends


	//B
	//called from main controller in backend
	function deleteDonations($odid)
	{
		//check which orders is recurring

		for($i=0;$i<(count($odid));$i++)
		{

			$q="SELECT donation_id FROM  #__jg_orders WHERE id=".$odid[$i];
			$this->_db->setQuery($q);
			$donation_id=$this->_db->loadResult();

			if($donation_id)
			{
				//is_recurring
				$q="SELECT is_recurring FROM  #__jg_donations WHERE id=".$donation_id;
				$this->_db->setQuery($q);
				$is_recurring=$this->_db->loadResult();

				if($is_recurring) // For recurring donations delete only order entry
				{
					//delete from  orders
					$query="DELETE FROM #__jg_orders where id =".$odid[$i];
					$this->_db->setQuery($query);
					if(!$this->_db->execute())
					{
						$this->setError( $this->_db->getErrorMsg() );
						return false;
					}
				}
				else //delete donor, Order, donations
				{
					//delete from donors
					$q="SELECT donor_id FROM  #__jg_donations WHERE id=".$donation_id;
					$this->_db->setQuery($q);
					$donor_id=$this->_db->loadResult();

					$query="DELETE FROM #__jg_donors where id =".$donor_id;
					$this->_db->setQuery($query);
					if (!$this->_db->execute())
					{
						$this->setError( $this->_db->getErrorMsg() );
						return false;
					}

					//delete from  orders
					$query="DELETE FROM #__jg_orders where id =".$odid[$i];
					$this->_db->setQuery($query);
					if(!$this->_db->execute())
					{
						$this->setError( $this->_db->getErrorMsg() );
						return false;
					}

					//delete from donations
					$query="DELETE FROM #__jg_donations where id=".$donation_id;
					$this->_db->setQuery($query);
					if (!$this->_db->execute())
					{
						$this->setError( $this->_db->getErrorMsg() );
						return false;
					}
				}
			}
			else // Support the version of jGive before 1.6 (Recurring payment & One page checkout) release
			{
				//delete from donors
				$q="SELECT donor_id FROM  #__jg_orders WHERE id=".$odid[$i];
				$this->_db->setQuery($q);
				$donor_id=$this->_db->loadResult();

				$query="DELETE FROM #__jg_donors where id =".$donor_id;
				$this->_db->setQuery($query);
				if (!$this->_db->execute())
				{
					$this->setError( $this->_db->getErrorMsg() );
					return false;
				}

				//delete from  orders
				$query="DELETE FROM #__jg_orders where id =".$odid[$i];
				$this->_db->setQuery($query);
				if(!$this->_db->execute())
				{
					$this->setError( $this->_db->getErrorMsg() );
					return false;
				}

				//delete from donations
				$query="DELETE FROM #__jg_donations where order_id=".$odid[$i];
				$this->_db->setQuery($query);
				if (!$this->_db->execute())
				{
					$this->setError( $this->_db->getErrorMsg() );
					return false;
				}
			}
		}
		return true;
	}

	function _random( $length = 17 )
	{
		$salt = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$random = '';

		$stat = @stat(__FILE__);
		if(empty($stat) || !is_array($stat)) $stat = array(php_uname());

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i ++) {
			$random .= $salt[mt_rand(0, $len -1)];
		}

		return $random;
	}

	//frontend
	//since 1.0.3
	function getMinimumAmount($cid)
	{
		$query="SELECT c.minimum_amount
			FROM #__jg_campaigns AS c
			WHERE c.id=".$cid;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	function checkMailExists($mail){

		$mailexist = 0;
		$query = "SELECT id FROM #__users where email  LIKE '".$mail."'";
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();
		if($result){

			$mailexist = 1;
		}
		else{
			$mailexist = 0;
		}

		return $mailexist;
	}
	function MailAdminOnExceedingGoalAmount($camp_id)
	{
		require_once(JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php');
		$jg_params=JComponentHelper::getParams('com_jgive');
		$db=JFactory::getDBO();
		$query = "SELECT * FROM #__jg_campaigns WHERE id = ".$camp_id;
		$db->setQuery($query);
		$campaign_details = $db->loadObjectList();
		//Send mail
		$loguser=JFactory::getUser();
		$app=JFactory::getApplication();
		$mailfrom = $app->getCfg('mailfrom');
		$fromname = $app->getCfg('fromname');
		$sitename = $app->getCfg('sitename');
		$sendto=$jg_params->get('email');
		$subject = JText::_('GOAL_AMOUNT_EXCEED_SUBJECT');
		$subject	= str_replace('{fundraiser_name}', $campaign_details[0]->title, $subject);

		$body 	= JText::_('EXCEED_GOAL_AMOUNT');
		$body	= str_replace('{fundraiser_name}', $campaign_details[0]->title, $body);
		$body	= str_replace('{sitename}', $sitename, $body);
		//$res=Quick2cartHelperUser::_doMail($mailfrom,$fromname,$sendto,$subject,$body);
		$donationsHelper = new donationsHelper();
		$res = $donationsHelper->sendmail($sendto,$subject,$body,'');
	}

	function MailCampaignerOnExceedingGoalAmount($camp_id)
	{
		require_once(JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php');
		$db=JFactory::getDBO();
	 	$query = "SELECT DISTINCT (u.`email`), c.* FROM `#__users` AS u
		LEFT JOIN #__jg_campaigns AS c ON u.id = c.creator_id
		LEFT JOIN #__jg_donations AS d ON d.campaign_id = c.id
		WHERE d.campaign_id = ".$camp_id;
		$db->setQuery($query);
		$creator = $db->loadObjectList();
		//Send mail
		$loguser=JFactory::getUser();
		$app=JFactory::getApplication();
		$mailfrom = $app->getCfg('mailfrom');
		$fromname = $app->getCfg('fromname');
		$sitename = $app->getCfg('sitename');
		$sendto=$creator[0]->email;

		$subject = JText::_('GOAL_AMOUNT_EXCEED_CAMPAIGNER_SUBJECT');
		$subject	= str_replace('{fundraiser_name}', $creator[0]->title, $subject);

		$body 	= JText::_('EXCEED_GOAL_AMOUNT_BODY');
		$body	= str_replace('{fundraiser_name}', $creator[0]->title, $body);
		$body	= str_replace('{sitename}', $sitename, $body);

		//$res=Quick2cartHelperUser::_doMail($mailfrom,$fromname,$sendto,$subject,$body);
		$donationsHelper = new donationsHelper();
		$res = $donationsHelper->sendmail($sendto,$subject,$body,'');
		return true;;
	}
}
