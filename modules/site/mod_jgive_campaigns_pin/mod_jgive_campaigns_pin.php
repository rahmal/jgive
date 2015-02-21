<?php
/**
* @package    jGive Campaigns
* @author     Techjoomla
* @copyright  Copyright 2012 - Techjoomla
* @license    http://www.gnu.org/licenses/gpl-3.0.html
**/

//no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

// Load helper File
require_once( dirname(__FILE__).DS.'helper.php' );
$modJGiveLayoutHelper=new modJGiveLayoutHelper();

$db=JFactory::getDBO();
//get Params
$campaigns_to_show=$params->get('campaigns_to_show');

$no_of_camp_show=$params->get('no_of_camp_show');
$orderby=$params->get('campaigns_sort_by');
$orderby_dir=$params->get('order_dir');
//$no_of_campaigns_in_column=$params->get('no_of_campaigns_in_column');
require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helper.php");
//get items ids
//@create jgiveFrontendHelper object
$jgiveFrontendHelper=new jgiveFrontendHelper();
//$singleCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=single');

$singleCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
//Campaigns to show in module


$result=$modJGiveLayoutHelper->getFeaturedCampaigns($no_of_camp_show,$orderby,$orderby_dir,$campaigns_to_show);

require(JModuleHelper::getLayoutPath('mod_jgive_campaigns_pin'));

?>

