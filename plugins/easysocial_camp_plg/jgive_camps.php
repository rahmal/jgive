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

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helper.php';

if(!class_exists('jgiveFrontendHelper'))
{
  //require_once $path;
   JLoader::register('jgiveFrontendHelper', $helperPath );
   JLoader::load('jgiveFrontendHelper');
}

Foundry::import( 'admin:/includes/apps/apps' );

/**
 * Friends application for EasySocial.
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialUserAppjGive_camps extends SocialAppItem
{
	/**
	 * Class constructor.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct()
	{
		parent::__construct();
		
	}
}
