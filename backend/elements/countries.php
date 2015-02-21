<?php
// no direct access
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
jimport('joomla.html.parameter.element');
 //require_once(JPATH_SITE . DS . 'libraries' . DS . 'joomla' . DS . 'html' . DS . 'parameter' . DS . 'element.php');
if(JVERSION>=1.6){
	jimport('joomla.form.formfield');
	class JFormFieldCountries extends JFormField {

		var	$type = 'Countries';

		function getInput(){
			return $this->fetchElement($this->name, $this->value, $this->element, $this->options['control']);
		}


	

	var	$_name = 'Countries';

	function fetchElement($name, $value, &$node, $control_name){

		$db = JFactory::getDBO();

		$query="SELECT country_id, country
		FROM #__tj_country
		ORDER BY country";
		$db->setQuery($query);
		$countries=$db->loadObjectList();

		$options = array();
		foreach($countries as $country){
			$options[] = JHtml::_('select.option',$country->country_id, $country->country);
		}

		if(JVERSION>=1.6) {
			$fieldName = $name;
		}
		else {
			$fieldName = $control_name.'['.$name.']';
		}
		return JHtml::_('select.genericlist', $options, $fieldName, 'class="inputbox required"', 'value', 'text', $value, $control_name.$name );

	}

	function fetchTooltip($label, $description, &$node, $control_name, $name){
		return NULL;
	}
}

}

