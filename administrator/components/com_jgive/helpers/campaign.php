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
// Component Helper
jimport('joomla.application.component.helper');
class campaignHelper
{
	function getCampaignPromoterPaypalId($orderid)
	{
		$db=JFactory::getDBO();
		$query="SELECT c.paypal_email
			FROM #__jg_campaigns AS c
			LEFT JOIN #__jg_orders AS o ON o.campaign_id=c.id
			WHERE o.id=".$orderid;
		$db->setQuery($query);
		return $db->loadResult();
	}

	function getCampaignTitle($orderid)
	{
		$db=JFactory::getDBO();
		$query="SELECT c.title
			FROM #__jg_campaigns AS c
			LEFT JOIN #__jg_orders AS o ON o.campaign_id=c.id
			WHERE o.id=".$orderid;
		$db->setQuery($query);
		return $db->loadResult();
	}

	function getCampaignTitleFromCid($cid)
	{
		$db=JFactory::getDBO();
		$query="SELECT c.title
			FROM #__jg_campaigns AS c
			WHERE c.id=".$cid;
		$db->setQuery($query);
		return $db->loadResult();
	}

	//used in backend - reports view
	function getAllCampaignOptions()
	{
		$db=JFactory::getDBO();
		$query="SELECT c.id, c.title
		FROM `#__jg_campaigns` AS c
		ORDER BY c.title";
		//WHERE c.id=".$cid;
		$db->setQuery($query);
		$campaigns=$db->loadObjectList();
		return $campaigns;
		//print_r($campaign);
	}

	function getCampaignAmounts($cid)
	{
		$db=JFactory::getDBO();

		$query="SELECT c.goal_amount
		FROM `#__jg_campaigns` AS c
		WHERE c.id=".$cid;
		$db->setQuery($query);
		$goal_amount=$db->loadResult();

		$query="SELECT SUM(o.amount) AS amount_received
		FROM `#__jg_orders` AS o
		WHERE o.campaign_id=".$cid."
		AND o.status='C'";
		$db->setQuery($query);
		$amounts=array();
		$amounts['amount_received']=$db->loadResult();
		//if no donations, set receved amount as zero
		if($amounts['amount_received']=='')
			$amounts['amount_received']=0;

		//calculate remaining amount
		$amounts['remaining_amount']=($goal_amount)- ($amounts['amount_received']);

		return $amounts;
	}

	function getCampaignImages($cid)
	{
		$db=JFactory::getDBO();
		$query="SELECT *
		FROM `#__jg_campaigns_images`
		WHERE `campaign_id`=".$cid;
		$db->setQuery($query);
		$images=$db->loadObjectList();
		return $images;
	}

	function getCampaignGivebacks($cid)
	{
		$db=JFactory::getDBO();
		$query="SELECT *
		FROM `#__jg_campaigns_givebacks`
		WHERE `campaign_id`=".$cid."
		ORDER BY amount ";
		$db->setQuery($query);
		$givebacks=$db->loadObjectList();
		return $givebacks;
	}

	function getCampaignDonors($cid)
	{
		$db=JFactory::getDBO();
		//get campaign donors
		$query="SELECT du.user_id, du.first_name, du.last_name, du.address, du.address2, du.city, du.state, du.country, du.zip,
		ds.annonymous_donation, o.amount, o.cdate
		FROM `#__jg_donors` AS du
		LEFT JOIN `#__jg_donations` AS ds on ds.donor_id=du.id
		LEFT JOIN `#__jg_orders` AS o on o.donation_id=ds.id
		WHERE ds.campaign_id=".$cid."
		AND o.status='C'";
		$db->setQuery($query);
		$donors=$db->loadObjectList();
		if(isset($donors))
		{
			foreach($donors as $donor)
			{
				$integrationsHelper=new integrationsHelper();
				$donor->avatar=$integrationsHelper->getUserAvatar($donor->user_id);
				$donor->profile_url=$integrationsHelper->getUserProfileUrl($donor->user_id);
			}
		}
		return $donors;
	}

	function getCampaignDonorsCount($cid)
	{
		$db=JFactory::getDBO();
		$query="SELECT COUNT(o.amount) AS donors_count
			FROM `#__jg_orders` AS o
			WHERE o.campaign_id=".$cid."
			AND o.status='C'";
		$db->setQuery($query);
		$donors_count=$db->loadResult();
		if($donors_count=='')
			$donors_count=0;
		return $donors_count;
	}

	function uploadImage($camp_id)
	{
		$db=JFactory::getDBO();

		//save uploaded image
		//check the file extension is ok
		$file_field='camp_image';
		$file_name = $_FILES[$file_field]['name'];
		$media_info=pathinfo($file_name);
		//print_r($media_info);
		$uploadedFileName=$media_info['filename'];
		$uploadedFileExtension=$media_info['extension'];
		$validFileExts = explode(',','jpeg,jpg,png,gif');
		//assume the extension is false until we know its ok
		$extOk=false;
		//go through every ok extension, if the ok extension matches the file extension (case insensitive)
		//then the file extension is ok
		foreach($validFileExts as $key => $value)
		{
			if( preg_match("/$value/i", $uploadedFileExtension ) )
			{
				$extOk = true;
			}
		}
		if ($extOk == false)
		{
			echo JText::_('COM_JGIVE_INVALID_IMAGE_EXTENSION');
			return;
		}

		//the name of the file in PHP's temp directory that we are going to move to our folder
		$file_temp=$_FILES[$file_field]['tmp_name'];
		//for security purposes, we will also do a getimagesize on the temp file (before we have moved it
		//to the folder) to check the MIME type of the file, and whether it has a width and height
		$image_info=getimagesize($file_temp);
		//we are going to define what file extensions/MIMEs are ok, and only let these ones in (whitelisting), rather than try to scan for bad
		//types, where we might miss one (whitelisting is always better than blacklisting)
		$okMIMETypes = 'image/jpeg,image/pjpeg,image/png,image/x-png,image/gif';
		$validFileTypes = explode(",", $okMIMETypes);
		//if the temp file does not have a width or a height, or it has a non ok MIME, return
		if( !is_int($image_info[0]) || !is_int($image_info[1]) ||  !in_array($image_info['mime'], $validFileTypes) )
		{
			echo JText::_('COM_JGIVE_INVALID_IMAGE_EXTENSION');
			return;
		}

		//Clean up filename to get rid of strange characters like spaces etc
		$file_name=JFile::makeSafe($uploadedFileName);
		//lose any special characters in the filename
		$file_name=preg_replace("/[^A-Za-z0-9]/i", "-", $file_name);
		//use lowercase
		$file_name=strtolower($file_name);
		//add timestamp to file name
		$timestamp=time();
		$file_name=$file_name.'_'.$timestamp.'.'.$uploadedFileExtension;

		//always use constants when making file paths, to avoid the possibilty of remote file inclusion
		$upload_path_folder=JPATH_SITE.DS.'images'.DS.'jGive';
		$image_upload_path_for_db='images'.DS.'jGive';
		//if folder is not present create it
		if(!file_exists($upload_path_folder)){
			@mkdir($upload_path_folder);
		}
		$upload_path=$upload_path_folder.DS.$file_name;
		$image_upload_path_for_db.=DS.$file_name;
		if(!JFile::upload($file_temp,$upload_path))
		{
			echo JText::_('COM_JGIVE_ERROR_MOVING_FILE');
			return false;
		}
		else
		{
			$obj=new stdClass();
			$obj->id='';
			$obj->campaign_id=$camp_id;
			$obj->path=$image_upload_path_for_db;
		//	$obj->video_provider=$post->get('video_provider','','STRING');
		//	$obj->video_url=$post->get('video_url','','STRING');
		//	$obj->video_img=$post->get('video_img','','INT');
			$obj->order='';
			if(!$db->insertObject('#__jg_campaigns_images',$obj,'id'))
			{
				echo $db->stderr();
				return false;
			}
			else 
			{
				return $db->insertid();
			}
		}

		return true;
	}
	function getAllCategoryOptions()
	{
		$db=JFactory::getDBO();
		$query="SELECT c.category_id,cat.title
		FROM `#__jg_campaigns` AS c
		INNER JOIN #__categories as cat ON cat.id=c.category_id
		ORDER BY cat.title";
		//WHERE c.id=".$cid;
		$db->setQuery($query);
		$campaigns=$db->loadObjectList();
		return $campaigns;
	}
	function getCampaignsCats() {
		$mainframe = JFactory::getApplication();
		$db	= JFactory::getDBO();
		$query = "SELECT id,title FROM #__categories WHERE extension='com_jgive' && parent_id=1";
		$db->setQuery($query);
		$categories=$db->loadObjectList();
		$default='';
		$options[] = JHtml::_('select.option', '0', '-Select Category-');

		foreach($categories as $cat_obj) {
		$options[] = JHtml::_('select.option', $cat_obj->id, $cat_obj->title);
		}

		if(JFactory::getApplication()->input->get('cid'))
		{
			$details=$this->getCampaignDetails(JFactory::getApplication()->input->get('cid'));
			$camp_cat=$details->category_id;

			$cid=JFactory::getApplication()->input->get('cid');
			$db	= JFactory::getDBO();
			$query = "SELECT * FROM #__jg_campaigns WHERE id='".$cid."'";
			$db->setQuery($query);

			$options         = array_merge( $options, $db->loadObjectlist() );
			$dropdown=JHtml::_('list.category', 'campaign_category', "com_jgive", intval( $camp_cat ));
		}
		else
			$dropdown=JHtml::_('list.category', 'campaign_category', "com_jgive", intval( 1 ));
//	 echo "<pre>";
	// print_r($dropdown);
	 return  $dropdown;
	}
	function getCampaignDetails($c_id)
	{
		$db	= JFactory::getDBO();
		$query = "SELECT * FROM #__jg_campaigns WHERE id='".$c_id."'";
		$db->setQuery($query);
		return($db->loadObject());
	}
	//get campaign category filter
	function getCampaignsCategories($firstOption='')
	{
		$app = JFactory::getApplication();
		$categories=JHtml::_('category.options', 'com_jgive');
		$cat_options=array();
		
		if($app->isSite() OR JVERSION<3.0)
			$cat_options[]=JHtml::_('select.option','0',JText::_('COM_JGIVE_CAMPAIGN_CATEGORIES'));

		if(!empty($categories))
		{
			foreach($categories as $category){
					if(!empty($category))
					{
						if(JVERSION>=3.0)
							$cat_options[]=JHtml::_('select.option',$category->value,$category->text);
						else
							$cat_options[]=JHtml::_('select.option',$category->get('value'),$category->get('text'));
					}
			}
		}
	return $cat_options;
	}
	function getCampaignTypeFilterOptions()
	{
		$mainframe=JFactory::getApplication();
		$filter_campaign_type=$mainframe->getUserStateFromRequest('com_jgive.filter_campaign_type','filter_campaign_type');

		$options=array();
		if(JVERSION<3.0)
			$options[]=JHtml::_('select.option','',JText::_('COM_JGIVE_FILTER_SELECT_TYPE'));
		$options[]=JHtml::_('select.option','donation',JText::_('COM_JGIVE_CAMPAIGN_TYPE_DONATION'));
		$options[]=JHtml::_('select.option','investment',JText::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT'));
     	return $options;
	}
	// Check the campaign marks as featured
	function isFeatured($contentId)
	{
			$db = JFactory::getDBO();
			$query  = 'SELECT featured FROM `#__jg_campaigns` WHERE `id` = ' . $contentId;
			$db->setQuery($query);
			$result = $db->loadResult();
		return	$result = (empty($result)) ? 0 : $result;
	}
	//get Campaign type Donate/Invest
	function getCampaignType($campid)
	{
			$db=JFactory::getDBO();
			$query='SELECT id,creator_id,type FROM `#__jg_campaigns` WHERE `id`= '.$campid;
			$db->setQuery($query);
			$result=$db->LoadObject();
		return $result=(empty($result))?'':$result;
	}

	// Send email to site admin when campaigns is created
	function sendCmap_create_mail($camp_details,$camp_id)
	{
		//echo "<pre>";print_r($camp_details);echo"</pre>";die;
		$params=JComponentHelper::getParams('com_jgive');
		$billemail=$params->get('email');
		if(!empty($billemail))
		{
			$userid=JFactory::getUser()->id;
			$body 	= JText::_('COM_JGIVE_CAMP_AAPROVAL_BODY');
			$body	= str_replace('{title}', $camp_details->get('title','','STRING'), $body);
			$body	= str_replace('{campid}', ':'.$camp_id, $body);
			$body	= str_replace('{username}', $camp_details->get('first_name','','STRING'), $body);
			$body	= str_replace('{userid}', $userid, $body);
			$body	= str_replace('{link}', JUri::base().'administrator/index.php?option=com_jgive&view=campaigns&layout=all_list&approve=1', $body);
			$subject=JText::sprintf('COM_JGIVE_CAMP_CREATED_EMAIL_SUBJECT',$camp_details->get('title','','STRING'));
			$donationsHelper=new donationsHelper();
			$donationsHelper->sendmail($billemail,$subject,$body,$params->get('email'));
		}
	}
	// Send email to promoter when campaigns is created
	function sendCmapCreateMailToPromoter($camp_details,$camp_id)
	{
		//echo "<pre>";print_r($camp_details);echo"</pre>";die;
		$user=JFactory::getUser();
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$donationsHelper=new donationsHelper();
		$body 	= JText::_('COM_JGIVE_CAMP_AAPROVAL_BODY_PROMOTER');
		$body	= str_replace('{title}', $camp_details->get('title','','STRING'), $body);
		$body	= str_replace('{campid}', ':'.$camp_id, $body);// embed campid
		$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my');//get item id
		$body	= str_replace('{link}', JUri::base().'index.php?option=com_jgive&view=campaigns&layout=my&itemid='.$itemid, $body);
		$billemail=$user->email;// to send email
		$subject=JText::sprintf('COM_JGIVE_CAMP_CREATED_EMAIL_SUBJECT_PROMOTER',$camp_details->get('title','','STRING'));
		$donationsHelper->sendmail($billemail,$subject,$body,$user->email);
	}
	//send email to promoter after approved campaign
	function sendemailCampaignApprovedReject($camp_info,$state)
	{
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$donationsHelper=new donationsHelper();
		$objectcount=count($camp_info);
		foreach($camp_info as $each_creator )
		{
			$html=JText::_('COM_JGIVE_CAMPAIGN_DETAILS_MAIL');
			$html.='<table cellspacing="15">
					<th>'.JText::sprintf('COM_JGIVE_CAMPAIGN_ID').'</th>
					<th>'.JText::sprintf('COM_JGIVE_CAMPAIGN_NAME').'</th> ';
			foreach($each_creator as $row)
			{
				$html.='<tr>
							<td>'.
								$row->id
							.'</td>
							<td>'.
								$row->title
							.'</td>
						</tr>';
				$billemail=$row->email;
			}
			$html.='</table>';

			$subject=JText::_('COM_JGIVE_CAMP_CREATED_EMAIL_SUBJECT_PROMOTER_APPROVED');

			//according to state get approved / rejected /delete language constant
			if($state==0) //rejected
				$campaign_status=JText::sprintf('COM_JGIVE_CAMAPIGN_REJECTED');
			else if($state==1) //approved
				$campaign_status= JText::sprintf('COM_JGIVE_CAMAPIGN_APPROVED');
			else //deleted
				$campaign_status= JText::sprintf('COM_JGIVE_CAMAPIGN_DELETED');

			$subject=str_replace('{status}', $campaign_status, $subject);// embed campid
			// my campaign link for user only when campaign is approved
			if($state==1 or $state==0)
			{
				$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my');//get item id
				$html.=JText::_('COM_JGIVE_CLICK_HERE');
				$html.=JUri::root().'index.php?option=com_jgive&view=campaigns&layout=my&itemid='.$itemid;
			}
			$donationsHelper->sendmail($billemail,$subject,$html,$billemail);
		}
	}
	function getCatname($catid)
	{
		$db=JFactory::getDBO();
	 	$query="SELECT title FROM #__categories as cat
				WHERE cat.id=".$catid." AND `extension`='com_jgive'";
		$db->setquery($query);
		return $result=$db->loadResult();
	}
	//function to add the organization/individual type field Individuals 
	function organization_individual_type()
	{
		$options=array();
		$app = JFactory::getApplication();
		
		if($app->isSite() OR JVERSION<3.0)
			$options[]=JHtml::_('select.option','',JText::_('COM_JGIVE_SELECT_TYPE_ORG_INDIVIDUALS'));
		$options[]=JHtml::_('select.option','non_profit',JText::_('COM_JGIVE_ORG_NON_PROFIT'));
		$options[]=JHtml::_('select.option','self_help',JText::_('COM_JGIVE_SELF_HELP'));
		$options[]=JHtml::_('select.option','individuals',JText::_('COM_JGIVE_SELF_INDIVIDUALS'));

		return $options;
	}
	function campaignsToShowOptions()
	{
		$options=array();
		$app = JFactory::getApplication();
		
		if($app->isSite() OR JVERSION<3.0)
			$options[]=JHtml::_('select.option','',JText::_('COM_JGIVE_CAM_TO_SHOW'));

		$options[]=JHtml::_('select.option','featured',JText::_('COM_JGIVE_FEATURED_CAMP'));
		$options[]=JHtml::_('select.option','successful',JText::_('COM_JGIVE_SUCCESSFUL_CAMP'));
		$options[]=JHtml::_('select.option','other',JText::_('COM_JGIVE_OTHTER_CAMP'));

		return $options;
	}
}
?>
