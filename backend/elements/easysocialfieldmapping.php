<?php
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidation');
$document=JFactory::getDocument();
//load techjoomla bootstrapper

include_once JPATH_ROOT.'/media/techjoomla_strapper/strapper.php';
TjAkeebaStrapper::bootstrap();
jimport('joomla.html.parameter.element');
jimport('joomla.form.formfield');
jimport( 'joomla.html.html.access' );
jimport( 'joomla.utilities.xmlelement' );
require_once(JPATH_SITE.DS.'libraries/joomla/form/fields/textarea.php');
class JFormFieldeasysocialfieldmapping extends JFormFieldTextarea {
	var	$type = 'easysocialfieldmapping';

	function getInput(){
		return $textarea=$this->fetchElement($this->name,$this->value, $this->element, $this->options['control']);
	}

	var $_name = 'cb_fieldmap';
	function fetchElement($name, $value, &$node, $control_name)
	{
		//require_once(JPATH_SITE.DS.'libraries/joomla/html/parameter/element/textarea.php');
		$rows = $node->attributes()->rows;
		$cols = $node->attributes()->cols;
		$class = ( $node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"' );
		// To render field which already saved in db
		$fieldvalue=trim($this->renderedfield());
		// for first time installation check value or textarea is empty
		if(($fieldvalue==''))
		{
			$fieldvalue='first_name=joomla_fullname'."\n";
			$fieldvalue='last_name=joomla_fullname'."\n";
			$fieldvalue.='paypal_email=email'."\n";
		}
		$fieldavi='first_name=joomla_fullname'."\n";
		$fieldavi.='last_name=joomla_fullname'."\n";
		$fieldavi.='address=address'."\n";
		$fieldavi.='phone=textbox'."\n";
		$fieldavi.='website_address=url'."\n";
		$fieldavi.='paypal_email=email'."\n";
		

	$html= '<textarea name="'.$control_name.$name.'" cols="'.$cols.'" rows="'.$rows.'" '.$class.' id="'.$control_name.$name.'" >'.$fieldvalue.'</textarea>';

	if(JVERSION<'3.0.0')
		$html.='<span style="float:left;">  '.JText::_('COM_JGIVE_FIELDS_CB').':</span>';
	else
		$html.='  '.JText::_('COM_JGIVE_FIELDS_EASYSOCIAL').':';

	return $html.= '<textarea  cols="'.$cols.'" rows="'.$rows.'" '.$class.' disabled="disabled" >'.$fieldavi.'</textarea>';

	}
	function renderedfield()
	{
		$params=JComponentHelper::getParams('com_jgive');
		$mapping=trim($params->get('easysocial_fieldmap'));
		$field_explode=explode('\n',$mapping);
		$fieldvalue='';
		if(isset($mapping)) // check value exist in array
			foreach($field_explode as $field)
				$fieldvalue.=$field."\n";
		return $fieldvalue;
	}
}
?>
