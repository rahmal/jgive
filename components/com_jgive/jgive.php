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
defined( '_JEXEC' ) or die( ';)' );
// Require the base controller
// defibe DIRECTORY_SEPARATOR if not mostly for joomla 3.0
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}


require_once( JPATH_COMPONENT.DS.'controller.php' );

$params=JComponentHelper::getParams('com_jgive');



//load techjoomla bootstrapper
include_once JPATH_ROOT.'/media/techjoomla_strapper/strapper.php';
TjAkeebaStrapper::bootstrap();
$document=JFactory::getDocument();
// Load CSS & JS resources.
if (JVERSION > '3.0')
{
	$document->addScript(JUri::root().'media/techjoomla_strapper/js/namespace.js');
	// Load jQuery.
	JHtml::_('jquery.framework');

	$load_bootstrap=$params->get('load_bootstrap');
	if ($load_bootstrap)
	{
		// Load bootstrap CSS and JS.
		JHtml::_('bootstrap.loadcss');
		JHtml::_('bootstrap.framework');
	}

}

//load css
$document=JFactory::getDocument();
$document->addStyleSheet(JUri::root().'components/com_jgive/assets/css/jgive.css');//frontend css

$helperPath=dirname(__FILE__).DS.'helper.php';

if(!class_exists('jgiveFrontendHelper'))
{
  //require_once $path;
   JLoader::register('jgiveFrontendHelper', $helperPath );
   JLoader::load('jgiveFrontendHelper');
}


$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php';
if(!class_exists('donationsHelper'))
{
  //require_once $path;
   JLoader::register('donationsHelper', $helperPath );
   JLoader::load('donationsHelper');
}
$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php';
if(!class_exists('donationsHelper'))
{
  //require_once $path;
   JLoader::register('donationsHelper', $helperPath );
   JLoader::load('donationsHelper');
}
$integrationsHelperPath=dirname(__FILE__).DS.'helpers'.DS.'integrations.php';
//load integrations helper file
if(!class_exists('integrationsHelper'))
{
  //require_once $path;
   JLoader::register('integrationsHelper', $integrationsHelperPath );
   JLoader::load('integrationsHelper');
}

$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'reports.php';
if(!class_exists('reportsHelper'))
{
	JLoader::register('reportsHelper', $helperPath );
	JLoader::load('reportsHelper');
}
$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'campaign.php';
if(!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath );
	JLoader::load('campaignHelper');
}

//load media helper
$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'media.php';
if(!class_exists('jgivemediaHelper'))
{
	JLoader::register('jgivemediaHelper', $helperPath );
	JLoader::load('jgivemediaHelper');
}

// Require specific controller if requested
if( $controller = JFactory::getApplication()->input->get('controller'))
{
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if( file_exists($path))
	{
		require $path;
	} else
	{
		$controller = '';
	}
}

// Create the controller
$classname='jgiveController'.$controller;
$controller=new $classname( );
$controller->execute( JRequest::getWord( 'task' ) );
// Redirect if set by the controller
$controller->redirect();
?>
