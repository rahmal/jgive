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

$user=JFactory::getUser();

$js_joomla15 ='function submitbutton(prm)
{
	if(prm=="add")
	{
		window.location = "index.php?option=com_jgive&view=reports&task=save&layout=edit_payout";
	}
	else
		window.location = "index.php?option=com_jgive";

}';
$js_joomla16 ="Joomla.submitbutton = function(prm)
{
	if(prm=='add')
	{
		
		window.location = 'index.php?option=com_jgive&view=reports&task=save&layout=edit_payout';
	}
	else if(prm=='deletePayouts')
	{
		document.getElementById('controller').value='report';
		document.getElementById('task').value='deletePayouts';
		document.adminForm.submit();
	}
	else
		window.location = 'index.php?option=com_jgive';
}";
/*
if(JVERSION >= '1.6.0')
	$document->addScriptDeclaration($js_joomla16);
else
	$document->addScriptDeclaration($js_joomla15);
*/
//override active menu class to remove active class from other submenu
$menuCssOverrideJs="techjoomla.jQuery(document).ready(function(){
	techjoomla.jQuery('ul>li> a[href$=\"index.php?option=com_jgive&view=reports\"]:last').removeClass('active');
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
		
		<?php
		if(empty($this->payouts))
		{
			echo JText::_('COM_JGIVE_NO_DATA');
			?>
			<input type="hidden" id="task" name="task" value="" />
			<input type="hidden" name="option" value="com_jgive" />
			<input type="hidden" name="view" value="reports" />
			<input type="hidden" name="layout" value="payouts" />

		</form>
			<?php
			return;
		}
		?>
		<?php if(JVERSION >= 3.0 ){ ?> 
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<?php } ?>	
		<div class="com_jgive_float_left alert alert-info">
			<i><?php echo JText::_('COM_JGIVE_PAYOUT_VIEW_NOTICE');?></i>
		</div>
		<div class="com_jgive_clear_both"><br/></div>
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
				
				<th class="center" width="1%">
					<!-- <?php //checkAll(echo (count($this->payouts)+1));?> -->
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
				</th>
			
				<th class="center"><?php echo JText::_('COM_JGIVE_NUMBER');?></th>
				<th class="center"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PAYOUT_ID','id', $this->lists['order_Dir'], $this->lists['order']); ?></th>

				<th class="center"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PAYEE_NAME','payee_name', $this->lists['order_Dir'], $this->lists['order']); ?></th>

				<th class="center"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PAYPAL_EMAIL','email_id', $this->lists['order_Dir'], $this->lists['order']); ?></th>

				<th class="center"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_TRANSACTION_ID','transaction_id', $this->lists['order_Dir'], $this->lists['order']); ?></th>

				<th class="center"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PAYOUT_DATE','date', $this->lists['order_Dir'], $this->lists['order']); ?></th>

				<th class="center"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PAYMENT_STATUS','status', $this->lists['order_Dir'], $this->lists['order']); ?></th>

				<th class="center"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PAYOUT_AMOUNT','amount', $this->lists['order_Dir'], $this->lists['order']); ?></th>


			</tr>
			</thead>
			<?php
			$i=0;

			foreach($this->payouts as $payout)
			{

				?>
					<tr class="row<?php echo $i % 2;?>">

					<td class="center">
						 <?php echo JHtml::_('grid.id',$i,$payout->id);?>
					</td>
				
					<td class="center"><?php echo $i+1;?></td>

					<td class="center">
						<a href="<?php
						if(strlen($payout->id)<=6)
						{
							$append='';
							for($z=0;$z<(6-strlen($payout->id));$z++){
								$append.='0';
							}
							$payout->id=$append.$payout->id;
						}
						echo 'index.php?option=com_jgive&view=reports&layout=edit_payout&payout_id='.$payout->id; ?>"
						title="<?php echo JText::_('COM_JGIVE_PAYOUT_ID_TOOLTIP');?>">
							<?php echo $payout->id;
						?></a>
					</td>

					<td class="center">
						<?php
							$ulink=JRoute::_(JUri::base().'index.php?option=com_users&task=user.edit&id='.$payout->user_id);
							echo $payout->payee_name;
						?>
						<br/>
						<a href="<?php echo $ulink;?>" ><?php echo $payout->username;?></a>
					</td>
					<td class="center"><?php echo $payout->email_id;?></td>
					<td class="center"><?php echo $payout->transaction_id;?></td>
					<td class="center">
						<?php
						if(JVERSION<'1.6.0')
							echo JHtml::_( 'date', $payout->date, '%Y/%m/%d');
						else
							echo JHtml::_( 'date', $payout->date, "Y-m-d");
						?>
					</td>


					<td class="center">
						<?php
							if($payout->status)
								echo JText::_('COM_JGIVE_PAID');
							else
								echo JText::_('COM_JGIVE_NOT_PAID');
						?>
					</td>

					<td class="center"><?php 
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($payout->amount);
							echo $diplay_amount_with_format;
						?>
					</td>

				</tr>
			<?php
			$i++;
			}
			?>

			<tr rowspan="2" height="20">
				<td class="com_jgive_align_right" colspan="8"></td>
				<td></td>
			</tr>

			<tr>
				<td class="com_jgive_align_right" colspan="8"><b><?php echo JText::_( 'COM_JGIVE_SUBTOTAL'); ?></b></td>
				<td class="center">
					<b>
					<?php
					$reportsHelper=new reportsHelper();
					$totalAmount2BPaidOut=$reportsHelper->getTotalAmount2BPaidOut();

					$jgiveFrontendHelper=new jgiveFrontendHelper();
					$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($totalAmount2BPaidOut);
					echo $diplay_amount_with_format;

					?>
					</b>
				</td>
			</tr>

			<tr>
			<td class="com_jgive_align_right" colspan="8"><b><?php echo JText::_('COM_JGIVE_PAID_OUT'); ?></b></td>
				<td class="center">
					<b>
					<?php
					$totalpaidamount=$reportsHelper->getTotalPaidOutAmount();
					$jgiveFrontendHelper=new jgiveFrontendHelper();
					$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($totalpaidamount);
					echo $diplay_amount_with_format;
					?>
					</b>
				</td>
			</tr>

			<tr>
				<td class="com_jgive_align_right" colspan="8"><b><?php echo JText::_( 'COM_JGIVE_BALANCE'); ?></b></td>
				<td class="center">
					<b>
					<?php
						$balanceamt1=$totalAmount2BPaidOut-$totalpaidamount;
						$balanceamt=number_format($balanceamt1, 2, '.', '');
						if($balanceamt=='-0.00')
						{
							$balanceamt=0;
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($balanceamt);
							echo $diplay_amount_with_format;
						}
						else
						{
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($balanceamt1);
							echo $diplay_amount_with_format;
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
				<td class="center" colspan="9" class="<?php echo $class_pagination; ?> com_jgive_align_center"><?php echo $this->pagination->getListFooter(); ?></td>				
			</tr>
		</table>

		<input type="hidden" name="option" value="com_jgive" />
		<input type="hidden" name="view" value="reports" />
		<input type="hidden" name="layout" value="payouts" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" id="controller" name="controller" value="" />
		<input type="hidden" id="task" name="task" value="" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

	</form>
<?php if(JVERSION<3.0): ?>
</div>" >
<?php endif;?>
