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

defined('_JEXEC') or die('Restricted access');
//require_once( JPATH_COMPONENT.DS.'views'.DS.'config'.DS.'view.html.php' );

jimport('joomla.application.component.controller');

class jgiveControllerReports extends jgiveController
{

	function add()
	{
		$redirect=JRoute::_('index.php?option=com_jgive&view=reports&layout=edit_payout',false);
		$this->setRedirect($redirect);
	}

	function csvexport(){
		$model = $this->getModel("reports");
		$CSVData = $model->getCsvexportData();
		$filename = JText::_('COM_JGIVE_CAMPAIGNS_WISE_REPORTS').date("Y-m-d");
		$params=JComponentHelper::getParams('com_jgive');
		$currency=$params->get('currency_symbol');
		$csvData = null;
		//$csvData.= "Item_id;Product Name;Store Name;Store Id;Sales Count;Amount;Created By;";
		//echo count($CSVData);die;
		$headColumn = array();
		$headColumn[0] = JText::_('COM_JGIVE_NAME');// 'Product Name';
		$headColumn[1] = JText::_('COM_JGIVE_CAMPAIGN_USER');
		$headColumn[2] = JText::_('COM_JGIVE_NOF_DONATIONS');
		$headColumn[3] = JText::_('COM_JGIVE_TOTAL_AMOUNT_DONATION');
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
				$csvrow[1] = '"'.$data['first_name'].' '.$data['last_name'].' '.$data['paypal_email'].' '.$data['username'].'"';
				$csvrow[2] = '"'.$data['donations_count'].'"';
				$csvrow[3] = '"'.$currency.' '.$data['total_amount'].'"';
				//$csvrow[3] = '"'.$data['total_amount'].$CSVData['currency_code'].'"';

				$csvData .= implode(";",$csvrow);
				$csvData .= "\n";
			}
		}
		ob_clean();
		echo    $csvData."\n";
		jexit();
		$link=JURI::base().substr(JRoute::_('index.php??option=com_jgive&view=reports&layout=default',false),strlen(JURI::base(true))+1);
		$this->setRedirect( $link);
	}
	function csvexportpayouts(){
		$model = $this->getModel("reports");
		$CSVData = $model->getCsvexportData();
		$filename = JText::_('COM_JGIVE_PAYOUT_REPORTS').date("Y-m-d");
		$params=JComponentHelper::getParams('com_jgive');
		$currency=$params->get('currency_symbol');
		$csvData = null;
		//$csvData.= "Item_id;Product Name;Store Name;Store Id;Sales Count;Amount;Created By;";

		$headColumn = array();
		$headColumn[0] = JText::_('COM_JGIVE_NUMBER');// 'Product Name';
		$headColumn[1] = JText::_('COM_JGIVE_PAYOUT_ID');
		$headColumn[2] = JText::_('COM_JGIVE_PAYEE_NAME');
		$headColumn[3] = JText::_('COM_JGIVE_TRANSACTION_ID');
		$headColumn[4] = JText::_('COM_JGIVE_PAYOUT_DATE');
		$headColumn[5] = JText::_('COM_JGIVE_PAYMENT_STATUS');
		$headColumn[6] = JText::_('COM_JGIVE_PAYOUT_AMOUNT');

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
				$csvrow[1] = '"'.$data['id'].'"';
				$csvrow[2] = '"'.$data['payee_name'].'"';
				$csvrow[3] = '"'.$data['transaction_id'].'"';
				if(JVERSION<'1.6.0')
					$date = JHtml::_( 'date', $payout->date, '%Y/%m/%d');
				else
					$date = JHtml::_( 'date', $payout->date, "d-m-Y");
				$csvrow[4] = '"'.$date.'"';
				if($data['status'])
					$status = JText::_('COM_JGIVE_PAID');
				else
					$status = JText::_('COM_JGIVE_NOT_PAID');
				$csvrow[5] = '"'.$status.'"';
				//$jgiveFrontendHelper=new jgiveFrontendHelper();
				//$amount=$jgiveFrontendHelper->getFromattedPrice($payout->amount);
				$csvrow[6] = '"'.$currency.' '.$data['amount'].'"';

				$csvData .= implode(";",$csvrow);
				$csvData .= "\n";
			}
		}
		ob_clean();
		echo    $csvData."\n";
		jexit();
		$link=JURI::base().substr(JRoute::_('index.php??option=com_jgive&view=reports&layout=default',false),strlen(JURI::base(true))+1);
		$this->setRedirect( $link);
	}

}?>
