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

if(JVERSION>=3.0)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');
}

//load jgive js
$document->addScript(JUri::root().'components/com_jgive/assets/javascript/donations.js');//backend

$cdata=$this->donation_details;
//print_r($cdata);
if(JVERSION >= '1.6.0'){
       $core_js = JUri::root().'media/system/js/core.js';
       $flg=0;
       foreach($document->_scripts as $name=>$ar)
       {
               if($name == $core_js )
                       $flg=1;
       }
       if($flg==0)
				echo "<script type='text/javascript' src='".$core_js."'></script>";
}
$params=JComponentHelper::getParams( 'com_jgive' );
//added by Sneha
//Check if goal amount present or not
$show_goal_field=0;
$goal_amount=0;
$show_selected_fields=$params->get('show_selected_fields');

if($show_selected_fields)
{
	$creatorfield=$params->get('creatorfield');
	if(isset($creatorfield))
	foreach($creatorfield as $tmp)
	{
		switch($tmp)
		{
			case 'goal_amount':
				$goal_amount=1;
			break;
		}
	}
}
else
{
	$show_goal_field=1;
}
//End Addded by Sneha
$donations_site=( isset($this->donations_site) )?$this->donations_site:0;
$donations_email=( isset($this->donations_email) )?$this->donations_email:0;
?>
<?php 
if(JVERSION < 3.0) { ?>
<div class="techjoomla-bootstrap">
<?php } ?>
	<div class="row-fluid">
		<div class="">
			<div>
				<?php if($donations_site)
				{
					?>
					<h2 class="componentheading">
						<?php
							echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATION_DETAILS') : JText::_('COM_JGIVE_INVESTMENT_DETAILS'));
						?>
					</h2>

					<hr/>
					<?php
				}
				?>
			</div>

			<?php if($donations_email){?>
				<h4 style="background-color: #cccccc" ><?php
					echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATION_DETAILS_INFO') : JText::_('COM_JGIVE_INVESTMENT_DETAILS_INFO'));
				?></h4>
			<?php }?>

			<?php if($donations_site){ ?>
				<div class="well span6" >
					<h4><?php echo JText::_('COM_JGIVE_PAYMENT_DETAILS_SHORT'); ?></h4>
				<?php }
			else{?>
				<fieldset>
				<legend><?php echo JText::_('COM_JGIVE_PAYMENT_DETAILS_SHORT'); ?></legend>
				<?php } ?>

					<table class="table table-condensed adminlist table-striped table-bordered" >
						<tr>
							<td><?php
								echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATION_ID') : JText::_('COM_JGIVE_INVETMENT_ID'));
							?></td>
							<td><?php
							//echo $cdata['payment']->id;
							if(!$cdata['payment']->order_id){
								$cdata['payment']->order_id=$cdata['payment']->id;
							}
							echo $cdata['payment']->order_id;
							?></td>
						</tr>

						<tr>
							<td><?php
								echo JText::_('COM_JGIVE_BACK_ID');
							?></td>
							<td><?php

							if($cdata['payment']->giveback_id)
							{
								echo $cdata['payment']->giveback_id;
							}
							else
							{
								echo JText::_('COM_JGIVE_NO_GIVEBACK');
							}

							?></td>
						</tr>

						<tr>
							<td><?php echo JText::_('COM_JGIVE_DATE');?></td>
							<td><?php echo JFactory::getDate($cdata['payment']->cdate)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('COM_JGIVE_AMOUNT');?></td>
							<td>
								<?php 
									$jgiveFrontendHelper=new jgiveFrontendHelper();
									$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cdata['payment']->amount);
									echo $diplay_amount_with_format;
								 ?>
							</td>
						</tr>

						<?php 
						$params=JComponentHelper::getParams('com_jgive');
						if($params->get('vat_for_donor'))
						{
						 ?>
						<tr>
							<td><?php echo JText::_('COM_JGIVE_VAT_NUMBER');?></td>
							<td><?php echo $cdata['payment']->vat_number;?></td>
						</tr>
							<?php 
						} ?>

						<tr>
							<td><?php echo JText::_('COM_JGIVE_IP_ADDRESS');?></td>
							<td><?php echo $cdata['payment']->ip_address;?></td>
						</tr>
						<tr>
							<td><?php echo JText::_('COM_JGIVE_PAYMENT_METHOD');?></td>
							<td><?php 
								$donationsHelper= new donationsHelper();
								// gettng plugin name which is set in plugin option
								$plgname=$donationsHelper->getPluginName($cdata['payment']->processor);
								$plgname=!empty($plgname)?$plgname:$cdata['payment']->processor;
								echo $plgname;?>
							</td>
						</tr>
						<tr>
							<td><?php echo JText::_('COM_JGIVE_TRANSACTION_ID');?></td>
							<td><?php echo $cdata['payment']->transaction_id;?></td>
						</tr>
						<?php
							$annonymous_donation = '';
							switch($cdata['payment']->annonymous_donation)
							{
								case 0 :
									$annonymous_donation =  JText::_('COM_JGIVE_NO');
								break;
								case 1 :
									$annonymous_donation = JText::_('COM_JGIVE_YES') ;
								break;
							}
						?>
						<tr>
							<td><?php
							echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_ANNONYMOUS_DONATION') : JText::_('COM_JGIVE_ANNONYMOUS_INVESTMENT'));
							?></td>
							<td><?php echo $annonymous_donation;?></td>
						</tr>
					</table>

			<?php if($donations_site){?>
				</div>
			<?php }
			else{ ?>
				</fieldset>
			<?php } ?>


			<?php if($donations_site){?>
				<div class="well span5">
					<h4><?php echo JText::_('COM_JGIVE_PAYMENT_STATUS'); ?></h4>
				<?php }
			else{ ?>
				<fieldset>
				<legend><?php echo JText::_('COM_JGIVE_PAYMENT_STATUS'); ?></legend>
			<?php } ?>

				<form action="" name="adminForm" id="adminForm" class="form-validate" method="post">
					<table class="table table-condensed adminlist table-striped table-bordered" >
						<tr>
							<td><?php echo JText::_('COM_JGIVE_PAYMENT_STATUS');?></td>
							<td>
							<?php
								$whichever = '';
								//echo $cdata['payment']->status;
								switch($cdata['payment']->status)
								{
									case 'C' :
										$whichever =  JText::_('COM_JGIVE_CONFIRMED');
									break;
									case 'RF' :
										$whichever = JText::_('COM_JGIVE_REFUND') ;
									break;
									case 'P' :
									if($donations_site) {
										$whichever = JText::_('COM_JGIVE_PENDING') ;
									}
									break;
									case 'E' :
										$whichever = JText::_('COM_JGIVE_CANCELED') ;
									break;
									case 'D' :
										$whichever = JText::_('COM_JGIVE_DENIED') ;
									break;
								}

								if( ($cdata['payment']->status == 'P' || $cdata['payment']->status == 'C' || $cdata['payment']->status == 'E') && !($donations_site))
									echo JHtml::_('select.genericlist',$this->pstatus,"pstatus",'class="pad_status" size="1" onChange="selectstatusorder('.$cdata['payment']->id.',this);"',"value","text",$cdata['payment']->status);
								else
									echo $whichever ;
								 ?>

							</td>
						</tr>
						<?php if(!$donations_site){ ?>
							<tr>
								<td><?php echo JText::_('COM_JGIVE_NOTIFY');?></td>
								<td>
									<input type="checkbox" id = "notify_chk"  name = "notify_chk" value="1" size= "10" checked />
								</td>
							</tr>
							<tr>
								<td><?php echo JText::_('COM_JGIVE_COMMENT');?></td>
								<td><textarea id="" name="comment" rows="3" size="28" value=""></textarea></td>
							</tr>
						<?php } ?>
					</table>
					<input type="hidden" name="option" value="com_jgive" />
					<input type="hidden" id='hidid' name="id" value="" />
					<input type="hidden" id='hidstat' name="status" value="" />
					<input type="hidden" name="task" id="task" value="" />
					<input type="hidden" name="view" value="donations" />
					<input type="hidden" name="controller" value="donations" />

				</form>

			<?php if($donations_site) { ?>
			</div>
			<?php }
			else{ ?>
			</fieldset>
			<?php } ?>


	<div style="clear:both;"></div>

	<?php if($donations_site) { ?>
	<div class="well span6">
		<h4><?php
		echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONOR_DETAILS_SHORT') : JText::_('COM_JGIVE_INVESTOR_DETAILS_SHORT'));
		?></h4>
	<?php }
	else{ ?>
	<fieldset>
	<legend><?php
		echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONOR_DETAILS_SHORT') : JText::_('COM_JGIVE_INVESTOR_DETAILS_SHORT'));
	?></legend>
	<?php } ?>
		<table class="table table-condensed adminlist table-striped table-bordered" >
			<!--Condition added by Sneha-->
			<?php if( $cdata['donor']->first_name OR  $cdata['donor']->last_name): ?>
				<tr>
					<td><?php echo JText::_('COM_JGIVE_NAME');?></td>
					<td><?php echo $cdata['donor']->first_name.' '.$cdata['donor']->last_name;?></td>
				</tr>
			<?php endif; ?>

			<!--Condition added by Sneha-->
			<?php if( $cdata['donor']->address OR  $cdata['donor']->address2): ?>
				<tr>
					<td><?php echo JText::_('COM_JGIVE_ADDRESS');?></td>
					<td>
						<?php
							echo $cdata['donor']->address;
							echo "<br/>";
							echo $cdata['donor']->address2;
						?>
					</td>
				</tr>
			<?php endif; ?>

			<!--Condition added by Sneha-->
			<?php if( $cdata['donor']->zip): ?>
				<tr>
					<td><?php echo JText::_('COM_JGIVE_ZIP');?></td>
					<td><?php echo $cdata['donor']->zip;?></td>
				</tr>
			<?php endif; ?>

			<!--Condition added by Sneha-->
			<?php if( $cdata['donor']->country): ?>
				<tr>
					<td><?php echo JText::_('COM_JGIVE_COUNTRY');?></td>
					<td><?php echo $cdata['donor']->country;?></td>
				</tr>
			<?php endif; ?>

			<!--Condition added by Sneha-->
			<?php if( $cdata['donor']->state): ?>
				<tr>
					<td><?php echo JText::_('COM_JGIVE_STATE');?></td>
					<td><?php echo $cdata['donor']->state;?></td>
				</tr>
			<?php endif; ?>

			<!--Condition added by Sneha-->
			<?php if( $cdata['donor']->city): ?>
				<tr>
					<td><?php echo JText::_('COM_JGIVE_CITY');?></td>
					<td><?php echo $cdata['donor']->city;?></td>
				</tr>
			<?php endif;?>

			<!--Condition added by Sneha-->
			<?php if( $cdata['donor']->phone): ?>
				<tr>
					<td><?php echo JText::_('COM_JGIVE_PHONE');?></td>
					<td><?php echo $cdata['donor']->phone;?></td>
				</tr>
			<?php endif; ?>

			<tr>
				<td><?php echo JText::_('COM_JGIVE_EMAIL');?></td>
				<td><?php echo $cdata['donor']->email;?></td>
			</tr>
		</table>

	<?php if($donations_site) { ?>
	</div>
	<?php }
	else{ ?>
	</fieldset>
	<?php } ?>

	<?php if($donations_site) { ?>
	<div class="well span5">
		<h4><?php echo JText::_('COM_JGIVE_CAMPAIGN_DETAILS_SHORT'); ?></h4>
	<?php }
	else{ ?>
	<fieldset>
	<legend><?php echo JText::_('COM_JGIVE_CAMPAIGN_DETAILS_SHORT'); ?></legend>
	<?php } ?>
		<table class="table table-condensed adminlist table-striped table-bordered" >
			<tr>
				<td><?php echo JText::_('COM_JGIVE_TITLE');?></td>
				<td><?php echo $cdata['campaign']->title;?></td>
			</tr>
			<!--Condition added by Sneha-->
			<?php if($show_goal_field==1 OR $goal_amount==0 ): ?>
				<tr>
					<td><?php echo JText::_('COM_JGIVE_GOAL_AMOUNT');?></td>
					<td><?php 
						$jgiveFrontendHelper=new jgiveFrontendHelper();
						$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cdata['campaign']->goal_amount);
						echo $diplay_amount_with_format;
						?>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<td><?php echo JText::_('COM_JGIVE_AMOUNT_RECEIVED');?></td>
				<td><?php 
					$jgiveFrontendHelper=new jgiveFrontendHelper();
					$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cdata['campaign']->amount_received);
					echo $diplay_amount_with_format;
					?>
				</td>
			</tr>
			<!--Condition added by Sneha-->
			<?php if($show_goal_field==1 OR $goal_amount==0 ): ?>
				<tr>
					<td><?php echo JText::_('COM_JGIVE_REMAINING_AMOUNT');?></td>
					<td>
						<?php
						if($cdata['campaign']->amount_received>$cdata['campaign']->goal_amount){
							echo JText::_('COM_JGIVE_NA');
						}
						else{
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cdata['campaign']->remaining_amount);
							echo $diplay_amount_with_format;
						}
						?>
					</td>
				</tr>
			<?php endif; ?>
		</table>

	<?php if($donations_site) { ?>
	</div>
	<?php }
	else{ ?>
	</fieldset>
	<?php } ?>

		</div>
	</div>
<div style="clear:both;"></div>
<?php 
if(JVERSION < 3.0) { ?>
</div>
<?php } ?>
