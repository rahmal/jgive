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
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.plugin.plugin' );
jimport('joomla.filesystem.file');
jimport('joomla.application.application' );

class plgSystemJgive_api extends JPlugin
{

	function OnAfterJGiveCampaignSave($cid, $post)
	{
		// Check the campaign is published.
		$published=$this->checkCampaignIsPublished($cid);

		if(!$published)
		{
			return false;
		}

		// Push activity to various activity streams.
		// Set some of the data for activity.
		$act_subtype='campaign';
		$act_description=JText::_('COM_JGIVE_ACTIVITY_CREATED_CAMPAIGN');
		$result=$this->pushActivity($cid,$act_subtype,$act_description);

		if(!$result)
		{
			return false;
		}

		return true;
	}

	function OnAfterJGiveCampaignDelete($cids)
	{
		//return true;
	}

	function OnAfterJGiveCampaignEdit($cid, $post, $newDetails, $newDetailsImage, $oldDetails, $oldDetailsImage)
	{
		//return true;
	}

	function OnAfterJGivePaymentStatusChange($order_id_key, $status, $comment, $send_mail)
	{
	}

	function OnAfterJGivePaymentStatusProcess($orderid, $orderStatus)
	{
	}

	function OnAfterJGivePaymentSuccess($orderid)
	{
		// Push activity to various activity streams.
		// Set some of the data for activity.
		$path = JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php';

		if(!class_exists('donationsHelper'))
		{
			JLoader::register('donationsHelper', $path );
			JLoader::load('donationsHelper');
		}

		$donationsHelper = new donationsHelper();
		$cid = $donationsHelper->getCidFromOrderId($orderid);
		$uid = $donationsHelper->getDonorIdFromOrderId($orderid);

		//set some of the data for activity
		$act_subtype     = 'payment';
		$act_description = JText::_('COM_JGIVE_ACTIVITY_DONATED');
		$result          = $this->pushActivity($cid, $act_subtype, $act_description, $uid);

		if(!$result)
		{
			return false;
		}

		return true;
	}

	function pushActivity($cid,$act_subtype,$act_description,$uid=0)
	{
		$params=JComponentHelper::getParams('com_jgive');
		$integration_option=$params->get('integration');
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		//set activity data to be pushed
		$user=JFactory::getUser();

		$actor_id=$user->get('id');
		if(!$actor_id)
			$actor_id=$uid;
		$act_type='jgive';

		$singleCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all',1);
		$act_link=JURI::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$cid.'&Itemid='.$singleCampaignItemid),strlen(JURI::base(true))+1);

		$path=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'campaign.php';
		if(!class_exists('campaignHelper'))
		{
			JLoader::register('campaignHelper',$path);
			JLoader::load('campaignHelper');
		}
		$campaignHelper=new campaignHelper();

		$act_title=$campaignHelper->getCampaignTitleFromCid($cid);

		if($integration_option=='joomla')
		{
			return true;
		}
		else if($integration_option=='cb')
		{
			$result=$this->pushToCBActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title);
			if(!$result){
				return false;
			}
		}
		else if($integration_option=='jomsocial')
		{
			$result=$this->pushToJomsocialActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title);
			if(!$result){
				return false;
			}
		}
		else if($integration_option=='jomwall')
		{
			$result=$this->pushToJomwallActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title);
			if(!$result){
				return false;
			}
		}
		else if($integration_option=='EasySocial')
		{
			$result=$this->pushToEasySocialActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title);
			if(!$result){
				return false;
			}
		}

		return true;
	}

	function pushToEasySocialActivity($actor_id,$act_type='',$act_subtype='',$act_description='',$act_link='',$act_title='')
	{
		require_once( JPATH_ROOT . '/administrator/components/com_easysocial/includes/foundry.php' );

		$linkHTML='<a href="'.$act_link.'">'.$act_title.'</a>';
		if($actor_id!=0)
		$myUser = Foundry::user( $actor_id );
		$stream = Foundry::stream();
		$template = $stream->getTemplate();
		$template->setActor( $actor_id, SOCIAL_TYPE_USER );
		$template->setContext( $actor_id, SOCIAL_TYPE_USERS );
		$template->setVerb( 'jgive_campaign' );
		$template->setType( SOCIAL_STREAM_DISPLAY_MINI );
		if($actor_id!=0)
		{
			$userProfileLink = '<a href="'. $myUser->getPermalink() .'">' . $myUser->getName() . '</a>';
			$title 	 = ($userProfileLink." ".$act_description."".$linkHTML);
		}
		else
		$title 	 = ("A guest ".$act_description);
		$template->setTitle( $title );
		$template->setAggregate( false );

		$template->setPublicStream( 'core.view' );
		$stream->add( $template );
		return true;
	}
	function pushToCBActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title)
	{
		//load CB framework
		global $_CB_framework, $mainframe;
		if(defined( 'JPATH_ADMINISTRATOR'))
		{
			if(!file_exists(JPATH_ADMINISTRATOR.'/components/com_comprofiler/plugin.foundation.php'))
			{
				echo 'CB not installed!';
				return false;
			}
			include_once( JPATH_ADMINISTRATOR.'/components/com_comprofiler/plugin.foundation.php' );
		}
		else
		{
			if(!file_exists($mainframe->getCfg('absolute_path').'/administrator/components/com_comprofiler/plugin.foundation.php'))
			{
				echo 'CB not installed!';
				return false;
			}
			include_once( $mainframe->getCfg('absolute_path').'/administrator/components/com_comprofiler/plugin.foundation.php' );
		}

		cbimport('cb.plugins');
		cbimport('cb.html');
		cbimport('cb.database');
		cbimport('language.front');
		cbimport('cb.snoopy');
		cbimport('cb.imgtoolbox');

		global $_CB_framework, $_CB_database, $ueConfig;

		//load cb activity plugin class
		if(!file_exists(JPATH_SITE.DS."components".DS."com_comprofiler".DS."plugin".DS."user".DS."plug_cbactivity".DS."cbactivity.class.php"))
		{
			//echo 'CB Activity plugin not installed!';
			return false;
		}
		require_once(JPATH_SITE.DS."components".DS."com_comprofiler".DS."plugin".DS."user".DS."plug_cbactivity".DS."cbactivity.class.php");

		//push activity
		$linkHTML='<a href="'.$act_link.'">'.$act_title.'</a>';

		$activity=cbactivityData::getActivity(array('id','=',$id),null,null,false);
		$activity->set('user_id',$actor_id);
		$activity->set('type',$act_type);
		$activity->set('subtype',$act_subtype);
		$activity->set('title', $act_description.' '.$linkHTML);
		$activity->set('icon','nameplate');
		$activity->set('date',cbactivityClass::getUTCDate() );
		$activity->store();

		return true;
	}

	function pushToJomsocialActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title)
	{
		/*load Jomsocial core*/
		$jspath=JPATH_ROOT.DS.'components'.DS.'com_community';
		if(file_exists($jspath)){
			include_once($jspath.DS.'libraries'.DS.'core.php');
		}

		//push activity
		$linkHTML='<a href="'.$act_link.'">'.$act_title.'</a>';
		$act=new stdClass();
		$act->cmd='wall.write';
		$act->actor=$actor_id;
		$act->target=0; // no target
		$act->title='{actor} ' .$act_description.' '.$linkHTML;
		$act->content='';
		$act->app='wall';
		$act->cid=0;
		$jspath=JPATH_ROOT.DS.'components'.DS.'com_community';
		if(file_exists($jspath)){
		CFactory::load('libraries','activities');
		CActivityStream::add($act);
		return true;
		}
		return false;
	}

	function pushToJomwallActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title)
	{
		/*load jomwall core*/
		if(!class_exists('AwdwallHelperUser')){
			require_once(JPATH_SITE.DS.'components'.DS.'com_awdwall'.DS.'helpers'.DS.'user.php');
		}
		$linkHTML='<a href="'.$act_link.'">'.$act_title.'</a>';
		$comment=$act_description.' '.$linkHTML;
		$attachment=$act_link;
		$type='text';
		$imgpath=NULL;
		$params=array();

		AwdwallHelperUser::addtostream($comment,$attachment,$type,$actor_id,$imgpath,$params);

		return true;
	}
	function checkCampaignIsPublished($cid)
	{
		$db=JFactory::getDBO();
		$query="SELECT c.published FROM #__jg_campaigns as c where c.id=".$cid;
		$db->setQuery($query);
		$campaignStatus=$db->LoadResult();
		if($campaignStatus)
			return 1;
		return 0;
	}
}//end class
