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
$document=JFactory::getDocument();
$jgiveFrontendHelper=new jgiveFrontendHelper();
if(JVERSION>=3.0)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');
}

//////////////
$url="index.php?option=com_jgive&controller=donations&task=gethtml&processor=";
$ajax="techjoomla.jQuery(document).ready(function()
{
	techjoomla.jQuery('#gateways').change(function()
	{
		var url1='".$url."'+document.getElementById('gateways').value;
		techjoomla.jQuery('#html-container').empty().append(
		'<div class=\"com_jgive_ajax_loading\"><div class=\"com_jgive_ajax_loading_text\">".JText::_('COM_JGIVE_LOADING_PAYMET_FORM_MSG')."</div><img class=\"com_jgive_ajax_loading_img\" src=\"".JUri::base()."components/com_jgive/assets/images/ajax.gif\"></div>');
	 	techjoomla.jQuery.ajax(
	 	{
			url:url1,
			type:'GET',
			dataType:'html',
			success:function(response){
				techjoomla.jQuery('#html-container').removeClass('ajax-loading').html(response);
			}
		});
	});
});";
$document->addScriptDeclaration($ajax);
/////////////////

$cdata=$this->cdata;

?>
<div class="techjoomla-bootstrap">

	<h2 class="componentheading">
		<?php echo JText::_('COM_JGIVE_MAKE_PAYMENT_CONFIRM');?>
	</h2>

	<hr/>

	<h4>
		<?php echo JText::_('COM_JGIVE_CAMPAIGN_DETAILS');?>
	</h4>


	<div style="width:100%;">
		<table class="table table-bordered table-striped">
			<tr>
				<td style="width:50%;">
					<?php
						echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_CAMPAIGN_YOU_ARE_DONTAING_TO') : JText::_('COM_JGIVE_CAMPAIGN_YOU_ARE_INVESTING_IN'));
						$itemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=my');//used below
					?>
				</td>
				<td style="width:50%;">
					<a target='_blank' href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$cdata['campaign']->id.'&Itemid='.$itemid),strlen(JUri::base(true))+1);?>">
						<?php echo $cdata['campaign']->title;?>
					</a>
				</td>
			</tr>

			<tr>
				<td style="width:50%;"><?php echo JText::_('COM_JGIVE_GOAL_AMOUNT');?></td>
				<td style="width:50%;"><?php echo $cdata['campaign']->goal_amount.' '.$this->currency_code;?>
				</td>
			</tr>

			<tr>
				<td style="width:50%;">
					<?php
						echo JText::_('COM_JGIVE_AMOUNT_RECEIVED');
					?>
				</td>
				<td style="width:50%;">
					<?php
						echo $cdata['campaign']->amount_received.' '.$this->currency_code;
					?>
				</td>
			</tr>
			<tr>
				<td style="width:50%;">
					<?php
						echo JText::_('COM_JGIVE_REMAINING_AMOUNT');
					?>
				</td>
				<td style="width:50%;">
					<?php
						if($cdata['campaign']->amount_received>$cdata['campaign']->goal_amount){
							echo JText::_('COM_JGIVE_NA');
						}
						else{
							echo $cdata['campaign']->remaining_amount.' '.$this->currency_code;
						}
					?>
				</td>
			</tr>
		</table>
	</div>

	<div style="width:100%;">
		<h4>
			<?php
			echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_CAMPAIGN_DONATION_DETAILS') : JText::_('COM_JGIVE_CAMPAIGN_INVESTMENT_DETAILS'));
			?>
		</h4>

		<table class="table table-bordered table-striped">

			<tr>
				<td style="width:50%;">
					<?php
						echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_AMOUNT_YOU_ARE_DONATING') : JText::_('COM_JGIVE_AMOUNT_YOU_ARE_INVESTING'));
					?>
				</td>
				<td style="width:50%;">
					<?php
						echo $this->session->get('JGIVE_donation_amount').' '.$this->currency_code;
					?>
				</td>
			</tr>
			<tr>
				<td style="width:50%;">
					<?php
						echo JText::_('COM_JGIVE_SEL_GATEWAY');
					?>
				</td>
				<td style="width:50%;">
					<?php
						$select[] =new stdclass();
						$select[0]->id=0;
						$select[0]->name=JText::_('COM_JGIVE_SEL_GATEWAY');
						$gateways=array_merge($select,$this->gateways);
						$gateways=array_filter($gateways);
						if(empty($this->gateways))
							echo JText::_('COM_JGIVE_NO_PAYMENT_GATEWAY');
						else
						{
							$pg_list = JHtml::_('select.genericlist', $gateways, 'gateways', 'class="inputbox" id="gateways"', 'id', 'name');
							echo $pg_list;
						}
					?>
				</td>
			</tr>
		</table>
	</div>

	<div id="html-container"></div>

</div>
