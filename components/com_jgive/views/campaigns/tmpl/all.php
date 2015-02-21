<?php
/**
* @version		1.0.0 jgive $
* @package		jgive
* @copyright	Copyright © 2012 - All rights reserved.
* @license		GNU/GPL
* @author		TechJoomla
* @author mail	extensions@techjoomla.com
* @website		http://techjoomla.com
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

//jomsocial toolbar
echo $this->jomsocailToolbarHtml;

if($this->params->get('layout_to_load')=='pin_layout')
{
	echo $this->loadTemplate('pin'); 
}
else
{
	echo $this->loadTemplate('blog'); 
}
?>
