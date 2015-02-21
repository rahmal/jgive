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
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

/**
 * Profile view for Notes app.
 *
 * @since	1.0
 * @access	public
 */
class jGive_campsWidgetsProfile extends SocialAppsWidgets
{
	/**
	 * Display user photos on the side bar
	 *
	 * @since	1.0
	 * @access	public
	 * @param	string
	 * @return
	 */
	//public function sidebarBottom( $user )
	
	public function aboveHeader( $user )
	{
		
	}
	public function sidebarBottom( $user )
	{
		//die('ddddddd');
		// Get the user params
		$params 	= $this->getUserParams( $user->id );

		//echo $this->getFriends( $user , $params );
		echo $this->_getquick2cartstoreHTML( $user , $params );
	}
	
	function _getquick2cartstoreHTML( $user , $params )
	{
		jimport('joomla.filesystem.file');
		if( JFile::exists( JPATH_SITE.'/components/com_jgive/jgive.php') ){

			$appParams 	= $this->app->getParams();
			$profile_no_of_camp = $params->get('profile_no_of_camp','2');

			//Get profile id
			$user=  Foundry::user( $user->id );

			/*load language file for plugin frontend*/
			$lang=JFactory::getLanguage();
			$lang->load('plg_app_user_jgive_camps',JPATH_ADMINISTRATOR);

			//load Campaigns helper
			$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'campaign.php';
			if(!class_exists('campaignHelper'))
			{
				JLoader::register('campaignHelper', $helperPath );
				JLoader::load('campaignHelper');
			}
			//load jgive front end helper
			$helperFrontPath=JPATH_SITE.DS."components".DS."com_jgive".DS."helper.php";

			if(!class_exists('jgiveFrontendHelper'))
			{
			  //require_once $path;
			   JLoader::register('jgiveFrontendHelper', $helperFrontPath );
			   JLoader::load('jgiveFrontendHelper');
			}
			$theme 		= Foundry::themes();
			$theme->set( 'user'		, $user );
			$theme->set( 'profile_no_of_camp', $profile_no_of_camp );
			$theme->set( 'params'	, $params );

		//	return parent::display( 'widgets/profile/default' );
		return $theme->output( 'themes:/apps/user/jgive_camps/widgets/profile/jgive_camps' );

		}
	}  // end of function


}
