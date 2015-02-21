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
jimport( 'joomla.application.component.model' );

class jgiveModelCampaign extends JModelLegacy
{
	function __construct(){
		parent::__construct();
	}

	//saves a new campaign details
	//used for create view
	function save()
	{
		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."media.php");
		$input=JFactory::getApplication()->input;
		$post=$input->post;

		$session=JFactory::getSession();
		//echo "<pre>";	print_r($post);die('save');
		$user=JFactory::getUser();
		$userid=$user->id;
		if(!$userid)
		{
			$userid=0;
			return false;
		}
		//save campaign details
		//prepare object for insert
		$obj=$this->createCampaignObject($post,$userid,NULL);
		//@TODO use lang constant for date
		$obj->created=date(JText::_('Y-m-d H:i:s'));
		//insert object
		if(!$this->_db->insertObject( '#__jg_campaigns',$obj,'id'))
		{
			echo $this->_db->stderr();
			return false;
		}
		//get last insert id
		$camp_id=$this->_db->insertid();
		$session->set('camapign_id',$camp_id);

		//save giveback details
		$params=JComponentHelper::getParams('com_jgive');
		$creatorfield=array();
		$show_selected_fields=$params->get('show_selected_fields');
		$show_field=0;
		$give_back_cnf=0;
		if($show_selected_fields)
		{
			$creatorfield=$params->get('creatorfield');

			if(isset($creatorfield))
			if(in_array('give_back',$creatorfield))
			{
				$give_back_cnf=1;
			}
		}
		else
		{
			$show_field=1;
		}

		if($show_field==1 OR $give_back_cnf==0 )  // save give back detail only when show give back is yes (When editing campaign)
		{
			$givebackSaveSuccess=$this->saveGivebacks($post,$camp_id);
			if(!$givebackSaveSuccess){
				return false;
			}
		}

		//upload campaign image
		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."campaign.php");
		$campaignHelper=new campaignHelper();

		$jgivemediaHelper=new jgivemediaHelper();
		$img_dimensions = array();
		$img_dimensions[] = 'small';
		$img_dimensions[] = 'medium';
		$img_dimensions[] = 'large';
		$image_path=array();

			//@params filed_name,image dimensions,resize=0 or not_resize=1(upload original)
		$img_org_name=$jgivemediaHelper->imageupload('camp_image',$img_dimensions,0);

		if(empty($img_org_name))
		{
			if($img_org_name)
			{
				$uploadSuccess=$campaignHelper->uploadImage($camp_id,'camp_image',0,'');
				if(!$uploadSuccess)
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		else
		{
			$db=JFactory::getDBO();
			$obj=new stdClass();
			$obj->id='';
			$obj->campaign_id=$camp_id;

			$image_upload_path_for_db='images/jGive';
			$image_upload_path_for_db.='/'.$img_org_name;

			$obj->path=$image_upload_path_for_db;
			$obj->gallery_image=0;
			$obj->order='';

			if(!$db->insertObject('#__jg_campaigns_images',$obj,'id'))
			{
				echo $db->stderr();
				return false;
			}

			$uploadSuccess=$db->insertid();
		}


		//get video embed url
		$video_url=$post->get('video_url','','STRING');
		$video_provider=$post->get('video_provider','','STRING');

		if(!empty($video_url))
		{
			$jgivemediaHelper=new jgivemediaHelper();
			$result=$jgivemediaHelper->geturl($video_provider,$video_url);
			if($result)
			{
				$video_url=$result;
			}
		}

		if($uploadSuccess) // Save video_url & other details
		{
			$obj=new stdClass();
			$obj->id=$uploadSuccess;
			$obj->video_provider=$post->get('video_provider','','STRING');
			$obj->video_url=$video_url;
			$obj->video_img=$post->get('video_img','','INT');
			if(!$this->_db->updateObject('#__jg_campaigns_images',$obj,'id'))
			{
				echo $db->stderr();
				return false;
			}
		}

		//@Amol Add new images in gallery
		$file_field='jgive_img_gallery';
		//print_r($_FILES);die;
		$file_errors=$_FILES[$file_field]['error'];
		//print_r($file_errors);die;
		foreach($file_errors as $key=>$file_error)
		{
			if(!$file_error==4)//if file field is not empty
			{
				$uploadSuccess=$campaignHelper->uploadImage($camp_id,'jgive_img_gallery',0,$key);
				if(!$uploadSuccess){
					//return false;
				}
			}
		}

		//send campaigns created email to creator & site admin
		$params=JComponentHelper::getParams('com_jgive');
		if($params->get('admin_approval'))
		{
			$campaignHelper->sendCmap_create_mail($post,$camp_id);// email to site admin
			$campaignHelper->sendCmapCreateMailToPromoter($post,$camp_id);// email to campaign creator
		}

		//Trigger plugins
		//OnAfterJGiveCampaignSave
		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		$result=$dispatcher->trigger('OnAfterJGiveCampaignSave',array($camp_id, $post));
		if(!$result){
			//return false;
		}

		//trigger after save campaign
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onAfterCampaignSave', array ($post));
		//END plugin triggers

		return true;
	}

	/*
	 * Lets you edit campaign
	 * */
	function edit()
	{
		// manoj - added for bill start
		$oldDetails='';
		$oldDetailsImage='';
		$newDetails='';
		$newDetailsImage='';
		// manoj - added for bill end
		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."media.php");
		$input=JFactory::getApplication()->input;
		$post=$input->post;

		$cid=$post->get('cid');

		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."campaign.php");
		$campaignHelper=new campaignHelper();

		// manoj - added for bill start
		$oldDetails      = $this->getCampaignDetails($cid);
		$oldDetailsImage = $campaignHelper->getCampaignImages($cid);
		// manoj - added for bill end

		$promoter_id=$post->get('promoter_id');
		//echo "<pre>";		print_r($post);		print_r($post);		die('edit');
		$user=JFactory::getUser($promoter_id);
		$userid=$user->id;
		if(!$userid)
		{
			$userid=0;
			return false;
		}

		$give_back_ids=$post->get('ids','','Array');
		$givebackDeleteSuccess=$this->deleteGivebacks($give_back_ids,$cid);

		//save campaign details
		$obj=$this->createCampaignObject($post,$userid,$cid);
		$obj->modified=date(JText::_('Y-m-d H:i:s'));
		if(!$this->_db->updateObject('#__jg_campaigns',$obj,'id'))
		{
			echo $this->_db->stderr();
			return false;
		}

		//save giveback details
		//for edit , delete all existing givebacks for this campaign and re-add new
		$params=JComponentHelper::getParams('com_jgive');
		$creatorfield=array();
		$show_selected_fields=$params->get('show_selected_fields');
		$show_field=0;
		$give_back_cnf=0;
		if($show_selected_fields)
		{
			$creatorfield=$params->get('creatorfield');
			if(isset($creatorfield))
			if(in_array('give_back',$creatorfield))
			{
				$give_back_cnf=1;
			}
		}
		else
		{
			$show_field=1;
		}

		//save giveback details
		$params=JComponentHelper::getParams('com_jgive');
		$creatorfield=array();
		$show_selected_fields=$params->get('show_selected_fields');
		$show_field=0;
		$give_back_cnf=0;
		if($show_selected_fields)
		{
			$creatorfield=$params->get('creatorfield');
			if(isset($creatorfield))
			if(in_array('give_back',$creatorfield))
			{
				$give_back_cnf=1;
			}
		}
		else
		{
			$show_field=1;
		}

		if($show_field==1 OR $give_back_cnf==0 )  // save give back detail only when show give back is yes (When editing campaign)
		{
			$givebackSaveSuccess=$this->saveGivebacks($post,$cid);
			if(!$givebackSaveSuccess){
				return false;
			}
		}

		//upload image
		//for edit , delete all existing images for this campaign and re-add new
		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."campaign.php");
		$campaignHelper=new campaignHelper();

		$existing_imgs_ids=$post->get('existing_imgs_ids','','Array');
		$campaignHelper->deleteGalleryImages($cid,$existing_imgs_ids);

		$file_field='camp_image';
		//print_r($_FILES);die;
		$file_error=$_FILES[$file_field]['error'];


		//get video embed url
		$video_url=$post->get('video_url','','STRING');
		$video_provider=$post->get('video_provider','','STRING');

		if(!empty($video_url))
		{
			$jgivemediaHelper=new jgivemediaHelper();
			$result=$jgivemediaHelper->geturl($video_provider,$video_url);
			if($result)
			{
				$video_url=$result;
			}
		}

		$jgivemediaHelper=new jgivemediaHelper();
		$img_dimensions = array();
		$img_dimensions[] = 'small';
		$img_dimensions[] = 'medium';
		$img_dimensions[] = 'large';
		$image_path=array();

		if(!$file_error==4)//if file field is not empty
		{

			$main_img_id=$post->get('main_img_id');
			//@params filed_name,image dimensions,resize=0 or not_resize=1(upload original)
			$img_org_name=$jgivemediaHelper->imageupload('camp_image',$img_dimensions,0);

			//before deleting image get image path to delete image from folder
			$campaignHelper=new campaignHelper();
			$campaignHelper->deleteCampaignMainImage($cid);
			//var_dump($img_org_name);die;

			//handling error Not Valid Extension

			if(empty($img_org_name))
			{
				if($img_org_name)
					$uploadSuccess=$campaignHelper->uploadImage($cid,'camp_image',$main_img_id,0);
				else
					return false;
			}
			else
			{
				$db=JFactory::getDBO();

				$obj=new stdClass();
				$obj->id=$main_img_id;
				$obj->campaign_id=$cid;

				$image_upload_path_for_db='images/jGive';
				$image_upload_path_for_db.='/'.$img_org_name;

				$obj->path=$image_upload_path_for_db;
				$obj->gallery_image=0;
				$obj->order='';

				if($main_img_id)
				{
					if(!$db->updateObject('#__jg_campaigns_images',$obj,'id'))
					{
						echo $db->stderr();
						return false;
					}
				}
				else
				{
					if(!$db->insertObject('#__jg_campaigns_images',$obj,'id'))
					{
						echo $db->stderr();
						return false;
					}
					$main_img_id=$db->insertid();
				}
			}

			if($main_img_id) // Save video_url & other details
			{
				$obj=new stdClass();
				$obj->id=$main_img_id;
				$obj->video_provider=$post->get('video_provider','','STRING');
				$obj->video_url=$video_url;
				$obj->video_img=$post->get('video_img','','INT');
				if(!$this->_db->updateObject('#__jg_campaigns_images',$obj,'id'))
				{
					echo $db->stderr();
					return false;
				}
			}
		}
		else //update only video_url & other details in __jg_campaigns_images table
		{
			$img_id=$post->get('img_id');
			$obj=new stdClass();
			$obj->id=$img_id;
			$obj->video_provider=$post->get('video_provider','','STRING');
			$obj->video_url=$video_url;
			$obj->video_img=$post->get('video_img','','INT');
			if(!$this->_db->updateObject('#__jg_campaigns_images',$obj,'id'))
			{
				echo $db->stderr();
				return false;
			}
		}


		//@Amol Add new images in gallery
		$file_field='jgive_img_gallery';
		//print_r($_FILES);die;
		$file_errors=$_FILES[$file_field]['error'];
		//print_r($file_errors);die;
		foreach($file_errors as $key=>$file_error)
		{
			if(!$file_error==4)//if file field is not empty
			{
				$uploadSuccess=$campaignHelper->uploadImage($cid,'jgive_img_gallery',0,$key);
				if(!$uploadSuccess){
					//return false;
				}
			}
		}

		//delete
		//Trigger plugins
		//OnAfterJGiveCampaignSave

		$dispatcher=JDispatcher::getInstance();
		JPluginHelper::importPlugin('system');
		// manoj - changed for bill
		$newDetails      = $this->getCampaignDetails($cid);
		$newDetailsImage = $campaignHelper->getCampaignImages($cid);
		//$result=$dispatcher->trigger('OnAfterJGiveCampaignEdit',array($cid));
		$result=$dispatcher->trigger('OnAfterJGiveCampaignEdit',array($cid, $post, $newDetails, $newDetailsImage[0], $oldDetails, $oldDetailsImage[0]));
		// manoj - changed for bill
		if(!$result){
			//return false;
		}

		//Trigger after edit campaign
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onAfterCampaignEdit', array ($post));

		//END plugin triggers

		//Added by Sneha
		//option for sending mail on edit
		$params=JComponentHelper::getParams('com_jgive');
		$on_edit = $params->get('mail_on_edit');
		if($on_edit == 1)
		{
			$campaignHelper->send_mail_on_edit($post,$cid); // email to site admin
		}
		//End added by Sneha

		return true;
	}

	//used when creating/editing a campaign
	function createCampaignObject($post,$userid,$cid)
	{
		$obj = new stdClass();
		$obj->id=$cid;//editing campaign
		$obj->creator_id=$userid;
		$obj->category_id='';//@TODO use in next versions
		$obj->title=$post->get('title','','STRING');
		if($post->get('type')){
			$obj->type=$post->get('type','','STRING');
		}
		else
		{
			// get the campaign set by admin to be created & save in db
			/*$params=JComponentHelper::getParams('com_jgive');
			$type_array=$params->get('camp_type');
			//echo $type_array;die;
			$obj->type=$type_array['0'];
			if($obj->type=='d')
				$obj->type='donation';
			else
			*/
			$obj->type='donation';
		}
		$obj->category_id=$post->get('campaign_category','','INT');
		//org_ind_type since version 1.5.1
		$obj->org_ind_type=$post->get('org_ind_type','','STRING');

		if($post->get('max_donors'))
			$obj->max_donors=(int)$post->get('max_donors','','INT');

		if($post->get('minimum_amount'))
			$obj->minimum_amount=(int)$post->get('minimum_amount','','FLOAT');

		$obj->short_description=$post->get('short_desc','','STRING');
		//$obj->long_description=$post->get('long_desc');

		//$obj->long_description=	JRequest::getvar( 'long_desc', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$obj->long_description=	JRequest::getVar( 'long_desc', '', 'post', 'string', JREQUEST_ALLOWRAW );

		$obj->goal_amount=$post->get('goal_amount','','FLOAT');
		$obj->paypal_email=$post->get('paypal_email','','STRING');

		$obj->first_name=$post->get('first_name','','STRING');
		$obj->last_name=$post->get('last_name','','STRING');

		if($post->get('address'))
			$obj->address=$post->get('address','','STRING');

		if($post->get('address2'))
			$obj->address2=$post->get('address2','','STRING');

		$jgiveFrontendHelper=new jgiveFrontendHelper();

		//for city since version 1.6
		$other_city_check=$post->get('other_city_check','','STRING');
		//print_r($post);die;
		if(!empty($other_city_check))
		{
			$obj->city=$post->get('other_city','','STRING');
			$obj->other_city=1;
		}
		else if(($post->get('city')) && $post->get('city')!='')
		{
			$obj->city=$jgiveFrontendHelper->getCityNameFromId($post->get('city'),$post->get('country'));
			$obj->other_city=0;
		}

		$obj->country=$jgiveFrontendHelper->getCountryNameFromId($post->get('country'));

		if(($post->get('state')) && $post->get('state')!='')
		{
			$obj->state=$jgiveFrontendHelper->getRegionNameFromId($post->get('state','','STRING'),$post->get('country','','STRING'));
		}

		if($post->get('zip'))
			$obj->zip=$post->get('zip','','STRING');

		if($post->get('phone'))
			$obj->phone=$post->get('phone','','STRING');

		if($post->get('group_name'))
			$obj->group_name=$post->get('group_name','','STRING');//from version 1.6

		if($post->get('website_address'))
			$obj->website_address=$post->get('website_address','','STRING');//from version 1.6

		$obj->start_date=JHtml::_('date',$post->get('start_date','','STRING'),'Y-m-d');
		$obj->end_date=JHtml::_('date',$post->get('end_date','','STRING'),'Y-m-d');

		if(($post->get('publish')))
		{
			$params=JComponentHelper::getParams('com_jgive');
			if($params->get('admin_approval'))
			{
				$obj->published = 0 ;
			}
			else
			{
				$obj->published=$post->get('publish','','STRING');
			}
		}

		if(($post->get('allow_exceed'))){
			$obj->allow_exceed=$post->get('allow_exceed','','INT');
		}
		if(($post->get('show_public'))){
			$obj->allow_view_donations=$post->get('show_public','','INT');
		}

		if($post->get('internal_use'))
			$obj->internal_use=$post->get('internal_use','','STRING');

		//js_groupid

		if(($post->get('js_group'))){
			$obj->js_groupid=$post->get('js_group','','INT');
		}

		return $obj;
	}
	// Function added by Sneha
	// save campaign givebacks
	function saveGivebacks($post,$cid)
	{
		$give_back_value=$post->get('give_back_value','','Array');
		$give_back_details=$post->get('give_back_details','','Array');
		$giveback_count=count($post->get('give_back_value','','Array'));
		$give_back_ids=$post->get('ids','','Array');
		$give_back_order=$post->get('give_back_order','','Array');
		$give_back_quantity=$post->get('give_back_quantity','','Array');

		$i=0;
		foreach ($give_back_ids as $key => $value) {
			$db = JFactory::getDBO();
			$coupon = new stdClass;

			if ($value)
				$coupon->id = $value;
			else
				$coupon->id = '';

			$coupon->campaign_id = $cid;
			if(!empty($coupon->campaign_id)){
				$coupon->amount = $give_back_value[$key];
				$coupon->description = $give_back_details[$key];
				$coupon->order = $i++;
				$coupon->total_quantity = $give_back_quantity[$key];
				if ($value)
				{
					if (!$db->updateObject('#__jg_campaigns_givebacks', $coupon, 'id'))
					{
						return false;
					}
				}
				else if(!empty($coupon->amount))
				{

					$coupon->quantity = 0;
					if (!$db->insertObject('#__jg_campaigns_givebacks', $coupon, 'id'))
					{
						echo $db->stderr();
						return false;
					}
				}
				if($value)
					$giveback_id = $value;
				else
					$giveback_id = $db->insertid();

				require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."campaign.php");
				$campaignHelper=new campaignHelper();
				$couponimgSuccess=$campaignHelper->uploadImageForGiveback($key,$giveback_id,$cid);
			}
        }
		return true;
	}
	// Function added by Sneha
	// Delete entries from giveback table
	function deleteGivebacks($give_back_ids,$cid)
	{
		$db = JFactory::getDBO();
		$give_back_idsarr=(array)$give_back_ids;
		$query="SELECT id FROM #__jg_campaigns_givebacks WHERE campaign_id=".$cid;
		$db->setQuery($query);
		$type_ids=$db->loadColumn();
		$diff=array_diff($type_ids,$give_back_idsarr);
		$diffids=implode("','",$diff);
		$query = "DELETE FROM #__jg_campaigns_givebacks WHERE id IN ('".$diffids."') AND campaign_id=".$cid;
		$db->setQuery( $query );
		if (!$db->execute()) {
		}
	}
	//get campaign information
	//used on single view
	//also used when editing campaign
	function getCampaign()
	{
		$cid=JRequest::getInt('cid');
		if(empty($cid)) //for single campaign menu
			$cid=JRequest::getInt('id');

		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."campaign.php");
		$campaignHelper=new campaignHelper();

		$query="SELECT c.*
		FROM `#__jg_campaigns` AS c
		WHERE c.id=".$cid;
		$this->_db->setQuery($query);
		$campaign=$this->_db->loadObject();
		$cdata['campaign']=$campaign;
		//print_r($campaign);

		//needed for loading states
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$cdata['campaign']->country_id=$jgiveFrontendHelper->getCountryIdFromName($cdata['campaign']->country);

		//get campaign images
		$cdata['images']=$campaignHelper->getCampaignImages($cid);
		//get campaign givebacks
		$cdata['givebacks']=$campaignHelper->getCampaignGivebacks($cid);

		//Get orders count
		$cdata['orders_count'] = $campaignHelper->getCampaignOrdersCount($cid);

		//get campaign donors
		$params = JComponentHelper::getParams('com_jgive');
		$limit = $params->get('donor_records', '', 'INT');

		if(!$limit)
		{
			$limit=10;
		}

		$cdata['donors']=$campaignHelper->getCampaignDonors($cid, 0, $limit);

		$layout=JFactory::getApplication()->input->get('layout','create');
		if($layout=='single')
		{
			//get campaign amounts
			$amounts=$campaignHelper->getCampaignAmounts($cid);
			$cdata['campaign']->amount_received=$amounts['amount_received'];
			$cdata['campaign']->remaining_amount=$amounts['remaining_amount'];

			//get campaign promoter info
			$integrationsHelper=new integrationsHelper();
			$cdata['campaign']->creator_avatar=$integrationsHelper->getUserAvatar($cdata['campaign']->creator_id);
			$cdata['campaign']->creator_profile_url=$integrationsHelper->getUserProfileUrl($cdata['campaign']->creator_id);
			//get campaign categories
			if(!empty($cdata['campaign']->category_id))
				$cdata['campaign']->catname=$campaignHelper->getCatname($campaign->category_id);

		}
		return $cdata;
	}
	// get Campaign Category
	function getCampaignsCats() {
		if(JFactory::getApplication()->input->get('cid'))
		{
			$details=$this->getCampaignDetails(JFactory::getApplication()->input->get('cid'));
			$camp_cat=$details->category_id;
			if(JVERSION>=3.0)
				$dropdown=JHtml::_('select.genericlist', JHtml::_('category.options', 'com_jgive'), 'campaign_category',null,'value','text',intval( $camp_cat ));
			else
				$dropdown=JHtml::_('list.category', 'campaign_category', "com_jgive", intval( $camp_cat ));
		}
		else
		{	if(JVERSION>=3.0)
				$dropdown=JHtml::_('select.genericlist', JHtml::_('category.options', 'com_jgive'), 'campaign_category');
			else
				$dropdown=JHtml::_('list.category', 'campaign_category', "com_jgive", intval( 1 ));
		}
		return  $dropdown;
	}

	function getCampaignDetails($c_id)
	{
		$db	= JFactory::getDBO();
		$query = "SELECT * FROM #__jg_campaigns WHERE id='".$c_id."'";
		$db->setQuery($query);
		return($db->loadObject());
	}
	function getJS_usergroup()
	{
		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."integrations.php");
		$integrationsHelper=new integrationsHelper();
		return $result=$integrationsHelper->getJS_usergroup();
	}

	function viewMoreDonorReports($cid, $jgive_index)
	{
		$campaignHelper = new campaignHelper();
		$params = JComponentHelper::getParams('com_jgive');

		$limit = $params->get('donor_records', '', 'INT');
		$limit_start = $jgive_index;

		if(!$limit)
		{
			$limit=10;
		}

		$donors = $campaignHelper->getCampaignDonors($cid, $limit_start-1, $limit);
		$this->currency_code = $params->get('currency');

		$html = "";

		foreach($donors as $donor)
		{
		$html.= "<tr> <td>".$jgive_index."</td>

				<td>";

					//if no avatar, use default avatar
					if(!$donor->avatar)
					{
						$donor->avatar=JUri::root().'components/com_jgive/assets/images/default_avatar.png';
					}

					$title=$donor->first_name." ".$donor->last_name;

					if(!empty($donor->profile_url) && $donor->user_id!=0)
					{
		$html.=		"<a href=".$donor->profile_url." target='_blank'>"
							.$title."
						</a>";
					}
					else
					{
		$html.=			$title;
					}
		$html.=	"<br/>
					<img class='com_jgive_img_48_48' src=".$donor->avatar." />
					<br/>";

					if($donor->annonymous_donation)
					{
		$html.=			JText::_("COM_JGIVE_ANNONYMOUS_DONATION_MSG_OWNER")."
						<br/>";
					}

		$html.=	"</td>";

		$html.=	"<td>

						".JText::_('COM_JGIVE_DONATED_AMT').":".$donor->amount." ".$this->currency_code."<br>".JText::_('COM_JGIVE_GIVEBACK_SELECTED').":";

						if($donor->giveback_id)
						{
		$html.=				JText::_('COM_JGIVE_YES')."<br>".JText::_('COM_JGIVE_GIVEBACK_MIN_VALUE').":".$donor->giveback_value." ".$this->currency_code."<br>".JText::_('COM_JGIVE_GIVEBACK_DESC').":";

								if (strlen($donor->gb_description>50))
								{
		$html.=						substr($donor->gb_description,0,50)."...";
								}
								else
								{
		$html.=						$donor->gb_description;
								}
						}
						else
						{
		$html.=				JText::_('COM_JGIVE_NO');
						}


		$html.=	"</td>";
		$html.=	"<td>". $donor->cdate ."</td> ";

		$html.= "</tr>";
		$jgive_index ++;
		}

		$result = array();

		$result['jgive_index'] = $jgive_index;
		$result['records'] = $html;

		return $result;
	}

	function viewMoreDonorProPic($cid, $jgive_index)
	{
		$campaignHelper = new campaignHelper();
		$params = JComponentHelper::getParams('com_jgive');

		$limit = $params->get('donor_records', '', 'INT');
		$limit_start = $jgive_index;

		if(!$limit)
		{
			$limit=10;
		}

		$donors = $campaignHelper->getCampaignDonors($cid, $limit_start-1, $limit);
		$this->currency_code = $params->get('currency');

		$html = "";

		foreach($donors as $donor)
		{
			if(!$donor->avatar){//if no avatar, use default avatar
				$donor->avatar=JUri::root().'components/com_jgive/assets/images/default_avatar.png';
			}
			if($donor->annonymous_donation)
			{
				$title=JText::_("COM_JGIVE_DONOR_ANNONYMOUS_NAME").' - '.$donor->amount.' '.$this->currency_code;
				//if annonymous_donation, use annonymous avatar, reset url to blank
				$donor->avatar=JUri::root().'components/com_jgive/assets/images/annonymous.png';
				$donor->profile_url='';
			}
			else
			{
				$title=$donor->first_name.' '.$donor->last_name.' - '.$donor->amount.' '.$this->currency_code;
			}

		$html.=	'<div class="com_jgive_border_ano_donor">';

				if(!empty($donor->profile_url) && $donor->user_id!=0)
				{

		$html.=		'<a href="'.$donor->profile_url.'" target="_blank">
						<img class="com_jgive_img_48_48" src="'.$donor->avatar.'" title="'.$title.'"/>
					</a>';
				}
				else
				{
		$html.=		'<img class="com_jgive_img_48_48" src="'.$donor->avatar.'" title="'. $title.'"/>';
				}

		$html.='</div>';
			$jgive_index ++;
		}//end for loop

		$result = array();

		$result['jgive_index'] = $jgive_index;
		$result['records'] = $html;

		return $result;
	}
}
?>
