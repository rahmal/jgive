<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if(JVERSION>=3.0)
	JHtmlBehavior::framework();
else
	JHtml::_('behavior.mootools');

$document=JFactory::getDocument();

jimport('joomla.filter.output');
jimport( 'joomla.utilities.date');

$document->addStyleSheet(JUri::root().'components/com_jgive/assets/css/jgive.css');//frontend css

global $Itemid,$mainframe;
$user=JFactory::getUser();
//print_r($this->cwdonations);
if(JVERSION >= '1.6.0'){
	$js_key="Joomla.submitbutton=function(task){";
}
else{
	$js_key="function submitbutton(task){";
}
//Commented By Sneha

/*
$js_key.="
document.adminForm.action.value=task;
	if (task =='cancel')
	{";
		if(JVERSION >= '1.6.0')
			$js_key.="	Joomla.submitform(task);";
		else
			$js_key.="document.adminForm.submit();";
	$js_key.="
	}
}";
$document->addScriptDeclaration($js_key);
*/
//override active menu class to remove active class from other submenu
$menuCssOverrideJs="techjoomla.jQuery(document).ready(function(){
	techjoomla.jQuery('ul>li> a[href$=\"index.php?option=com_jgive&view=reports&layout=payouts\"]:last').removeClass('active');
});";
$document->addScriptDeclaration($menuCssOverrideJs);

?>
<?php if(JVERSION<3.0): ?>
<div class="techjoomla-bootstrap" >
<?php endif;?>
	<form action="index.php" method="post" name="adminForm"	id="adminForm">
		<?php
		// @ sice version 3.0 Jhtmlsidebar for menu
		if(JVERSION>=3.0):
			 if (!empty( $this->sidebar)) : ?>
				<div id="j-sidebar-container" class="span2">
					<?php echo $this->sidebar; ?>
				</div>
				<div id="j-main-container" class="span10">
			<?php else : ?>
				<div id="j-main-container">
			<?php endif;
		endif;
		?>
		<div class="com_jgive_float_left alert alert-info">
			<i><?php echo JText::_('COM_JGIVE_EXCLUDE_AMOUNT_DESC');?></i>
		</div>

		<div class="com_jgive_float_right">
			<table >
				<tr>
					<td class="center">
						<?php

						if(JVERSION<3.0):

						$campaignHelper=new campaignHelper();
						$campaign_type=$campaignHelper->filedToShowOrHide('campaign_type');

						if($campaign_type)
						{
						echo JHtml::_('select.genericlist', $this->campaign_type_filter_options, "filter_campaign_type", ' size="1"
						onchange="document.adminForm.submit();" name="filter_campaign_type"',"value", "text", $this->lists['filter_campaign_type']);
						}

						 echo JHtml::_('select.genericlist', $this->cat_options, "filter_campaign_cat", 'class="" size="1"
						onchange="document.adminForm.submit();" name="filter_campaign_cat"',"value", "text",$this->lists['filter_campaign_cat']);



						echo JHtml::_('select.genericlist', $this->filter_campaign_options, "filter_campaign", 'class="" size="1"
						onchange="document.adminForm.submit();" name="filter_campaign"',"value", "text", $this->lists['filter_campaign']);

						endif;
						?>
						<?php if(JVERSION >= 3.0 ){ ?>
						<div class="btn-group pull-right hidden-phone">
							<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
							<?php echo $this->pagination->getLimitBox(); ?>
						</div>
						<?php } ?>
					</td>
				</tr>
			</table>
		</div>

		<div class="com_jgive_clear_both"></div>

		<?php
		if(empty($this->cwdonations))
		{?>
			<div class="span4">
				<?php	echo JText::_('COM_JGIVE_NO_DATA');?>
			</div>
			<input type="hidden" name="option" value="com_jgive" />
			<input type="hidden" name="view" value="reports" />
			<input type="hidden" name="layout" value="default" />
			<input type="hidden" id="controller" name="controller" value="reports" />
			<input type="hidden" id="task" name="task" value="" />
			<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign'];?>" />
			<input type="hidden" name="defaltevent_cat" value="<?php echo $this->lists['filter_campaign_cat'];?>" />
			<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_type'];?>" />

		</form>
			<?php
			return;
		}
		?>
	<?php
	if(JVERSION >= 3.0 ){
		$tblclass='table table-striped';
	}
	else{
		$tblclass='adminlist table table-striped table-bordered';
	}
	?>

		<table class="<?php echo $tblclass; ?>" >
		<thead>
			<tr>
				<th class="center"><?php echo JText::_('COM_JGIVE_NUMBER');?></th>

				<th class="center"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_NAME','id', $this->lists['order_Dir'], $this->lists['order']); ?></th>

				<th class="center"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_CAMPAIGN_USER','creator_id', $this->lists['order_Dir'], $this->lists['order']); ?></th>

				<th class="center"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_NOF_DONATIONS','donations_count', $this->lists['order_Dir'], $this->lists['order']); ?></th>

				<th class="center">
					<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_TOTAL_AMOUNT_DONATION','total_amount', $this->lists['order_Dir'], $this->lists['order']); ?>
					<br/>(A)
				</th>

				<th class="center">
					<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_TOTAL_AMOUNT_EXCLUDE','exclude_amount', $this->lists['order_Dir'], $this->lists['order']); ?>
					<br/>(B)
				</th>

				<th class="center">
					<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_TOTAL_AMOUNT_COMMISSION','total_commission', $this->lists['order_Dir'], $this->lists['order']); ?>
					<br/>(C)
				</th>

				<th class="center">
					<?php echo JText::_('COM_JGIVE_TOTAL_AMOUNT_TOBE_PAID');?>
					<br/>=(A-B-C)
				</th>
			</tr>
		</thead>
			<?php
			$i=0;
			$total_donations=$total_amount=$total_commission=$total_amount_2bpaid=$total_exclude=0;
			foreach($this->cwdonations as $cwdonation)
			{
				$total_donations=$total_donations+$cwdonation->donations_count;
				$total_amount=$total_amount+$cwdonation->total_amount;
				$total_exclude=$total_exclude+$cwdonation->exclude_amount;
				$total_commission=$total_commission+$cwdonation->total_commission;
				$total_amount_2bpaid=$total_amount_2bpaid+($cwdonation->total_amount-$cwdonation->total_commission-$cwdonation->exclude_amount);


				$link=JRoute::_(JUri::base().'index.php?option=com_jgive&view=donations&cid='.$cwdonation->cid);
				?>

				<tr>
					<td class="center"><?php echo $i+1;?></td>
					<td class="center"><a href="<?php echo $link;?>" title="<?php echo JText::_('COM_JGIVE_NAME_TOOLTIP');?>"><?php echo ucfirst($cwdonation->title);?></a></td>
					<td class="center">
						<?php
						echo $cwdonation->first_name.' '.$cwdonation->last_name;
						echo "<br/>";
						echo $cwdonation->paypal_email;
						echo "<br/>";
						$ulink=JRoute::_(JUri::base().'index.php?option=com_users&task=user.edit&id='.$cwdonation->creator_id);
						?>
						<a href="<?php echo $ulink;?>" ><?php echo $cwdonation->username;?></a>
					</td>
					<td class="center"><?php echo $cwdonation->donations_count ?></td>
					<td class="center"><?php
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cwdonation->total_amount);
					?></td>
					<td class="center"><?php
							echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cwdonation->exclude_amount);
					?></td>
					<td class="center"><?php
							echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cwdonation->total_commission);
					?></td>

					<td class="center">
						<?php
						$subtotal_amount_2bpaid=$cwdonation->total_amount-$cwdonation->total_commission-$cwdonation->exclude_amount;
						echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($subtotal_amount_2bpaid);
						?>
					</td>
				</tr>
				<?php
				$i++;
			}
			?>

			<tr>
				<td class="center" colspan="3" class="com_jgive_align_right"><b><?php echo JText::_('COM_JGIVE_TOTAL');?></b></td>
				<td class="center"><b><?php echo number_format($total_donations, 0, '', '');?></b></td>
				<td class="center"><b><?php
					$jgiveFrontendHelper=new jgiveFrontendHelper();
					echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($total_amount);
				?></b>
				</td>
				<td class="center"><b><?php
					echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($total_exclude);
				 ?></b>
				 </td>
				<td class="center"><b><?php
						echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($total_commission);
				?></b></td>
				<td class="center"><b><?php
					echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($total_amount_2bpaid);
				?></b></td>
			</tr>

			<tr rowspan="3">
				<td class="com_jgive_align_right" colspan="7"></td>
				<td></td>
			</tr>



			<tr>
				<td class="com_jgive_align_right" colspan="7"><b><?php echo JText::_( 'COM_JGIVE_SUBTOTAL'); ?></b></td>
				<td class="center">
					<b>
					<?php
					$user=JFactory::getUser();
					$jgiveFrontendHelper=new jgiveFrontendHelper();
					echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($total_amount_2bpaid);
					?>
					</b>
				</td>
			</tr>

			<tr>
				<td class="com_jgive_align_right" colspan="7"><b><?php echo JText::_( 'COM_JGIVE_PAID'); ?></b></td>
				<td class="center">
					<b>
					<?php
					$reportsHelper=new reportsHelper();
					$user=JFactory::getuser();
					$total_paid_out_amount=$reportsHelper->getTotalPaidOutAmount();

					$jgiveFrontendHelper=new jgiveFrontendHelper();
					echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($total_paid_out_amount);
					?>
					</b>
				</td>
			</tr>

			<tr>
				<td class="com_jgive_align_right" colspan="7"><b><?php echo JText::_( 'COM_JGIVE_BALANCE'); ?></b></td>
				<td class="center">
					<b>
					<?php
					$total_remaining_amount_2bpaid=$total_amount_2bpaid-$total_paid_out_amount;
					$balanceamt=number_format($total_remaining_amount_2bpaid, 2, '.', '');
					if($balanceamt=='-0.00')
					{
						$balanceamt=0;
						$jgiveFrontendHelper=new jgiveFrontendHelper();
						echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($balanceamt);
					}
					else
					{
						$jgiveFrontendHelper=new jgiveFrontendHelper();
						echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($balanceamt);
					}
					?>
					</b>
				</td>
			</tr>


			<tr>
				<?php
					if(JVERSION<3.0)
						$class_pagination='pager';
					else
						$class_pagination='';
				?>
				<td class="center" colspan="8" class="<?php echo $class_pagination; ?> com_jgive_align_center"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>

		</table>

		<input type="hidden" name="option" value="com_jgive" />
		<input type="hidden" name="view" value="reports" />
		<input type="hidden" name="layout" value="default" />
		<input type="hidden" id="task" name="task" value="" />
		<input type="hidden" id="controller" name="controller" value="reports" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign'];?>" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_type'];?>" />
		<input type="hidden" name="defaltevent_cat" value="<?php echo $this->lists['filter_campaign_cat'];?>" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />


	</form>
<?php if(JVERSION<3.0): ?>
</div>
<?php endif;?>
