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

//load frontend donations view - layout all
 $document = JFactory::getDocument();
 JToolBarHelper::DeleteList(JText::_('COM_JGIVE_DELETE_CONFRIM'),'remove','JTOOLBAR_DELETE');
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

JToolBarHelper::publishList();
JToolBarHelper::unpublishList();
JToolBarHelper::preferences( 'com_jgive' );
if(JVERSION>=3.0)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');
}

$campaignHelper =new campaignHelper();

/*Added by Sneha*/
$params=JComponentHelper::getParams('com_jgive');
$show_field=0;
$max_donation_cnf=0;
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
	$show_field=1;
}
/*Added by Sneha Ends*/
?>
<script type="text/javascript">

Joomla.submitbutton = function(action){
	var form = document.adminForm;
	if(action=='placeOrder')
	{


		Joomla.submitform(action );
	}
	else
		Joomla.submitform(action );
	}
</script>

<!--manoj - added for bill start-->
<script type="text/javascript">
function changeSuccessState(cid, ele)
{
	var selInd = ele.selectedIndex;
	var status = ele.options[selInd].value;
	var r;

	if(status==1)
	{
		r=confirm('<?php echo JText::_("COM_JGIVE_STATUS_CHANGE_CONFIRM_SUCCESS");?>');
	}

	if(status==-1)
	{
		r=confirm('<?php echo JText::_("COM_JGIVE_STATUS_CHANGE_CONFIRM_FAILED");?>');
	}

	if(status==0)
	{
		r=confirm('<?php echo JText::_("COM_JGIVE_STATUS_CHANGE_CONFIRM_ONGOING");?>');
	}

	if(r===true)
	{
		document.getElementById('hiddenCid').value = cid;
		document.getElementById('hiddenSuccessStatus').value = status;
		submitbutton('changeSuccessState');
	}
	else
	{
		return false;
	}
}
</script>
<!--manoj - added for bill end-->

<?php if(JVERSION<3.0): ?>
<div class="techjoomla-bootstrap">
<?php endif; ?>

<?php
	if($this->issite)
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

	<?php
	if($this->issite)
	{
		?>
		<!--page header-->
		<div class="componentheading">
			<?php echo JText::_('COM_JGIVE_ALL_CAMPAIGNS');?>
		</div>
		<hr/>
		<?php
	}
	?>


	<form action="index.php" method="post" name="adminForm" id="adminForm">

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
	<?php if(JVERSION >= 3.0 ){ ?>
	<div class="btn-group pull-right hidden-phone">
		<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>

	<?php } ?>
	<?php
	if(JVERSION >= 3.0 ){
		$tblclass='table table-striped';
	}
	else{
		$tblclass='adminlist table table-striped table-bordered';
	}
	?>
	<table class="<?php echo $tblclass; ?>" width="100%">
		<?php if(JVERSION<3.0):?>
			<tr>
				<!--<td colspan="11">-->
				<td colspan="13">
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
								echo JHtml::_('select.genericlist', $this->cat_options, "filter_campaign_cat", 'class="" size="1"
								onchange="document.adminForm.submit();" name="filter_campaign_cat"',"value", "text",$this->lists['filter_campaign_cat']);

								echo JHtml::_('select.genericlist', $this->campaign_approve_filter_options, "filter_campaign_approve", ' size="1"
								onchange="document.adminForm.submit();" name="filter_campaign_approve"',"value", "text", $this->lists['filter_campaign_approve']);
							endif;
							?>
					</div>
				</td>
			</tr>
		<?php endif;?>
		<?php if(JVERSION>=3.0):?>
		<thead>
		<?php endif;?>
			<tr>

				<th width="1%" >
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
				</th>
				<th width="15%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_CAMPAIGN_DETAILS','title', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
			<!-- categories -->
				<?php
				if(!$this->issite)
				{
					?>
					<th><?php echo JText::_('COM_JGIVE_INTERNAL_USE');?></th>
					<?php
				}
				?>
				<!--Added by Sneha-->
				<th width="5%" class="center" ><?php echo JText::_( 'COM_JGIVE_EDIT_LINK'); ?></th>
				<th width="10%" class="center" ><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_START_DATE','start_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="10%" class="center" ><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_END_DATE','end_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<!--if condition added by Sneha, to hide goal amount-->
				<?php if($show_field==1 OR $goal_amount==0 ): ?>
				<th width="15%"  class="center" ><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_GOAL_AMOUNT','goal_amount', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<?php endif; ?>
				<th width="15%" class="center" ><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_AMOUNT_RECEIVED','amount_received', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="5%" class="center" ><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_DONORS','donor_count', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="5%" class="center" ><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PUBLISHED','published', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="9%" class="center" ><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_FEATURED','featured', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="9%" class="center" ><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_ID','id', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>

				<!--manoj - added for bill start-->
				<th width="9%" class="center"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_CAMPAIGN_SUCCESS_STATUS','success_status', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<!--
				<th width="9%" class="center" ><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_CAMPAIGN_PROCESSED_FLAG','processed_flag', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				-->
				<!--manoj - added for bill end-->

			</tr>
	</thead>
			<?php
			if(!empty($this->data))
			{
				$i=1;
				$j=0;
				$k=0;
				foreach($this->data as $camp_data)
				{
					$data=$camp_data['campaign'];
					$images=$camp_data['images'];
					$row=$data;
                    $published=JHtml::_('jgrid.published',$row->published,$j);

					?>
					<tr class="row<?php echo $j % 2;?>">

						<td align="center">
						     <?php echo JHtml::_('grid.id',$j,$row->id);?>
						</td>

						<td>
							<div>
								<a target="_blank" href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$data->id.'&Itemid='.$this->singleCampaignItemid),strlen(JUri::base(true))+1);?>" title="<?php echo JText::_('COM_JGIVE_CLICK_TO_VIEW_CAMP_TOOLTIP');?>">
									<?php echo $data->title;?>
								</a>
							</div>
							<div class="com_jgive_clear_both"></div>
						</td>
						<!-- categories -->
						<?php
						if(!$this->issite)
						{
							?>
							<td>
								<?php
								if(isset($data->internal_use))
								{
									if($data->internal_use)
									{
										?>
										<div>
											<pre><?php echo $data->internal_use;?></pre>
										</div>
										<?php
									}
								}
								?>
							</td>
							<?php
						}
						?>
						<!--Added by Sneha-->
						<td class="center" ><a href="<?php echo JURI::Base().'index.php?option=com_jgive&view=campaign&layout=create&cid='.$data->id;?>" ><?php echo JText::_('COM_JGIVE_EDIT_LINK'); ?></a></td>

						<td class="center" ><?php echo JFactory::getDate($data->start_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));	?></td>

						<td class="center" ><?php echo JFactory::getDate($data->end_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));	?></td>

						<!--if condition added by Sneha, to hide goal amount-->
						<?php if($show_field==1 OR $goal_amount==0 ): ?>
						<td class="center" ><?php 
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($data->goal_amount);
							echo $diplay_amount_with_format;
							?>
						</td>
						<?php endif; ?>

						<td class="center" ><?php

							$jgiveFrontendHelper=new jgiveFrontendHelper();
							$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($data->amount_received);
							echo $diplay_amount_with_format;
							?>
						</td>

						<td class="center" ><?php echo $data->donor_count;?></td>

						<td class="center" >
							<?php
								echo $published;
							?>
						</td>
						<td align="center">
							<a href="javascript:void(0);" onclick=" listItemTask('cb<?php echo $k;?>','<?php echo ( $campaignHelper->isFeatured($data->id ) ) ? 'unfeature' : 'feature';?>')">
								<img src="<?php echo JUri::root();?>administrator/components/com_jgive/assets/images/<?php echo ( $campaignHelper->isFeatured( $data->id ) ) ? 'default.png' : 'nodefault.png';?>" width="16" height="16" border="0" />
							</a>
						</td>
						<td class="center" ><?php echo $data->id;?></td>

						<!--manoj - added for bill start-->
						<?php
							$campaign_success_status = '';

							switch($data->success_status)
							{
								case 0:
									$campaign_success_status =  JText::_('COM_JGIVE_SUCCESS_STATUS_ONGOING)');
								break;

								case 1:
									$campaign_success_status = JText::_('COM_JGIVE_SUCCESS_STATUS_SUCCESSFUL');
								break;

								case -1:
									$campaign_success_status = JText::_('COM_JGIVE_SUCCESS_STATUS_FAILED');
								break;
							}
						?>

						<td class="center" >
							<?php
							if($data->success_status == 0 || $data->success_status == 1 || $data->success_status == -1)
							{
								echo JHtml::_('select.genericlist', $this->campaignSuccessStatus,
								'campaignSuccessStatus' . $i,
								'class="input-medium" size="1" onChange="changeSuccessState(' . $data->id . ', this);"', "value", "text",
								$data->success_status);
							}
							else
							{
								echo $campaign_success_status;
							}
							?>
						</td>

						<?php
						if(!$data->processed_flag || $data->processed_flag=='NA')
						{
							$data->processed_flag = JText::_('COM_JGIVE_NA');
						}

						$campaign_processed_flag = '';
						$badge_class = 'info';

						switch($data->processed_flag)
						{
							case 'NA':
								$campaign_processed_flag =  JText::_('COM_JGIVE_PROCESSED_FLAG_NA');
								$badge_class = 'info';
								$cpf_badge_tooltip = JText::_('COM_JGIVE_PROCESSED_FLAG_TOOLTIP_NA');
							break;

							case 'SP':
								$campaign_processed_flag = JText::_('COM_JGIVE_PROCESSED_FLAG_NA_SP');
								$badge_class = 'success';
								$cpf_badge_tooltip = JText::_('COM_JGIVE_PROCESSED_FLAG_TOOLTIP_SP');
							break;

							case 'RF':
								$campaign_processed_flag = JText::_('COM_JGIVE_PROCESSED_FLAG_RF');
								$badge_class = 'warning';
								$cpf_badge_tooltip = JText::_('COM_JGIVE_PROCESSED_FLAG_TOOLTIP_RF');
							break;
						}

						?>
						<!--
						<td class="center" >
							<span class="badge badge-<?php echo $badge_class;?> hasTooltip" title="<?php echo $cpf_badge_tooltip; ?>">
								<?php echo $campaign_processed_flag;?>
							</span>
						</td>
						-->
						<!--manoj - added for bill end-->

					</tr>
					<?php
					$i++;
					$j++;
					$k++;
				}

			}
			else{
				?>
				<tr>

					<!--manoj - changed for bill start-->
					<!--
					<td colspan="11">
						<?php echo JText::_('COM_JGIVE_NO_DATA');?>
					</td>
					-->
					<td colspan="13" class="center">
						<?php echo JText::_('COM_JGIVE_NO_DATA');?>
					</td>
					<!--manoj - changed for bill end-->
				</tr>
				<?php
			}

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

					<!--manoj - changed for bill start-->
					<!--
						<td colspan="11" class="com_jgive_align_center <?php echo $class_pagination; ?> ">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					-->
					<td colspan="13" class="com_jgive_align_center <?php echo $class_pagination; ?> ">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
					<!--manoj - changed for bill end-->

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
		<input type="hidden" name="view" value="campaigns" />
		<input type="hidden" name="layout" value="all_list" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_cat'];?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" id="controller" name="controller" value="campaigns" />

		<!--manoj - added for bill start-->
		<input type="hidden" id="hiddenCid" name="hiddenCid" value="" />
		<input type="hidden" id="hiddenSuccessStatus" name="hiddenSuccessStatus" value="" />
		<!--manoj - added for bill end-->

	</form>
</div>

