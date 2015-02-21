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
jimport('joomla.application.component.controller');
class jgiveControllerEnding_camp extends jgiveController
{
	function  csvexport(){
		$model = $this->getModel("ending_camp");
		$CSVData = $model->getCsvexportData();
		$filename = "EndingCampReport_".date("Y-m-d");
		$params=JComponentHelper::getParams('com_jgive');
		$currency=$params->get('currency_symbol');
		$csvData = null;
		//$csvData.= "Item_id;Product Name;Store Name;Store Id;Sales Count;Amount;Created By;";

		$headColumn = array();
		$headColumn[0] = JText::_('COM_JGIVE_CAMPAIGN_DETAILS');
		$headColumn[1] = JText::_('COM_JGIVE_START_DATE');// 'Product Name';
		$headColumn[2] = JText::_('COM_JGIVE_END_DATE');
		$headColumn[3] = JText::_('COM_JGIVE_GOAL_AMOUNT');
		$headColumn[4] = JText::_('COM_JGIVE_AMOUNT_RECEIVED');
		$headColumn[5] = JText::_('COM_JGIVE_DONORS');
		$headColumn[7] = JText::_('COM_JGIVE_ID');

		$csvData .= implode(";",$headColumn);
		$csvData .= "\n";
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m-d") .".csv");
		header("Content-disposition: filename=".$filename.".csv");

		if(!empty($CSVData))
		{
			foreach($CSVData as $data) {
				$csvrow = array();
				$csvrow[0] = '"'.$data['title'].'"';
				$sdate = JFactory::getDate($data['start_date'])->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
				$csvrow[1] = '"'.$sdate.'"';
				$end_date = JFactory::getDate($data['end_date'])->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
				$csvrow[2] = '"'.$end_date.'"';
				$jgiveFrontendHelper=new jgiveFrontendHelper();
				//$goal_amount=$jgiveFrontendHelper->getFromattedPrice($data['goal_amount']);
				//$csvrow[3] = '"'.$goal_amount.'"';
				$csvrow[3] = '"'.$currency.' '.$data['goal_amount'].'"';
				$campaignHelper=new campaignHelper();
				$amounts=$campaignHelper->getCampaignAmounts($data['id']);
				//$amount_received=$jgiveFrontendHelper->getFromattedPrice($amounts['amount_received']);
				//$csvrow[4] = '"'.$amount_received.'"';
				$csvrow[4] = '"'.$currency.' '.$amounts['amount_received'].'"';
				$csvrow[5] = '"'.$data['donor_count'].'"';
				$csvrow[7] = '"'.$data['id'].'"';
				$csvData .= implode(";",$csvrow);
				$csvData .= "\n";
			}
		}
		ob_clean();
		echo    $csvData."\n";
		jexit();
		$link=JURI::base().substr(JRoute::_('index.php?option=com_jgive&view=ending_camp',false),strlen(JURI::base(true))+1);
		$this->setRedirect( $link);
	}
}
?>
