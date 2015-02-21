<?php
defined('JPATH_BASE') or die();
jimport('joomla.html.parameter.element');
jimport('joomla.html.html');
jimport('joomla.form.formfield');
class JFormFieldHeader extends JFormField
{
	var	$type='Header';
	function getInput()
	{
		$document=JFactory::getDocument();
		$document->addStyleSheet(JUri::base().'components/com_jgive/assets/css/jgive.css');
		$return='
		<div class="jbolo_div_outer">
			<div class="jbolo_div_inner">
				'.JText::_($this->value).'
			</div>
		</div>';
		return $return;
	}
}
?>
