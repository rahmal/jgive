<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access'); 

$document=JFactory::getDocument();

jimport('joomla.filter.output');
jimport( 'joomla.utilities.date');

if(JVERSION>=3.0)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');
}

$document->addStyleSheet(JUri::base().'components/com_jgive/assets/css/jgive.css');//backend css
//jomsocial toolbar
echo $this->jomsocailToolbarHtml;
?>

<?php if(JVERSION<3.0): ?>
<div class="techjoomla-bootstrap">
<?php endif;?>	
	<?php		
	if($this->issite)
	{
		?>
		<!--page header-->
		<h2 class="componentheading">
			<?php echo JText::_('COM_JGIVE_MY_PAYOUTS');?>
		</h2>
		
		<hr/>
		<?php
	}
	?>
	
	<form action="" method="post" name="adminForm"	id="adminForm">
		<?php		
		if(empty($this->payouts))
		{
			echo JText::_('COM_JGIVE_NO_DATA');
			?>
			<input type="hidden" name="option" value="com_jgive" />
			<input type="hidden" name="view" value="reports" /> 
			<input type="hidden" name="layout" value="mypayouts" /> 					

		</form>	
			
		<?php if(JVERSION<3.0): ?>
		</div><!--bootstrap-->
		<?php endif; 
			return;
		}
		?>
		<?php if(JVERSION >= 3.0 ){ ?> 
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<?php } ?>	
		<br/>
		<table class="adminlist table table-striped table-bordered">	
			<tr>
				<th><?php echo JText::_('COM_JGIVE_NUMBER');?></th>	
				<th><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PAYOUT_ID','id', $this->lists['order_Dir'], $this->lists['order']); ?></th>				
				<th><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PAYEE_DETAILS','email_id', $this->lists['order_Dir'], $this->lists['order']); ?></th>
				<th><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_TRANSACTION_ID','transaction_id', $this->lists['order_Dir'], $this->lists['order']); ?></th>				
				<th><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PAYOUT_DATE','date', $this->lists['order_Dir'], $this->lists['order']); ?></th>								
				<th><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PAYMENT_STATUS','status', $this->lists['order_Dir'], $this->lists['order']); ?></th>				
				<th><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PAYOUT_AMOUNT','amount', $this->lists['order_Dir'], $this->lists['order']); ?></th>				
			</tr>
			
			<?php 
			$i=0;
			
			foreach($this->payouts as $payout)
			{ 
				
				?>
				<tr>	
					<td><?php echo $i+1;?></td>

					<td>
												
						<?php 
						if(strlen($payout->id)<=6)
						{
							$append='';
							for($z=0;$z<(6-strlen($payout->id));$z++){
								$append.='0';
							}
							$payout->id=$append.$payout->id;
						}
						echo $payout->id; 
						?>
					</td>
					
					<td>
						<b>
						<?php 
							
							echo $payout->payee_name;					
						?>
						</b>
						<br/>
						<i>
							<?php echo $payout->username;?>
						</i>
						<br/>
						<?php echo $payout->email_id;?>					
					</td>
					
					<td><?php echo $payout->transaction_id;?></td>	
					<td>
						<?php 
						if(JVERSION<'1.6.0')	
							echo JHtml::_( 'date', $payout->date, '%Y/%m/%d');
						else
							 echo JFactory::getDate($payout->date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
						?>
					</td>	
					
					
					<td>
						<?php 
							if($payout->status)	
								echo JText::_('COM_JGIVE_PAID');
							else
								echo JText::_('COM_JGIVE_NOT_PAID');
						?>
					</td>
					
					<td><?php 
						$jgiveFrontendHelper=new jgiveFrontendHelper();
						echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($payout->amount);
					?></td>	
					
				</tr>
			<?php 
			$i++;
			} 
			?>
			
			<tr rowspan="2" height="20">				
				<td class="com_jgive_align_right" colspan="6"></td>
				<td></td>
			</tr>
			
			<tr>
				<td class="com_jgive_align_right" colspan="6"><b><?php echo JText::_( 'COM_JGIVE_SUBTOTAL'); ?></b></td>
				<td>
					<b>
					<?php
					$reportsHelper=new reportsHelper();
					$totalAmount2BPaidOut=$reportsHelper->getTotalAmount2BPaidOut($this->logged_userid);

					$jgiveFrontendHelper=new jgiveFrontendHelper();
					echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($totalAmount2BPaidOut);
					?>
					</b>
				</td>	
			</tr>
			
			<tr>				
			<td class="com_jgive_align_right" colspan="6"><b><?php echo JText::_('COM_JGIVE_PAID_OUT'); ?></b></td>
				<td>
					<b>
					<?php
						$totalpaidamount=$reportsHelper->getTotalPaidOutAmount($this->logged_userid);
						$jgiveFrontendHelper=new jgiveFrontendHelper();
						echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($totalpaidamount);
					?>
					</b>
				</td>	
			</tr>
			
			<tr>				
				<td class="com_jgive_align_right" colspan="6"><b><?php echo JText::_( 'COM_JGIVE_BALANCE'); ?></b></td>
				<td>
					<b>
					<?php 
						$balanceamt1=$totalAmount2BPaidOut-$totalpaidamount;
						$balanceamt=number_format($balanceamt1, 2, '.', '');
						if($balanceamt=='-0.00')
						{
							$balanceamt=0;
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($balanceamt);
						}
						else 
						{
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($balanceamt1);
						}
					?>
					</b>
				</td>
			</tr>
			
			<?php
			if(!$this->issite)
			{
				?>
				<tr>
				<?php 
					if(JVERSION<3.0)
						$class_pagination='pager';
					else
						$class_pagination='';
				?>
					<td colspan="6" class="<?php echo $class_pagination; ?> com_jgive_align_center">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
				<?php
			}
			?>
			
		</table>
		
		<?php
		if($this->issite)
		{
			?>
			<?php 
				if(JVERSION<3.0)
					$class_pagination='pager';
				else
					$class_pagination='';
			?>
			<div class="<?php echo $class_pagination; ?> com_jgive_align_center">
				<?php echo $this->pagination->getListFooter(); ?>
			</div>
			<?php
		}
		?>
		
		<input type="hidden" name="option" value="com_jgive" />
		<input type="hidden" name="view" value="reports" /> 
		<input type="hidden" name="layout" value="mypayouts" />
		<input type="hidden" name="task" value="" /> 
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
		
	</form>
<?php if(JVERSION<3.0): ?>
</div><!--bootstrap-->
<?php endif; ?>
