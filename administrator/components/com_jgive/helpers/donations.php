<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

class donations_backendHelper
{





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

	//sets campaign id in session
	function setSessionCampaignId($post)
	{
		$session=JFactory::getSession();
		$this->clearSessionCampaignId();
		$session->set('JGIVE_cid', $post['cid']);
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


	//adds order in order table
	function addOrder($post)
	{
		$path=JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'campaign.php';
		if(!class_exists('donations_backendHelper'))
		{
			JLoader::register('donations_backendHelper', $path );
			JLoader::load('donations_backendHelper');
		}
		$path=JPATH_ROOT.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'campaign.php';
		if(!class_exists('campaignHelper'))
		{
			JLoader::register('campaignHelper', $path );
			JLoader::load('campaignHelper');
		}

		$path=JPATH_ROOT.DS.'components'.DS.'com_jgive'.DS.'helper.php';
		if(!class_exists('jgiveFrontendHelper'))
		{
			JLoader::register('jgiveFrontendHelper', $path );
			JLoader::load('jgiveFrontendHelper');
		}
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$campaignHelper=new campaignHelper();

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

		$donorid=$post->get('donor_id','','STRING');
		//Get Checkout Type Registerd/Guest 0=Registerd 1=Guest
		$checkout_type=$post->get('checkout_type','','STRING');
		$user=JFactory::getUser($donorid);



		//get the user groupwise commision form params
		$params_usergroup=$params->get('usergroup');

		//get campaign type donation/Investment & its creator
		$camp_details=$campaignHelper->getCampaignType($post->get('cid','','INT'));
		//get Campaign creator groups ids
		$campaign_creator =JFactory::getUser($camp_details->creator_id);
		$camp_creator_groups_ids=$campaign_creator->groups;

		//if checkout type=Registered
		if($checkout_type==0)
		$userid=$user->id;
		else
		$userid=0;


		$this->guest_donation=$params->get('guest_donation');

		if($this->guest_donation)
		{
			if(!$userid)
			{
				/*$userid=0;
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
				}*/
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


		$obj->id='';

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

		/*if(($post->get('city','','STRING')) && ($post->get('city','','STRING')!=''))
		{
			$obj->city=$jgiveFrontendHelper->getCityNameFromId($post->get('city','','STRING'),$post->get('country','','STRING'));
		}*/

		//use helper to save country and state
		$obj->country=$jgiveFrontendHelper->getCountryNameFromId($post->get('country','','STRING'));
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

		if(!$db->insertObject( '#__jg_donations',$obj,'id'))
		{
			echo $db->stderr();
			return false;
		}

		$donation_id=$db->insertid();

		//************************EOF save donations details***********************************


		//*************************START save order details************************************

		//save order details
		$obj = new stdClass();
		$obj->id='';

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
		$obj->original_amount=$post->get('donation_amount','','FLOAT');
		$obj->amount=$post->get('donation_amount','','FLOAT');
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
		$obj->status='C';//by default pending status
		$obj->processor=$post->get('gateways','','STRING');
		//Get the IP Address
		if(!empty($_SERVER['REMOTE_ADDR'])){
			$ip=$_SERVER['REMOTE_ADDR'];
		}else{
			$ip='unknown';
		}
		$obj->ip_address=$ip;

		if(!$db->insertObject( '#__jg_orders',$obj,'id'))
		{
			echo $db->stderr();
			//return false;
		}

		//get last insert id

			$orders_key=$db->insertid();


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

		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."donations.php");
		$donationsHelper=new donationsHelper();
		$donationsHelper->sendOrderEmail($orders_key,$post->get('cid','','INT'));
		return true;
	}
	function getPaymentVars($pg_plugin, $tid)
	{
		$campaignHelper=new campaignHelper();
		$vars=new stdclass();
		$params=JComponentHelper::getParams('com_jgive');
		$currency_code=$params->get('currency');
		$send_payments_to_owner=$params->get('send_payments_to_owner');

		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."campaign.php");

		$pass_data=$this->getdetails($tid);
		//print_r($pass_data);die;
		$vars->order_id=$pass_data->order_id;
		//get the campaign promoter paypal email id
		$CampaignPromoterPaypalId=$campaignHelper->getCampaignPromoterPaypalId($tid);

		$session=JFactory::getSession();
		$session->set('order_id',$tid);

		$vars->client='com_jgive';


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



		/*
		if ($jgive_config['siteadmin_comm_per']==0)
		{
			$vars->business=$this->getEventownerEmail($tid);
		}
		*/
		//print_r($vars);die;
		return $vars;
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
		//echo $tid;die;
		$user_id=JFactory::getUser()->id;
		$query="SELECT o.id, o.order_id, d.first_name, d.email, d.phone,ds.is_recurring,ds.recurring_frequency,ds.recurring_count
		FROM #__jg_orders AS o
		LEFT JOIN #__jg_donors AS d ON d.id=o.donor_id
		LEFT JOIN #__jg_donations as ds ON ds.id=o.donation_id
		WHERE o.id=".$tid." AND d.user_id=".$user_id;

		$this->_db->setQuery($query);
		$orderdetails=$this->_db->loadObjectlist();
		return $orderdetails['0'];
	}

	//load campaign data for campaign id stored in session
	function getCampaign()
	{
		//die("ddddddd");
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
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$session=JFactory::getSession();
		$guest_email='';
		$guest_email=$session->get('guest_email');
		$session->clear('guest_email');
		$session->set('order_link_guestemail',$guest_email);
		//load donations helper
		require_once(JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php');
		$donationsHelper=new donationsHelper();

		JRequest::setVar('remote',1);
		$donationItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations');

		$return_resp=array();

		//Authorise Post Data
		if($post['plugin_payment_method']=='onsite'){
			$plugin_payment_method=$post['plugin_payment_method'];
		}

		//trigger payment plugins- onTP_Processpayment
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('payment',$pg_plugin);
		$data=$dispatcher->trigger('onTP_Processpayment',array($post));
		$data=$data[0];

		//store log
		$res=@$this->storelog($pg_plugin,$data);

		//get order id
		if(empty($order_id)){
			$order_id=$data['order_id'];
		}

		//get id for orders table using order_id
		$order_id_key=$donationsHelper->getOrderIdKeyFromOrderId($order_id);
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
		$order_amount=$this->_db->loadResult();//die;
		//return url
		$return_resp['return']=$data['return'];

		//$this->updateOrder($data); //To remove

		if($data['status']=='C' && $order_amount == $data['total_paid_amt'])//if payment status is confirmed
		{
			//if($processed==0)
			//{
			//update order details
			$this->updateOrder($data);
			//update order status, send email,
			//$donationsHelper->updatestatus($data['order_id'],$data['status'],$comment='',$notify_chk=1);
			$donationsHelper->updatestatus($order_id_key,$data['status'],$comment='',$notify_chk=1);
			//Trigger plugins
			//OnAfterJGivePaymentSuccess
			$dispatcher=JDispatcher::getInstance();
			JPluginHelper::importPlugin('system');
			$result=$dispatcher->trigger('OnAfterJGivePaymentSuccess',array($order_id_key));//Call the plugin and get the result
			if($result===false){
				return false;
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
		$donation_details=$db->loadObject();//die;

		//print_r($donation_details);die;
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
					}//die("dddd");

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
					}//die("ssssssssss");
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
						//echo $flag;
						//die;
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
				$this->addPayout($data['raw_data']['paymentInfoList']['paymentInfo'][1]);
			}
		}
	}
	//add payout entry after adaptive payment from paypal
	function addPayout($data)
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
		$res->amount=$data['receiver']['amount'];

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
		//print_r($data);
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
		$donationsHelper->updatestatus($data['id'],$data['status'],$comment,$notify_chk);
		if($status=='RF')
		{
			$returnvaule=3;
		}
		return $returnvaule;
	}//function store ends


	//B
	//called from main controller in backend
	function deleteDonations($odid)
	{
		//print_r($odid);die;

		//check which orders is recurring

		for($i=0;$i<(count($odid));$i++)
		{

			$q="SELECT donation_id FROM  #__jg_orders WHERE id=".$odid[$i];
			$this->_db->setQuery($q);
			$donation_id=$this->_db->loadResult();//die;

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

	//added by sagar

	function getAllCampaigns(){
		$query="SELECT * FROM
			 #__jg_campaigns WHERE published=1" ;
		$this->_db->setQuery($query);
		return $this->_db->loadObjectlist();

	}

	function getAllusers(){
		$query="SELECT * FROM #__users" ;
		$this->_db->setQuery($query);
		return $this->_db->loadObjectlist();

	}

	//added by sagar

}
