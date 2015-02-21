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
class integrationsHelper_backend
{
	function getUserProfileUrl($userid)
	{
		$path=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helper.php';
		if(!class_exists('jgiveFrontendHelper'))
		{
			JLoader::register('jgiveFrontendHelper', $path );
			JLoader::load('jgiveFrontendHelper');
		}
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$params=JComponentHelper::getParams('com_jgive');
		$integration_option=$params->get('integration');		
		$link='';		
		if($integration_option=='joomla')
		{
			//$itemid=jgiveFrontendHelper::getItemId('option=com_users');		
			$link='';
		}
		else if($integration_option=='cb')
		{
			$itemid=$jgiveFrontendHelper->getItemId('option=com_comprofiler');		
			$link=JUri::root().substr(JRoute::_('index.php?option=com_comprofiler&task=userprofile&user='.$userid.'&Itemid='.$itemid),strlen(JUri::base(true))+1);
		}
		else if($integration_option=='jomsocial')
		{
			$link='';
			$jspath=JPATH_ROOT.DS.'components'.DS.'com_community';
			if(file_exists($jspath)){
				include_once($jspath.DS.'libraries'.DS.'core.php');
			
			$link=JUri::root().substr(CRoute::_('index.php?option=com_community&view=profile&userid='.$userid),strlen(JUri::base(true))+1);
			}
			
		}
		else if($integration_option=='jomwall')
		{
			if(!class_exists('AwdwallHelperUser')){
				require_once(JPATH_SITE.DS.'components'.DS.'com_awdwall'.DS.'helpers'.DS.'user.php');	
			}
			$awduser=new AwdwallHelperUser();
			$Itemid=$awduser->getComItemId();
			$link=JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid='.$userid.'&Itemid='.$Itemid);
		}
		return $link;
	}
	
	function getUserAvatar($userid)
	{
		$integrationsHelper=new integrationsHelper();
		$params=JComponentHelper::getParams('com_jgive');
		$integration_option=$params->get('integration');		
		$uimage='';		
		if($integration_option=="joomla")
		{
			$uimage='';
		}
		else if($integration_option=="cb")
		{
			$uimage=$integrationsHelper->getCBUserAvatar($userid);
		}
		else if($integration_option=="jomsocial")
		{
			$uimage=$integrationsHelper->getJomsocialUserAvatar($userid);
		}
		else if($integration_option=="jomwall")
		{
			$uimage=$integrationsHelper->getJomwallUserAvatar($userid);
		}		
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
		$jspath=JPATH_ROOT.DS.'components'.DS.'com_community';
		if(file_exists($jspath)){
			include_once($jspath.DS.'libraries'.DS.'core.php');
		
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
			require_once(JPATH_SITE.DS.'components'.DS.'com_awdwall'.DS.'helpers'.DS.'user.php');	
		}
		$awduser=new AwdwallHelperUser();
		$uimage=$awduser->getAvatar($userid);
		
        return $uimage;
	}
	
	function loadScriptOnce($script)
	{
		$doc = JFactory::getDocument();
		$flg=0;
		foreach($doc->_scripts as $name=>$ar)
		{
			if($name==$script){
				$flg=1;
			}
		}
		if($flg==0){
			$doc->addScript($script);
		}
	}
	
	//function for  profile import
	function profileImport($paymentform='',$userid='')
	{
		$integrationsHelper=new integrationsHelper();
		
		if(JVERSION>=3.0)
			$cdata['campaign']=new stdclass();
		else
			$cdata=array();//imp
		
		$params=JComponentHelper::getparams('com_jgive');
		$integration=$params->get('integration');
		
		if($integration=='joomla')
		{
			$cdata=$integrationsHelper->joomlaProfileimport($paymentform,$userid);
		}
		else if($integration=='jomsocial')
		{
			$cdata=$integrationsHelper->jomsocialProfileimport($paymentform,$userid);
		}
		else if($integration=='cb')
		{
			$cdata=$integrationsHelper->cbProfileimport($paymentform,$userid);
		}
		return $cdata;
	}
	
	//function profile import for joomla
	function joomlaProfileimport($paymentform='',$userid='')
	{
		//$cdata=array();//imp
		$cdata['campaign']=new stdclass();
		$params=JComponentHelper::getparams('com_jgive');
		if($userid)
		$user=JFactory::getuser($userid);
		else
		$user=JFactory::getuser();

		$userinfo=JArrayHelper::fromObject($user, $recurse=true, $regex=null);
		$user_profile=JUserHelper::getProfile($user->id);
		//convert object to array
		$user_profile=JArrayHelper::fromObject($user_profile, $recurse=true, $regex=null);
		$mapping=$params->get('fieldmap');
		$mapping_field=explode("\n",$mapping);
		foreach($mapping_field as $each_field)
		{
			$field=explode("=",$each_field);
			$jgive_field='';
			$joomla_field='';
			if(isset($field[1]))
			{
				$jgive_field=trim($field[0]);
				$joomla_field=trim($field[1]);
				$joomla_field=trim(str_replace(',*','',$joomla_field));//remove campalsory star
			}
			if($joomla_field!='password') // for security mapping not allowed for user password
			{
				if(array_key_exists($joomla_field,$userinfo))
				{
					if($paymentform) //for paymentform layout
						$cdata[$jgive_field]=$userinfo[$joomla_field];
					else //for create campaign layout
						$cdata['campaign']->$jgive_field=$userinfo[$joomla_field];
				}
				else 
				{
					//if($jgive_field!='default_country') // @ TO DO For country/state/city
					if(!empty($user_profile['profile']))
					{
						if(array_key_exists($joomla_field,$user_profile['profile']))
						{
							if($paymentform)
								$cdata[$jgive_field]=$user_profile['profile'][trim($joomla_field)];
							else
								$cdata['campaign']->$jgive_field=$user_profile['profile'][trim($joomla_field)];
						}
					}
				}
			}
		}
		return 	$cdata;	
	}
	
	//function profile import for cb
	function cbProfileimport($paymentform,$userid='')
	{
		//load CB framework
		global $_CB_framework, $mainframe,$_CB_database, $ueConfig;
		
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
		if($userid)
		$myId		=	$_CB_framework->myId($userid);
		else
		$myId		=	$_CB_framework->myId();

		$cbUser		=	CBuser::getInstance( $myId );
		if ( ! $cbUser ) {
			$cbUser	=&	CBuser::getInstance( null );
		}
		$user		=&	$cbUser->getUserData();
	
		$cdata=array();//imp
		$params=JComponentHelper::getparams('com_jgive');
		$userinfo=JArrayHelper::fromObject($user, $recurse=true, $regex=null);
		$mapping=$params->get('cb_fieldmap');
		$mapping_field=explode("\n",$mapping);
		foreach($mapping_field as $each_field)
		{
			$field=explode("=",$each_field);
			$jgive_field='';
			$CB_field='';
			if(isset($field[1]))
			{
				$jgive_field=trim($field[0]);
				$CB_field=trim($field[1]);
				$CB_field=trim(str_replace(',*','',$CB_field));//remove campalsory star
			}
			if($CB_field!='password') // for security mapping not allowed for user password
			{
				if(array_key_exists($CB_field,$userinfo))
				{
					if($paymentform) //for paymentform layout
						$cdata[$jgive_field]=$userinfo[$CB_field];
					else //for create campaign layout
						$cdata['campaign']->$jgive_field=$userinfo[$CB_field];
				}
			}
		}
		return 	$cdata;	
	}
	
	//function profile import for jomsocial
	function jomsocialProfileimport($paymentform='',$userid='')
	{
		
		//$cdata=array();//imp
		$cdata['campaign']=new stdclass();
		$params=JComponentHelper::getparams('com_jgive');
		$jspath = JPATH_ROOT.DS.'components'.DS.'com_community';
		
		if(!file_exists($jspath))
			return;
		
		include_once($jspath.DS.'libraries'.DS.'core.php');			
		$userpro = CFactory::getUser();
		
		//get jomsocial user profile info
		if($userid)
		$user= CFactory::getUser($userid);
		else
		$user= CFactory::getUser();

		$userinfo=JArrayHelper::fromObject($user, $recurse=true, $regex=null);
		$mapping=$params->get('jomsocial_fieldmap');
		$mapping_field=explode("\n",$mapping);
		foreach($mapping_field as $each_field)
		{
			$field=explode("=",$each_field);
			$jgive_field='';
			$jomsocial_field='';
			if(isset($field[1]))
			{
				$jgive_field=trim($field[0]);
				$jomsocial_field=trim($field[1]);
				$jomsocial_field=trim(str_replace(',*','',$jomsocial_field));//remove campalsory star
			}
			if($jomsocial_field!='password') // for security mapping not allowed for user password
			{
				if(array_key_exists($jomsocial_field,$userinfo))
				{
					if($paymentform) //for paymentform layout
					{	
						if(!empty($userinfo[$jomsocial_field]))
							$cdata[$jgive_field]=$userinfo[$jomsocial_field];
					}
					else //for create campaign layout
					{
						if(!empty($userinfo[$jomsocial_field]))
							$cdata['campaign']->$jgive_field=$userinfo[$jomsocial_field];
					}
				}
				else 
				{
					$userInfo=$userpro->getInfo($jomsocial_field);
					if(!empty($userInfo))
					{
						if($paymentform)
							$cdata[$jgive_field]=$userInfo;
						else
							$cdata['campaign']->$jgive_field=$userInfo;
					}
				}
			}
		}
		return $cdata;
	}
	
	//function to check profile completion
	function profileChecking()
	{
		$integrationsHelper=new integrationsHelper();
		$params=JComponentHelper::getParams('com_jgive');
		$integration=$params->get('integration');
		$msg_field_required=array();

		if($integration=='joomla')
		{
			$msg_field_required=$integrationsHelper->joomlaProfileChecking($params);
		}
		else if($integration=='jomsocial')
		{
			//$msg_field_required=integrationsHelper::jomsocialProfileChecking($params);
		}
		return $msg_field_required;
	}
	
	//function to check integration joomla user profile complete
	function joomlaProfileChecking($params)
	{
		$msg_field_required=array();
		$user=JFactory::getUser();
		//convert object to array
		$user_profile=JUserHelper::getProfile($user->id);
		//convert object to array
		$user=JArrayHelper::fromObject($user, $recurse=true, $regex=null);
		$user_profile=JArrayHelper::fromObject($user_profile, $recurse=true, $regex=null);
		$mapping=$params->get('fieldmap');
		$required_field=explode("\n",$mapping);
		if(isset($required_field))
		foreach($required_field as $eachfield)
		{
			$eachfield=explode(",",$eachfield); // 
			if(isset($eachfield[1])){ // indentify required field  
				$row=$eachfield[0];
				$required_tmp=explode("=",$row); // 
				//get required field name
				$required_field=$required_tmp[1]; 
				//check user value present or not in user table
				if($required_field!='password') // for security mapping not allowed for user password
				{
					if((array_key_exists($required_field,$user)) OR (array_key_exists($required_field,$user_profile['profile'])) ) //if field not set is user array  then check  field in user profile array
					{
						$userfield='';
						$userProfilefield='';
						if(!empty($user[$required_field]))
							$userfield=trim($user[$required_field]);
						
						if(empty($userfield))  
						{
							if(!empty($user_profile['profile'][$required_field]))
								$userProfilefield=trim($user_profile['profile'][$required_field]);
							
							if(empty($userProfilefield))
							{
								$msg_field_required[]=$required_field;
							}
						}
					}
					else if(empty($user_profile['profile'])) //if user not edit his account first time after profile plugin is enabled
					{
						$msg_field_required[]=$required_field;
					}
				}
			}
		}
		return $msg_field_required;
	}
	
	//function to check integration jomsocial user profile complete
	function jomsocialProfileChecking($params)
	{
		$jspath = JPATH_ROOT.DS.'components'.DS.'com_community';
		if(!file_exists($jspath))
			return;
		include_once($jspath.DS.'libraries'.DS.'core.php');			
		$user =& CFactory::getUser();
		$msg_field_required=array();
		//convert object to array
		$user=JArrayHelper::fromObject($user, $recurse=true, $regex=null);
		$mapping=$params->get('jomsocial_fieldmap');
		$required_field=explode("\n",$mapping);
		
		if(isset($required_field))
		foreach($required_field as $eachfield)
		{
			$eachfield=explode(",",$eachfield); 
			if(isset($eachfield[1])) //indentify required field
			{ 
				$row=$eachfield[0];
				$required_tmp=explode("=",$row); 
				//get required field name
				$required_field=trim($required_tmp[1]); 
				//check user value present or not in user table
				if($required_field!='password') // for security mapping not allowed for user password
				{
					if(array_key_exists($required_field,$user))  //if field not set is user array  then check  field in user profile array
					{
						$userfield='';
						$userProfilefield='';
						if(!empty($user[$required_field]))
							$userfield=trim($user[$required_field]);
					}
					else
					{	
						$userpro =& CFactory::getUser();
						$userInfo=$userpro->getInfo($required_field);
						if(empty($userInfo))
						{
							$msg_field_required[]=$required_field;
						}
					}
				}
			}
		}
		return $msg_field_required;
	}
}
?>
