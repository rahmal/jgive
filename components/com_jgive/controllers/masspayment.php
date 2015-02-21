<?php
/**
 * @package	Jgive
 * @copyright Copyright (C) 2012 -2013 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
	
// no direct access
defined('_JEXEC') or die('Restricted access'); 
require_once( JPATH_COMPONENT.DS.'controller.php' );

jimport('joomla.application.component.controller');

class jgiveControllermasspayment extends JControllerLegacy
{
	
 	function performmasspay()
	{
		//get Params
		$params=JComponentHelper::getParams('com_jgive');
		$send_payments_to_owner=$params->get('send_payments_to_owner');
	
		if(!$send_payments_to_owner)
		{		
			$input=JFactory::getApplication()->input;
			$pkey=$input->get('pkey','');
			$params->get('private_key_cronjob');
			if($pkey!=$params->get('private_key_cronjob'))
			{
				echo JText::_( 'COM_JGIVE_SECRET_KEY_ERROR' );
				return false;
			}
			if($params->get('commission_fee')==0) 
			{
				echo '<b>'.JText::_( 'COM_JGIVE_COMMISSION_ZERO_ERROR' ).'</b>';
				return false;
			}
			$model	= $this->getModel('masspayment');
			$msg=$model->performmasspay();
			echo $msg;
		}
	}
	
}
