<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport('joomla.application.component.model');
//load frontend donations model file as it is
JLoader::import( 'donations', JPATH_ROOT.DS.'components'.DS.'com_jgive'.DS.'models' );