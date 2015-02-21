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
jimport( 'joomla.filesystem.file' );
jimport( 'joomla.filesystem.folder' );
class activitysocialintegrationprofiledata
{
	function getUserProfileUrl($integration_option,$userid)
	{
		//$cominvitexHelper=new cominvitexHelper();
		//$invitex_settings=$cominvitexHelper->getconfigData();
		//$params=JComponentHelper::getParams('com_jlike');
		//$integration_option=$invitex_settings['reg_direct'];
		//$integration_option='JomSocial'
		$link='';		
		if($integration_option=='Joomla')
		{
			//$itemid=jgiveFrontendHelper::getItemId('option=com_users');		
			$link='';
		}
		else if($integration_option=='Community Builder')
		{
			$cbpath=JPATH_ROOT.'/'.'components'.'/'.'com_community';
			if($cbpath){
				//$itemid=$cominvitexHelper->getItemId('option=com_comprofiler');		
				$link=JUri::root().substr(JRoute::_('index.php?option=com_comprofiler&task=userprofile&user='.$userid.'&Itemid='.$itemid),strlen(JUri::base(true))+1);
			}		
		}
		else if($integration_option=='JomSocial')
		{
			$jspath=JPATH_ROOT.'/'.'components'.'/'.'com_community';
			if($jspath){
				$link='';
				
				if(file_exists($jspath)){
					include_once($jspath.'/'.'libraries'.'/'.'core.php');
			
				$link=JUri::root().substr(CRoute::_('index.php?option=com_community&view=profile&userid='.$userid),strlen(JUri::base(true))+1);
				}
			}
		}
		else if($integration_option=='Jomwall')
		{
			$awdpath=JPATH_ROOT.'/'.'components'.'/'.'com_awdwall';
			if($awdpath){
				if(!class_exists('AwdwallHelperUser')){
					require_once(JPATH_SITE.'/'.'components'.'/'.'com_awdwall'.'/'.'helpers'.'/'.'user.php');	
				}
				$awduser=new AwdwallHelperUser();
				$Itemid=$awduser->getComItemId();
				$link=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$userid.'&Itemid='.$Itemid);
			}
		}
		else if($integration_option=='EasySocial')
		{
			$espath=JPATH_ROOT.'/'.'components'.'/'.'com_easysocial';
			if($espath){
				$link='';
				
				if(file_exists($espath)){
					require_once( JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php' );
					$user     = Foundry::user( $userid );
					$link=JRoute::_($user->getPermalink());
				}
			}
		}
		return $link;
	}
	
	function getUserAvatar($integration_option,$user)
	{
		
		$activitysocialintegrationprofiledata=new activitysocialintegrationprofiledata();
		$useremail=$user->email;
		$userid=$user->id;
		$uimage='';		
		if($integration_option=="Joomla")
		{
			$uimage=$activitysocialintegrationprofiledata->get_gravatar($useremail, '40', 'mm', 'g');
		}
		else if($integration_option=="Community Builder")
		{
			$installed=$activitysocialintegrationprofiledata->Checkifinstalled('com_comprofiler');
			if($installed){
				$uimage=$activitysocialintegrationprofiledata->getCBUserAvatar($userid);
			}
		}
		else if($integration_option=="JomSocial")
		{
			$installed=$activitysocialintegrationprofiledata->Checkifinstalled('com_community');
			if($installed){
				$uimage=$activitysocialintegrationprofiledata->getJomsocialUserAvatar($userid);
			}
		}
		else if($integration_option=="Jomwall")
		{
			$installed=$activitysocialintegrationprofiledata->Checkifinstalled('com_awdwall');
			if($installed){
				$uimage=$activitysocialintegrationprofiledata->getJomwallUserAvatar($userid);
			}
		}
		else if($integration_option=="EasySocial")
		{
			$installed=$activitysocialintegrationprofiledata->Checkifinstalled('com_easysocial');
			if($installed){
				$uimage=$activitysocialintegrationprofiledata->getEasySocialUserAvatar($userid);
			}
		}
		return $uimage;
	}
	
	
	function Checkifinstalled($folder){
		$path	=	JPATH_SITE . '/' .'components'. '/' .$folder;
		if(JFolder::exists($path))
				return true;
		else 
			return false;
					
	}
	
	
	function getEasySocialUserAvatar($userid)
	{
		require_once( JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php' );
		$user     = Foundry::user( $userid );
		$uimage=$user->getAvatar();
		return $uimage;
	}
	
	function getCBUserAvatar($userid)
	{	
		$db=JFactory::getDBO();
		$q="SELECT a.id,a.username,a.name, b.avatar, b.avatarapproved 
            FROM #__users a, #__comprofiler b 
            WHERE a.id=b.user_id AND a.id=".$userid;
        $db->setQuery($q);
        $user=$db->loadObject();
		$img_path=JUri::root()."images/comprofiler";		
		if(isset($user->avatar) && isset($user->avatarapproved))
		{
			if(substr_count($user->avatar, "/") == 0)
			{
				$uimage = $img_path . '/tn' . $user->avatar;
			}
			else
			{
				$uimage = $img_path . '/' . $user->avatar;
			}
		}
		else if (isset($user->avatar))
		{//avatar not approved
			$uimage = JUri::root()."/components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}
		else
		{//no avatar
			$uimage = JUri::root()."/components/com_comprofiler/plugin/templates/default/images/avatar/nophoto_n.png";
		}		
		return $uimage;
	}
	
	function getJomsocialUserAvatar($userid)
	{
		$mainframe=JFactory::getApplication();
		/*included to get jomsocial avatar*/
		$uimage='';
		$jspath=JPATH_ROOT.'/'.'components'.'/'.'com_community';
		if(file_exists($jspath)){
			include_once($jspath.'/'.'libraries'.'/'.'core.php');
		
			$user=CFactory::getUser($userid);
			$uimage=$user->getThumbAvatar();        
			if(!$mainframe->isSite())
			{
				$uimage=str_replace('administrator/','',$uimage);
			}
		}
        return $uimage;
	}
	
	function getJomwallUserAvatar($userid)
	{
		if(!class_exists('AwdwallHelperUser')){
			require_once(JPATH_SITE.'/'.'components'.'/'.'com_awdwall'.'/'.'helpers'.'/'.'user.php');	
		}
		$awduser=new AwdwallHelperUser();
		$uimage=$awduser->getAvatar($userid);
		
        return $uimage;
	}
	function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g' ) 
	{
    $url = 'http://www.gravatar.com/avatar/';
	  $url .= md5( strtolower( trim( $email ) ) );
    $url .= "?s=$s&d=$d&r=$r";
	  return $url;
	}
}
?>
