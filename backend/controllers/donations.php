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
defined('_JEXEC') or die();
class jgiveControllerDonations extends jgiveController
{
	function __construct(){
		parent::__construct();
	}

	//B
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
		//added by sagar for custom project This is trigger  when status changed
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result=$dispatcher->trigger('OnAfterJGivePaymentUpdate',array($post['id']));//Call the plugin and get the result
		//added by sagar for custom project This is trigger  when status changed
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

	function cancel() {

		$msg = JText::_( 'COM_JGIVE_CANCEL_MSG' );
		$this->setRedirect( 'index.php?option=com_jgive', $msg );
	}

	//added by sagar
	function loadprofiledata()
	{
		$compaignuserid=JFactory::getApplication()->input->get('compaignuserid');
		$path=JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'integrations.php';
		if(!class_exists('integrationsHelper_backend'))
		{
			JLoader::register('integrationsHelper_backend', $path );
			JLoader::load('integrationsHelper_backend');
		}
		$params=JComponentHelper::getParams('com_jgive');

		$profile_import=$params->get('profile_import');
		//if profie import is on the call profile import function
		if($profile_import)
		{
			$integrationsHelper_backend=new integrationsHelper_backend();
			$profiledata=$integrationsHelper_backend->profileImport(1,$compaignuserid);
			if(!empty($profiledata))
			{
				unset($profiledata['campaign']);

				if(!empty($profiledata))
					{

					}

				echo json_encode($profiledata);
				jexit();

			}
		}

	}


	function placeOrder()
	{
		$path=JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php';
		if(!class_exists('donations_backendHelper'))
		{
			JLoader::register('donations_backendHelper', $path );
			JLoader::load('donations_backendHelper');
		}
		$redirect_url = JRoute::_('index.php?option=com_jgive&view=donations');
		$input=JFactory::getApplication()->input;
		$post = $input->post;
		$donations_backendHelper=new donations_backendHelper();

		$res=$donations_backendHelper->addOrder($post);

		$session = JFactory::getSession();
		if($session->get('JGIVE_order_id'))
		{
			$payment_plg= $session->get('payment_plg');
			$itemid=$input->get('Itemid',0);
			$orderid= $session->get('JGIVE_order_id');
			$data['success_msg'] = JText::_('COM_JGIVE_ORDER_CREATED_SUCCESS');
			$data['success'] = 1;
			$data['order_id'] =$orderid ;
		}
		else
		{
				$data['success_msg'] = JText::_('COM_JGIVE_ORDER_CREATED_FAILED');
				$data['success'] = 0;
				$data['redirect_uri'] =$redirect_url;

		}
		$link='index.php?option=com_jgive&view=donations&layout=all';
		$this->setRedirect($link,$msg);
	}
	function addNewDonation()
	{
		$link='index.php?option=com_jgive&view=donations&layout=paymentform';
		$this->setRedirect($link,$msg);
	}

	function cancelorder(){

		$link='index.php?option=com_jgive&view=donations&layout=all';
		$this->setRedirect($link,$msg);

	}
	//added by sagar

	// getGiveBack Against Campaign
	function getGiveBackAgainstCampaign()
	{
		$input =  JFactory::getApplication()->input;
		$post =  $input->post;
		$cid = $post->get('cid','','INT');

		$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'campaign.php';
		if(!class_exists('campaignHelper'))
		{
			JLoader::register('campaignHelper', $helperPath );
			JLoader::load('campaignHelper');
		}

		$campaignHelper = new campaignHelper();

		$result = $campaignHelper->getCampaignGivebacks($cid);
		echo json_encode($result);
		jexit();
	}
}
