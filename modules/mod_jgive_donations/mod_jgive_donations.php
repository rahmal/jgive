
<?php
/**
* @package    jGive Campaigns
* @author     Techjoomla
* @copyright  Copyright 2012 - Techjoomla
* @license    http://www.gnu.org/licenses/gpl-3.0.html
**/

//no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
	// Load helper File
	require_once( dirname(__FILE__).DS.'helper.php' );
	$modJGiveDonationHelper=new modJGiveDonationHelper();
	$module_for=$params->get('module_for');
	$no_of_record_show=$params->get('no_of_record_show');
	$userid=JFactory::getUser()->id;
	switch($module_for)
	{
		case 'last_donations': 
			$result=$modJGiveDonationHelper->lastDonations($no_of_record_show);
		break;

		case 'top_donations': 
			$result=$modJGiveDonationHelper->topDonations($no_of_record_show);
		break;

		default:
			$result=$modJGiveDonationHelper->myDonations($no_of_record_show);
		break;
	}
		//echo "<pre>"; print_r($result);echo "</pre>"; 
		require(JModuleHelper::getLayoutPath('mod_jgive_donations'));

?>

