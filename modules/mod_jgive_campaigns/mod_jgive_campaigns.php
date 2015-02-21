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
$db=JFactory::getDBO();
//get Params
$orderby=$params->get('campaigns_sort_by');
$orderby_dir=$params->get('order_dir');
$count=$params->get('no_of_camp_show');
$image=$params->get('image');
//@model helper object
$modJGiveHelper=new modJGiveHelper();
$featured_camp=$params->get('featured_camp');

//for group campaign module
$module_for=$params->get('module_for');

$groupid='';
$group_page=0;
if($module_for=='js_group_camp')
{
	//get js group id
	$input=JFactory::getApplication()->input;
	if($input->get('task')=='viewgroup')
	{
		$group_page=1;
		$groupid=$input->get('groupid','','INT');
	}
	else //not js group page
	{
		return;
	}
}
	//Sort by Remaing Amount
	if($params->get('campaigns_sort_by')=='amount_remaining' )
	{
			//get Campaigns data
			$result=$modJGiveHelper->getData($featured_camp,$group_page,$groupid);
			foreach($result as $key=>$d)
			{
				//get Amount for each campaigns
				$amounts=$modJGiveHelper->getCampaignAmounts($d->id,$image);
				$d->path=$amounts['path'];
			}

	}//other Sort
	else
	{		//get query for Campaigns Data
			$where=$modJGiveHelper->getwhere($orderby,$orderby_dir,$count,$image,$featured_camp,$group_page,$groupid);
			$db->setQuery($where);
			$result=$db->loadobjectlist();
			foreach($result as $key=>$d)
			{
				//get Amount for each campaigns
				$amounts=$modJGiveHelper->getCampaignAmounts($d->id,$image);
				$d->path=$amounts['path'];
			}
	}
	//Call default.php

require(JModuleHelper::getLayoutPath('mod_jgive_campaigns'));

?>

