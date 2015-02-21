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
//load frontend order details view
ob_start();
include(JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'views'.DS.'donations'.DS.'tmpl'.DS.'details.php');
$html=ob_get_contents();
ob_end_clean();
echo $html;	
?>

