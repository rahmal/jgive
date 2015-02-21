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

if(JVERSION>=3.0)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');
}

$document=JFactory::getDocument();

//load jgive js, css
$document->addScript(JUri::root().'components/com_jgive/assets/javascript/donations.js');//backend

JHtml::_('behavior.tooltip');
$params=JComponentHelper::getParams('com_jgive');
$db=JFactory::getDBO();
$result=$this->donations;
$donations_site=( isset($this->donations_site) )?$this->donations_site:0;
$Itemid=( isset($this->Itemid) )?$this->Itemid:0;

?>
<?php if(JVERSION<3.0): ?>
<div class="techjoomla-bootstrap" >
<?php endif;?>

	<?php
	if(!$this->logged_userid || $donations_site)
	{
	?>
	<div class="well" >
		<div class="alert alert-error">
			<span ><?php echo JText::_('COM_JGIVE_NO_ACCESS_MSG'); ?> </span>
		</div>
	</div>
	</div><!-- eoc akeeba-bootstrap -->
	<?php
		return false;
	}
	?>

	<script type="text/javascript">
		<?php
			if(JVERSION >= '1.6.0'){
		?>
				Joomla.submitbutton = function(action)
				{

			<?php
			}
			else{
			?>
				function submitbutton( action ){
			<?php
			}
			?>
					if(action=='deleteDonations')
					{
						if (document.adminForm.boxchecked.value==0){
							alert('<?php echo JText::_("COM_JGIVE_MAKE_SEL");?>');
							return;}

						var r=confirm('<?php echo JText::_("COM_JGIVE_DELETE_CONFIRM");?>');
						if (r==true)
						{
							var aa;
						}
						else return;

					}
					var form = document.adminForm;
					submitform( action );
					return;
				}
	</script>

	<style type="text/css">
	 .pagination a{
	   text-decoration:none;
	 }
	</style>

	<form action="" name="adminForm" id="adminForm" class="form-validate" method="post">
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

		if($donations_site)
		{
		?>
			<div class="page-header">
				<h2>
					<?php echo JText::_('COM_JGIVE_MY_DONATIONS')?>&nbsp;<small></small>
				</h2>
			</div>
		<?php
		}

		?>
		<?php if(JVERSION >= 3.0 ){ ?> 
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<?php } ?>	
		<div class="com_jgive_float_left alert alert-info">
			<i><?php echo JText::_('COM_JGIVE_FUND_HOLDER_DESC');?></i>
		</div>

		<div class="com_jgive_float_right">
			<?php 
			if(JVERSION<3.0):
				$campaignHelper=new campaignHelper();
				$campaign_type=$campaignHelper->filedToShowOrHide('campaign_type');

				if($campaign_type)
				{
				echo JHtml::_('select.genericlist', $this->campaign_type_filter_options, "filter_campaign_type", ' size="1"
				onchange="document.adminForm.submit();" name="filter_campaign_type"',"value", "text", $this->lists['filter_campaign_type']);
				}
				echo JHtml::_('select.genericlist', $this->filter_campaign_options, "filter_campaign", 'class="" size="1"
				onchange="document.adminForm.submit();" name="filter_campaign"',"value", "text", $this->lists['filter_campaign']);
			
				echo JHtml::_('select.genericlist', $this->sstatus, "payment_status", 'class="" size="1"
				onchange="document.adminForm.submit();" name="payment_status"',"value", "text", $this->lists['payment_status']);
			
			endif; 
		 ?>
		</div>

		<div class="com_jgive_clear_both"></div>
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

				<?php
				if(!$donations_site)
				{
				?>
					<th width="2%" align="center" class="title">
						<!-- checkAll(<?php //echo (count($result)+1);?> -->
						<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
					</th>
				<?php
				}
				?>

				<th class="center" width="5%"><?php echo JText::_('COM_JGIVE_NO'); ?></th>

				<th class="center" width="15%">
					<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_DONATION_ID','id', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>

				<th class="center" width="15%">
					<?php echo JHtml::_( 'grid.sort','COM_JGIVE_DONATION_STATUS','status', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>

				<th class="center" width="10%">
					<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_FUND_HOLDER','fund_holder', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>

				<th class="center" width="10%">
					<?php echo JHtml::_( 'grid.sort','COM_JGIVE_GATEWAY','processor', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>

				<?php
				if(!$donations_site)
				{
				?>
					<th class="center" width="9%">
						<?php echo JHtml::_( 'grid.sort','COM_JGIVE_USERNAME','donor_id', $this->lists['order_Dir'], $this->lists['order']); ?>
					</th>
				<?php
				}
				?>

				<th class="center" width="9%">
					<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_DONATION_DATE','cdate', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>

				<th class="center" width="9%">
					<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_AMOUNT','amount', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>

				<th class="center" width="9%">
					<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_COMMISSION_AMOUNT','fee', $this->lists['order_Dir'], $this->lists['order']); ?>
				</th>

				<th class="center" width="15%">
					<?php echo JText::_('COM_JGIVE_COMMENT'); ?>
				</th>

			</tr>
			</thead>
			<?php
			$id=1;
			$i=0;
			$filid=0;
			//print_r($result);die;
			foreach($result as $donations)
			{
			?>
				<tr class="row<?php echo $i % 2; $i++; ?>">
					<?php
					if(!$donations_site)
					{
					?>
						<td align="center">
							<?php echo JHtml::_('grid.id', $id, $donations->id ); ?>
						</td>
					<?php
					}
					?>

					<td class="center">
						<?php echo $id++; ?>
					</td>

					<td class="center">
						<a href="<?php
						if(!$donations->order_id){
							$donations->order_id=$donations->id;
						}

						if(!$donations_site){
							echo JRoute::_(JUri::base().'index.php?option=com_jgive&view=donations&layout=details&donationid='.$donations->id); ?>"><?php echo $donations->order_id;
						}
						else{
							echo JUri::base().substr(JRoute::_('index.php?option=com_jgive&view=donations&layout=details&donationid='.$donations->id.'&Itemid='.$Itemid),strlen(JUri::base(true))+1); ?>"><?php echo $donations->order_id;
						}

						?></a>
					</td>

					<td class="center">
						 <?php
						 $whichever = '';

						 switch($donations->status)
						 {
								case 'C' :
									$whichever =  JText::_('COM_JGIVE_CONFIRMED');
								break;

								case 'RF' :
									$whichever = JText::_('COM_JGIVE_REFUND') ;
								break;

								/*
								case 'E' :
									$whichever = JText::_('COM_JGIVE_SHIP') ;
								break;
								*/

								case 'P' :
								if($donations_site) {
									$whichever = JText::_('COM_JGIVE_PENDING') ;
								}
								break;
							/*	case 'D' :
									$whichever = JText::_('COM_JGIVE_DENIED') ;
								break;*/
						 }

						if( ($donations->status == 'P' || $donations->status == 'C' || $donations->status == 'RF' || $donations->status == 'E' || $donations->status == 'D') && !($donations_site))
							echo JHtml::_('select.genericlist',$this->pstatus,'pstatus'.$filid,'class="pad_status" size="1"
							onChange="selectstatusorder('.$donations->id.',this);"',"value","text",$donations->status);
						else
							echo $whichever ;
						 $filid++;
						 ?>
					</td>

					<td class="center">
						<?php
						 $fund_holder='';
						 switch($donations->fund_holder)
						 {
							case 0:
								$fund_holder=JText::_('COM_JGIVE_ADMIN');
							break;

							case 1:
								$fund_holder=JText::_('COM_JGIVE_PROMOTOR');
							break;
						 }
						echo $fund_holder;
						?>
					</td>

					<td class="center">
						<?php 
							$donationsHelper= new donationsHelper();
							// gettng plugin name which is set in plugin option
							$plgname=$donationsHelper->getPluginName($donations->processor);
							$plgname=!empty($plgname)?$plgname:$donations->processor;
							echo $plgname;
						?>
					</td>

					<?php
					if(!$donations_site)
					{
					?>
						<td class="center">
							<?php
							$table=JUser::getTable();
							$user_id=intval($donations->donor_id);
							$creaternm='';
							if($table->load( $user_id ))
							{
								$creaternm=JFactory::getUser($donations->donor_id);
							}
							//print_r($donations->ad_creator);
							$ulink=JRoute::_(JUri::base().'index.php?option=com_users&task=user.edit&id='.$donations->donor_id);
							if(!$creaternm)
								echo JText::_('COM_JGIVE_NO_USER');
							else
							{
								?>
								<a href="<?php echo $ulink;?>" ><?php echo $creaternm->username;?></a>
								<?php
							}
								?>
						</td>
					<?php
					}
					?>

					<td class="center">
						<?php echo JFactory::getDate($donations->cdate)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));?>
					</td>

					<td class="center">
						<?php 
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($donations->amount);
							echo $diplay_amount_with_format;
						?>
					</td>

					<td class="center">
						<?php 
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($donations->fee);
							echo $diplay_amount_with_format;
						 ?>
					</td>

					<td class="center">
						<span><?php echo $donations->comment; ?> </span>
					</td>
				</tr>

			<?php
			} //end for
			?>
			<tr>
			<?php 
				if(JVERSION<3.0)
					$class_pagination='pager';
				else
					$class_pagination='';
			?>					
				<td class="center" colspan="11" class="<?php echo $class_pagination; ?> com_jgive_align_center"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</table>

		<input type="hidden" name="option" value="com_jgive" />
		<input type="hidden" id='hidid' name="id" value="" />
		<input type="hidden" id='hidstat' name="status" value="" />
		<input type="hidden" name="task" id="task" value="" />
		<input type="hidden" name="view" value="donations" />
		<input type="hidden" name="controller" value="donations" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_type'];?>" />
		<input type="hidden"  name = "notify_chk" value="1"/>
		</table>

	</form>

<?php if(JVERSION<3.0): ?>
</div>
<?php endif;?>
