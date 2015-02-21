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
class jgiveControllerCampaigns extends jgiveController
{
	
	//create New Campaign
	function addNew(){
			$redirect=JRoute::_('index.php?option=com_jgive&view=campaign&layout=create',false);
			$this->setRedirect($redirect,$msg);
	}
}
?>
