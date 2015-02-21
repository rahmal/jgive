<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
class jgiveControllerDonations extends jgiveController
{
	function __construct(){
		parent::__construct();
	}

	//used in backend view
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
		$link='index.php?option=com_jgive&view=donations&layout=my';
		//added by sagar for custom project This is trigger  when status changed
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result=$dispatcher->trigger('OnAfterJGivePaymentUpdate',array($post['id']));//Call the plugin and get the result
		//added by sagar for custom project This is trigger  when status changed
		$this->setRedirect($link,$msg);
	}
	function confirmpayment(){
		$model= $this->getModel('donations');
		$session =JFactory::getSession();
		$jinput=JFactory::getApplication()->input;
		$order_id = $session->get('JGIVE_order_id');
		$session->clear('JGIVE_order_id');//@Amol clear JGIVE_order_id from seesion to place new order after click on donate
		$pg_plugin = $jinput->get('processor');
		$response=$model->confirmpayment($pg_plugin,$order_id);
	}
	//cancel payment
	function cancel()
	{
		$msg=JText::_('COM_JGIVE_PAYMENT_CANCEL_MSG');
		$link='index.php?option=com_jgive&view=donations&layout=my';
		$this->setRedirect($link,$msg);
	}

	//export order payment stats into a csv file
	function payment_csvexport()
	{
		/*load language file for plugin frontend*/

		$db =& JFactory::getDBO();
		$query = "SELECT i.id, i.first_name, i.last_name, i.email, i.user_id, i.cdate, i.transaction_id, i.processor,i.order_tax,i.order_tax_details,i.order_shipping,i.order_shipping_details, i.amount,i.status,i.ip_address
		FROM  #__jg_orders AS i
		ORDER BY i.id";

		$db->setQuery($query);
		$results = $db->loadObjectList();

		$csvData = null;
        $csvData.= "Order_Id,Order_Date,User_Name,User_IP,      Order_Tax,Order_Tax_details,Order_Shipping,Order_Shipping_details,Order_Amount,Order_Status,Payment_Gateway,Cart_Items,billing_email,billing_first_name,billing_last_name,billing_phone,billing_address,billing_city,billing_state,billing_country_name,billing_postal_code,shipping_email,shipping_first_name,shipping_last_name,shipping_phone,shipping_address,shipping_city,shipping_state,shipping_country_name,shipping_postal_code";

        $csvData .= "\n";
        $filename = "Donations_".date("Y-m-d_H-i",time());
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: csv" . date("Y-m") .".csv");
        header("Content-disposition: filename=".$filename.".csv");
        foreach($results as $result ){
        	if( ($result->id ) ){
	       		$csvData .= '"'.$result->id.'"'.','.'"'.$result->cdate.'"'.','.'"'.JFactory::getUser($result->user_info_id)->username.'"'.','.'"'.$result->ip_address.'"'.','.'"'.$result->order_tax.'"'.','.'"'.str_replace ( ",", ";",$result->order_tax_details).'"'.','.'"'.$result->order_shipping.'"'.','.'"'.str_replace ( ",", ";",$result->order_shipping_details).'"'.','.'"'.$result->amount.'"'.',';

        		switch($result->status)
				 {
				case 'C' :
					$orderstatus =  JText::_('COM_JGIVE_CONFIRMED');
				break;
				case 'RF' :
					$orderstatus = JText::_('COM_JGIVE_REFUND') ;
				break;
				/*case 'S' :
					$orderstatus = JText::_('QTC_SHIP') ;
				break;
				*/
				case 'P' :
					$orderstatus = JText::_('COM_JGIVE_PENDING') ;
				break;
				 }

			$query = "SELECT count(order_item_id) FROM #__jg_order_item WHERE order_id =".$result->id;
			$db->setQuery($query);
 			$cart_items	= $db->loadResult();
			$csvData .= '"'.$orderstatus.'"'.','.'"'.$result->processor.'"'.','.'"'.$cart_items.'"'.',';

			$query = "SELECT ou.* FROM #__jg_users as ou WHERE ou.address_type='BT' AND ou.user_id =".$result->user_info_id;
			$db->setQuery($query);
 			$billin	= $db->loadObject();
			$csvData .= '"'.$result->user_email.'"'.','.'"'.$result->firstname.'"'.','.'"'.$result->lastname	.'"'.','.'"'.$result->phone.'"'.','.'"'.$result->address.'"'.','.'"'.$result->city.'"'.','.'"'.$result->state_code.'"'.','.'"'.$result->country_code.'"'.','.'"'.$result->zipcode.'"'.',';

			$query = "SELECT ou.* FROM #__jg_users as ou WHERE ou.address_type='ST' AND ou.user_id =".$result->user_info_id;
			$db->setQuery($query);
 			$shipin	= $db->loadObjectList();
			$csvData .= '"'.$result->user_email.'"'.','.'"'.$result->firstname.'"'.','.'"'.$result->lastname	.'"'.','.'"'.$result->phone.'"'.','.'"'.$result->address.'"'.','.'"'.$result->city.'"'.','.'"'.$result->state_code.'"'.','.'"'.$result->country_code.'"'.','.'"'.$result->zipcode.'"'.',';

				$csvData .= "\n";
        	}
        }
		ob_clean();
		print $csvData;

	exit();
	}


	/////////////////////////////////////
	//frontend functions
	/////////////////////////////////////
	//frontend
	//called when clicked on donate button on campaign details page
	function donate()
	{
		$post=JRequest::get('post');
		$jgiveFrontendHelper=new jgiveFrontendHelper();

		//clear session order id for placing new order
		$session=JFactory::getSession();
		$session->clear('JGIVE_order_id');
		$session->clear('JGIVE_giveback_id');

		if(!empty($post['cid']))
		{
			$cid=$post['cid'];
		}

		//check that this donation is giveback donation
		$input = JFactory::getApplication()->input;
		//get giveback id
		$giveback_id=$input->get('giveback_id','','INT');

		//If it is giveback donation then get campaign id from url
		if($giveback_id)
		{
			$cid=$input->get('cid','','INT');
		}

		if(!empty($cid))
		{
			$model=$this->getModel('donations');
			$model->setSessionCampaignId($cid,$giveback_id);
		}

		$redirect=JRoute::_('index.php?option=com_jgive&view=donations&layout=paymentform',false);
		//$msg='';
		$this->setRedirect($redirect);
	}

	//frontend
	//called when clicked on donate button on payment form
	function confirm()
	{
		//check token
		JSession::checkToken() or jexit('Invalid Token');
		$msg='';
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$post=JRequest::get('post');
		if(!empty($post['cid']))
		{
			//save donor details in session
			//so that those can be used in future donations in current session
			$model =$this->getModel('donations');
			$model->setSessionDonorData($post);
			//add entry in orders table, send email etc
			$result=$model->addOrder($post);
		}

		$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
		if((int)$result!=-1){

			$redirect=JRoute::_('index.php?option=com_jgive&view=donations&layout=confirm&Itemid='.$itemid,false);
		}
		else
		{
			if((int)$result==-1)
			{
				$msg = JText::_( 'COM_JGIVE_ERR_CONFIG_SAV_LOGIN' ); // if already exist eamil
			}
			else
			{
				$msg = JText::_( 'COM_JGIVE_ERR_CONFIG_SAV' );
			}

			$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');

			$redirect=JRoute::_( 'index.php?option=com_jgive&view=donations&layout=paymentform&Itemid='.$itemid, false );
		}
		if(!empty($msg))
		$this->setRedirect($redirect,$msg);
		else
		$this->setRedirect($redirect);
	}

	//frontend
	//called when selecting pament gateway on confirm payment view
	function getHTML($pg_plugin)
	{
		sleep(1);//sleep to show animated ajax image
		$model=$this->getModel('donations');
		$session=JFactory::getSession();
		//get order id from session
		$order_id=$session->get('JGIVE_order_id');
		//all module function
		$html=$model->getHTML($pg_plugin,$order_id);
		if(!empty($html[0]))
		{
			return $html[0];
		}
	}

	//frontend
	//notify url function
	function processPayment()
	{
		$mainframe=JFactory::getApplication();
		$jinput=JFactory::getApplication()->input;
		$session =JFactory::getSession();
		if($session->has('payment_submitpost')){
			$post = $session->get('payment_submitpost');
			$session->clear('payment_submitpost');
		}
		else
		{
			$post = JRequest::get('post');
		}
	   /*$post = json_decode('{"mc_gross":"15.00","protection_eligibility":"Eligible","address_status":"confirmed","payer_id":"KYCMB66E86NJ6","tax":"0.00","address_street":"1 Main St","payment_date":"04:16:38 Mar 27, 2014 PDT","payment_status":"Completed","charset":"windows-1252","address_zip":"95131","first_name":"amol","mc_fee":"0.74","address_country_code":"US","address_name":"amol Gh","notify_version":"3.7","custom":"JGOID-0003","payer_status":"verified","business":"sagar_c-facilitator@tekdi.net","address_country":"United States","address_city":"San Jose","quantity":"0","verify_sign":"AVkS-tHU2h7x-Z4rkFdc48Ls6tx5ANPzfbfIz4YQdiPMX9wrdypUO0sn","payer_email":"amol_g@tekdi.net","txn_id":"59H512502M183111N","payment_type":"instant","last_name":"Gh","address_state":"CA","receiver_email":"sagar_c-facilitator@tekdi.net","payment_fee":"0.74","receiver_id":"MKR5A5SU2W9VL","txn_type":"web_accept","item_name":"Techjoomla Test campaign","mc_currency":"USD","item_number":"","residence_country":"US","test_ipn":"1","transaction_subject":"JGOID-0003","payment_gross":"15.00","ipn_track_id":"2cfb5c3ec55f","main_response":1}',true);*/

		$pg_plugin = $jinput->get('processor');
		$model= $this->getModel('donations');

		$order_id = $jinput->get('order_id','','STRING');
		if($order_id=='')
		{
			if(isset($post['order_id']))
			{
				$order_id = $post['order_id'];
			}
		}

		if(empty($post) || empty($pg_plugin) )
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JGIVE_SOME_ERROR_OCCURRED'), 'error');
			return;
		}
		$this->storelog($pg_plugin,$post);

		$response=$model->processPayment($post,$pg_plugin,$order_id);
		if(!empty($response['msg']))
			$mainframe->redirect($response['return'],$response['msg']);
		else
			$mainframe->redirect($response['return']);
	}

	function storelog($name,$data)
	{
		$data['main_response'] = 1;
		$data1=array();
		$data1['raw_data']=$data;
		$data1['JT_CLIENT']="com_jgive";
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('payment',$name);
		$data=$dispatcher->trigger('onTP_Storelog',array($data1));
		return true;
	}

	/*function addPayout() //@Amol Do not delete it (Added for sample adaptive adding)
	{
		$model= $this->getModel('donations');
		$model->addPayout(1);
	}*/
	// check mail already exists in db for new user registration
	function chkmail(){

		$jinput=JFactory::getApplication()->input;
		$email =  $jinput->get('email','','STRING');
		$model = $this->getModel('donations');
		$status = $model->checkMailExists($email);
		$e[] = $status;
		if($status==1){
			$e[] = JText::_('COM_JGIVE_MAIL_EXISTS');
		}
		echo json_encode($e);
		jexit();
	}
	function login_validate() {

		$input=JFactory::getApplication()->input;
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$redirect_url = JRoute::_('index.php?option=com_jgive&view=donations&layout=paymentform');
		$json = array();
		if($user->id){
			$json['redirect'] = $redirect_url;
		}
		if(!$json)
		{
			require_once (JPATH_SITE.'/components/com_jgive/helpers/user.php');
			$userHelper = new UserHelper;
			//now login the user
			if ( !$userHelper->login(array('username' => $app->input->getString('email'), 'password' => $app->input->getString('password'))
			))
			{
				$json['error']['warning'] = JText::_('COM_JGIVE_CHECKOUT_ERROR_LOGIN');
			}
		}
		$json['redirect'] = $redirect_url;
		echo json_encode($json);
		$app->close();
	}
	function placeOrder()
	{
		$redirect_url = JRoute::_('index.php?option=com_jgive&view=donations');
		$input=JFactory::getApplication()->input;
		$post = $input->post;

		$model =$this->getModel('donations');

		$model->setSessionDonorData($post);
		$res=$model->addOrder($post);

		$session = JFactory::getSession();
		if($session->get('JGIVE_order_id'))
		{
			$payment_plg= $session->get('payment_plg');
			$itemid=$input->get('Itemid',0);
			$orderid= $session->get('JGIVE_order_id');
			$data['success_msg'] = JText::_('COM_JGIVE_ORDER_CREATED_SUCCESS');
			$data['success'] = 1;
			$data['order_id'] =$orderid ;
			$data['orderHTML'] =$this->getorderHTML($orderid);
		}
		else
		{
				$data['success_msg'] = JText::_('COM_JGIVE_ORDER_CREATED_FAILED');
				$data['success'] = 0;
				$data['redirect_uri'] =$redirect_url;
				echo json_encode($data);
				jexit();
		}
		//echo $post->get('gateways','','STRING');die;
		$data['gatewayhtml']=$this->getHTML($post->get('gateways','','STRING'));
		echo json_encode($data);
		jexit();
	}
	/*function getorderHTML($order_id)
	{
		$donationsHelper=new donationsHelper();
		//$order=$donationsHelper->getOrderInfo($order_id);
		$this->donation_details=$donationsHelper->getSingleDonationInfo($order_id);
		//print_r($this->donation_details);die;
		$params=JComponentHelper::getParams('com_jgive');
		$this->currency_code=$params->get('currency');
		$this->pstatus=$donationsHelper->getPStatusArray();
		$this->donations_site=1;
		ob_start();
			include(JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'views'.DS.'donations'.DS.'tmpl'.DS.'details.php');
			$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}*/

	function getorderHTML($order_id)
	{
		$donationsHelper=new donationsHelper();
		//$order=$donationsHelper->getOrderInfo($order_id);
		$this->donation_details=$donationsHelper->getSingleDonationInfo($order_id);
		//print_r($this->donation_details);die;
		$params=JComponentHelper::getParams('com_jgive');
		$this->currency_code=$params->get('currency');
		$this->pstatus=$donationsHelper->getPStatusArray();
		$this->donations_site=1;

		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$billpath = $jgiveFrontendHelper->getViewpath('donations','details');
		ob_start();
			   include($billpath);
			   $html = ob_get_contents();
		ob_end_clean();

		return $html;
	}


}
