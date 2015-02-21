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

jimport('joomla.application.component.controller');

class jgiveController extends JControllerLegacy
{
	/**
	 * Custom Constructor
	 */

	function __construct()
	{
		parent::__construct();
	}

	//loads region/states according to selected country
	//called via jquery ajax
	function loadState()
	{
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$country=JFactory::getApplication()->input->get('country');
		//use helper file function
		$state=$jgiveFrontendHelper->getState($country);
		echo json_encode($state);
		jexit();
	}
	//loads city according to selected country
	//called via jquery ajax	
	function loadCity()
	{
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$country=JFactory::getApplication()->input->get('country');
		//use helper file function
		$city=$jgiveFrontendHelper->getCity($country);
		echo json_encode($city);
		jexit();
	}
}
?>
