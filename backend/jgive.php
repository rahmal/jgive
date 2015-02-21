<?php
/**
 * @version		1.6 jgive $
 * @package		jgive
 * @copyright	Copyright © 2013 - All rights reserved.
 * @license		GNU/GPL
 * @author		TechJoomla
 * @author mail	extensions@techjoomla.com
 * @website		http://techjoomla.com
 *
 *
 * @MVC architecture generated by MVC generator tool at http://www.alphaplug.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include dependancies
jimport('joomla.application.component.controller');

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

//load techjoomla bootstrapper
include_once JPATH_ROOT.'/media/techjoomla_strapper/strapper.php';
TjAkeebaStrapper::bootstrap();

//load
$document=JFactory::getDocument();
$document->addStyleSheet(JUri::root().'components/com_jgive/assets/css/jgive.css');//frontend css
$document->addStyleSheet(JUri::root().'administrator/components/com_jgive/assets/css/jgive.css');//backend css

//load jgive helper
$jgivehelperPath=JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'jgive.php';
if(!class_exists('JgiveHelper'))
{
  //require_once $path;
   JLoader::register('JgiveHelper', $jgivehelperPath );
   JLoader::load('JgiveHelper');
}

//load jgiveFontendHelper
$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helper.php';
if(!class_exists('jgiveFrontendHelper'))
{
  //require_once $path;
   JLoader::register('jgiveFrontendHelper', $helperPath );
   JLoader::load('jgiveFrontendHelper');
}

$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'campaign.php';
if(!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath );
	JLoader::load('campaignHelper');
}

$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php';
if(!class_exists('donationsHelper'))
{
	JLoader::register('donationsHelper', $helperPath );
	JLoader::load('donationsHelper');
}

$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'reports.php';
if(!class_exists('reportsHelper'))
{
	JLoader::register('reportsHelper', $helperPath );
	JLoader::load('reportsHelper');
}

$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'media.php';
if(!class_exists('jgivemediaHelper'))
{
	JLoader::register('jgivemediaHelper', $helperPath );
	JLoader::load('jgivemediaHelper');
}

//~ // Require the base controller
//~ require_once (JPATH_COMPONENT.DS.'controller.php');
//~ if( $controller = JRequest::getWord('controller'))
	//~ {
		//~ $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
		//~ if( file_exists($path))
			//~ require_once $path;
		//~ else
			//~ $controller = '';
	//~ }
//~ // Create the controller
//~ $classname    = 'jgiveController'.$controller;
//~ $controller   = new $classname( );
//~ // Perform the Request task
//~ $controller->execute(JFactory::getApplication()->input->get('task', null, 'default', 'cmd'));
//~ $controller->redirect();

// Perform the Request task

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');
// Execute the task.
$controller = JControllerLegacy::getInstance('jgive');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();