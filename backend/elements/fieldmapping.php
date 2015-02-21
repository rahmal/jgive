<?php
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidation');
$document=JFactory::getDocument();

//define directory seperator
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
//load techjoomla bootstrapper

include_once JPATH_ROOT.'/media/techjoomla_strapper/strapper.php';
TjAkeebaStrapper::bootstrap();
jimport('joomla.html.parameter.element');
jimport('joomla.form.formfield');
jimport( 'joomla.html.html.access' );
jimport( 'joomla.utilities.xmlelement' );
require_once(JPATH_SITE.DS.'libraries/joomla/form/fields/textarea.php');

class JFormFieldfieldmapping extends JFormFieldTextarea {
	var	$type = 'fieldmapping';

	var $_name = 'joomla_mapping';

	function getInput(){
		return $textarea=$this->fetchElement($this->name,$this->value, $this->element, $this->options['control']);
	}


	function fetchElement($name, $value, &$node, $control_name)
	{
		$rows = $node->attributes()->rows;
		$cols = $node->attributes()->cols;
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"' );
		// To render field which already saved in db
		$fieldvalue=trim($this->renderedfield());
		// for first time installation check value or textarea is empty
		if(($fieldvalue==''))
		{
			$fieldvalue='first_name=name,*'."\n";
			$fieldvalue.='address=address1'."\n";
			$fieldvalue.='address2=address2'."\n";
			$fieldvalue.='city=city'."\n";
			$fieldvalue.='zip=postal_code'."\n";
			$fieldvalue.='phone=phone'."\n";
			$fieldvalue.='website_address=website'."\n";
			$fieldvalue.='paypal_email=email,*'."\n";
		}
		$fieldavi='first_name=name,*'."\n";
		$fieldavi.='address=address1'."\n";
		$fieldavi.='address2=address2'."\n";
		$fieldavi.='city=city'."\n";
		$fieldavi.='zip=postal_code'."\n";
		$fieldavi.='phone=phone'."\n";
		$fieldavi.='website_address=website'."\n";
		$fieldavi.='paypal_email=email,*'."\n";

	$html=	'<textarea name="'.$control_name.$name.'" cols="'.$cols.'" rows="'.$rows.'" '.$class.' id="'.$control_name.$name.'" >'.$fieldvalue.'</textarea>';

	if(JVERSION<'3.0.0')
		$html.='<span style="float:left;">  '.JText::_('COM_JGIVE_FIELDS_JOOMLA').':</span>';
	else
		$html.='  '.JText::_('COM_JGIVE_FIELDS_JOOMLA').':';

	return $html.= '<textarea  cols="'.$cols.'" rows="'.$rows.'" '.$class.' disabled="disabled" >'.$fieldavi.'</textarea>';
	}
	function renderedfield()
	{
		$params=JComponentHelper::getParams('com_jgive');
		$mapping=trim($params->get('fieldmap'));
		$field_explode=explode('\n',$mapping);
		$fieldvalue='';
		if(isset($mapping)) // check value exist in array
			foreach($field_explode as $field)
				$fieldvalue.=$field."\n";

	return $fieldvalue;
	}
}

?>
