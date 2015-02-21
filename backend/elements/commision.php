<?php
// no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidation');
$document=JFactory::getDocument();
//load techjoomla bootstrapper
//include_once JPATH_ROOT.'/media/techjoomla_strapper/strapper.php';
//AkeebaStrapper::bootstrap();
jimport('joomla.html.parameter.element');
?>

<script type="text/javascript">

	/*add clone script*/
	function addClone(rId,rClass)
	{
		var num=techjoomla.jQuery('.'+rClass).length;
		var removeButton="<div class='com_jgive_remove_button' style='float:right; margin-right:30% ' >";
		removeButton+="<button class='btn btn-mini' type='button' id='remove"+num+"'";
		removeButton+="onclick=\"removeClone('jgive_container"+num+"','jgive_container');\" title=\"<?php echo JText::_('COM_JGIVE_REMOVE_TOOLTIP');?>\" >";
		removeButton+="<i class=\"icon-minus-sign\"></i></button>";
		removeButton+="</div>";
		var newElem=techjoomla.jQuery('#'+rId).clone().attr('id',rId+num);

		techjoomla.jQuery(newElem).children('.com_jgive_repeating_block').children('.control-group').children('.controls').children('.input-prepend,.input-append').children().each(function()
		{

			var kid=techjoomla.jQuery(this);
			if(kid.attr('id')!=undefined)
			{
				var idN=kid.attr('id');
				kid.attr('id',idN+num).attr('id',idN+num);
				kid.attr('value','');
			}
			kid.attr('value','');

			//for joomla 3.0 change select element style
			var s = kid.attr('id');
			if(s.indexOf("jformusergroup_chzn"))
			{
				kid.attr('style', "display: block;");
			}
			else
			{
				kid.attr('style', "display: none;");
			}
		});

		techjoomla.jQuery('.'+rClass+':last').after(newElem);
		techjoomla.jQuery('div.'+rClass +' :last').append(removeButton);
	}
	/* remove clone script */
	function removeClone(rId,rClass,ids){
		if(ids==undefined)
			techjoomla.jQuery('#'+rId).remove();
		else
			techjoomla.jQuery('#'+'jgive_container'+ids).remove();
	}

</script>

<?php
if(JVERSION>=1.6){
	jimport('joomla.form.formfield');
	jimport( 'joomla.html.html.access' );
	class JFormFieldCommision extends JFormField {

		var	$type = 'Commision';


	function getInput(){

			if(JVERSION>=3.0)
			{
				$jgive_icon_plus="icon-plus-2 ";
			}
			else
			{ // for joomla3.0
				$jgive_icon_plus="icon-plus-sign ";
			}


			$html='';
			$html.='<div class="techjoomla-bootstrap" >
						<label  style="color: #493737;font-size: 13pt" class="control-label" for="give_back_value" title="'.JText::_('COM_JGIVE_GIVE_USERGROUPWISE_COM_TOOLTIP').'">
												'.JText::_('').'
						</label>
					</div>
					';
			$params=JComponentHelper::getParams('com_jgive');
			$group_info=$params->get('usergroup');
			if(isset($group_info))//for edit - recreate giveback blocks
			{
				$count=count($group_info);
				$j=0;

				for($i=0;$i<$count;$i=$i+3)
				{
					if(!empty($group_info[$i]))
					$html.='
							<div class="techjoomla-bootstrap">
								<div id="jgive_container'.$j.'" class="jgive_container" >
									<div class="com_jgive_repeating_block">
										<div class="control-group">
											<label class="control-label" for="give_back_value" title="'.JText::_('COM_JGIVE_GIVE_USERGROUP_TOOLTIP').'">
												'.JText::_('COM_JGIVE_GIVE_USERGROUP_VALUE').'
											</label>
											<div class="controls chzn-done"">
													'.$this->fetchElement($this->name, $group_info[$i], $this->element, $this->options['control']).'
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="give_back_details" title="'.JText::_('COM_JGIVE_GIVE_DONATE_PERCENT_TOOLTIP').'">
												'.JText::_('COM_JGIVE_GIVE_DONATE_PERCENT').'
											</label>
											<div class="controls">
												'.$this->fetchDonation($this->name, $group_info[$i+1], $this->element, $this->options['control']).'
											</div>
										</div>
										<div class="control-group">
											<label class="control-label" for="give_back_details" title="'.JText::_('COM_JGIVE_GIVE_INVEST_PERCENT_TOOLTIP').'">
												'.JText::_('COM_JGIVE_GIVE_INVEST_PERCENT').'
											</label>
											<div class="controls">
												'.$this->fetchInvest($this->name, $group_info[$i+2], $this->element, $this->options['control']).'
											</div>
										</div>
									</div>
									<div class="com_jgive_remove_button" style="float:right; margin-right:30% ">
											<button class="btn btn-mini" type="button" id="remove'.$j.'"
												onclick="removeClone(\'jgive_container\',\'jgive_container\','.$j.');" title="'.JText::_('COM_JGIVE_REMOVE_TOOLTIP').'" >
												<i class="icon-minus-sign"></i>
											</button>
										</div>
							<div>&nbsp;</div>
								</div>
							</div>';
						$j++;
				}
			}
		?>

		<?php
		// Fields
		$html.='
				<div class="techjoomla-bootstrap">
					<div id="jgive_container" class="jgive_container" >
						<div class="com_jgive_repeating_block" >
							<div class="control-group" >
								<label class="control-label" for="give_back_value" title="'.JText::_('COM_JGIVE_GIVE_USERGROUP_TOOLTIP').'">
									'.JText::_('COM_JGIVE_GIVE_USERGROUP_VALUE').'
								</label>
								<div class="controls">
									<div class="input-prepend input-append chzn-done"">
										'.$this->fetchElement($this->name, '', $this->element, $this->options['control']).'
									</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="give_back_details" title="'.JText::_('COM_JGIVE_GIVE_DONATE_PERCENT_TOOLTIP').'">
									'.JText::_('COM_JGIVE_GIVE_DONATE_PERCENT').'
								</label>
								<div class="controls">
									'.$this->fetchDonation($this->name,$this->value, $this->element, $this->options['control']).'
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="give_back_details" title="'.JText::_('COM_JGIVE_GIVE_INVEST_PERCENT_TOOLTIP').'">
									'.JText::_('COM_JGIVE_GIVE_INVEST_PERCENT').'
								</label>
								<div class="controls">
									'.$this->fetchInvest($this->name, $this->value, $this->element, $this->options['control']).'
								</div>
							</div>
						</div>
						<div>&nbsp;</div>
					</div>';
			$html.='<div class="com_jgive_add_button" style="float:right ;margin-right:30%">
						<button class="btn btn-mini" type="button" id="addbtn"
							onclick="addClone(\'jgive_container\',\'jgive_container\');"
								title="'.JText::_('COM_JGIVE_ADD_MORE_TOOLTIP').'">
							<i class="'.$jgive_icon_plus.'"></i>
						</button>
					</div>
				</div>';
		return $html;

		}


	var	$_name = 'Commision';
	function fetchElement($fieldName, $value, &$node, $control_name){
		$usergrp='';
		$usergrp=JHtml::_('access.usergroup',$fieldName.'[]', $value, 'class="chzn-done"');
		return $usergrp=JHtml::_('access.usergroup',$fieldName.'[]', $value, '');
	}

	function fetchDonation($fieldName, $value, &$node, $control_name){
		$donate_field='';
		return $donate_field=str_replace('Array','',$donate_field.='<input type="text" class="" name="'.$fieldName.'[]'.'"  value="'.$value.'" placeholder="Donate %" "/>');
	}

	function fetchInvest($fieldName, $value, &$node, $control_name){
		$invest_field='';
		return $invest_field=str_replace('Array','',$invest_field.='<input type="text" class="" name="'.$fieldName.'[]'.'" value="'.$value.'" placeholder="Invest %""/>');
	}


}
}

