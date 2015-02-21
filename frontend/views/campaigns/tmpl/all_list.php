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

$js_joomla16 ="Joomla.submitbutton = function(prm)
{
	if(prm=='publish' || prm=='unpublish' || prm=='remove')
	{
		Joomla.submitform(prm);
	}
	else
	{
		window.location = 'index.php?option=com_jgive&view=campaigns&layout=all_list';
	}
}";
$document->addScriptDeclaration($js_joomla16);
?>
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
				<td colspan="11">
					<div class="com_jgive_float_right">
							<?php 
							if(JVERSION<3.0):

								$campaignHelper=new campaignHelper();
								$campaign_type=$campaignHelper->filedToShowOrHide('campaign_type');

								if($this->params->get('show_type_filter') AND $campaign_type)
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
				<th width="5%"><?php echo JText::_( 'COM_JGIVE_EDIT_LINK'); ?></th>
				<th width="10%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_START_DATE','start_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="10%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_END_DATE','end_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="15%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_GOAL_AMOUNT','goal_amount', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="15%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_AMOUNT_RECEIVED','amount_received', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="5%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_DONORS','donor_count', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="5%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PUBLISHED','published', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="9%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_FEATURED','featured', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width="7%"><?php echo JText::_('COM_JGIVE_CAMPAIGN_STATUS');?></th>
				<th width="2%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_ID','id', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>			
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
						<td><a href="<?php echo JURI::Base().'index.php?option=com_jgive&view=campaign&layout=create&cid='.$data->id;?>" ><?php echo JText::_('COM_JGIVE_EDIT_LINK'); ?></a></td>
						
						<td><?php echo JFactory::getDate($data->start_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));	?></td>

						<td><?php echo JFactory::getDate($data->end_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));	?></td>

						<td><?php echo $data->goal_amount.' '.$this->currency_code;?></td>

						<td><?php echo $data->amount_received.' '.$this->currency_code;?></td>

						<td><?php echo $data->donor_count;?></td>

						<td>
							<?php
								echo $published;
							?>
						</td>
						<td align="center">
							<a href="javascript:void(0);" onclick=" listItemTask('cb<?php echo $k;?>','<?php echo ( $campaignHelper->isFeatured($data->id ) ) ? 'unfeature' : 'feature';?>')">
								<img src="<?php echo JUri::root();?>administrator/components/com_jgive/assets/images/<?php echo ( $campaignHelper->isFeatured( $data->id ) ) ? 'default.png' : 'nodefault.png';?>" width="16" height="16" border="0" />
							</a>
						</td>
						<td>
							<?php 
							if($data->status=='closed')
							{
								echo JText::_('COM_JGIVE_CAMP_CLOSED');
							}
							else if($data->status=='active')
							{
								echo JText::_('COM_JGIVE_CAMP_ACTIVE');
							}
							else
							{
								echo JText::_('COM_JGIVE_CAMP_SUCCESSFUL');
							}
							 ?>
						</td>
						<td><?php echo $data->id;?></td>
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
					<td colspan="11">
						<?php echo JText::_('COM_JGIVE_NO_DATA');?>
						<!--<input type="hidden" name="defaltevent" value="<?php //echo $this->lists['filter_campaign'];?>" />-->
					</td>
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
					<td colspan="11" class="com_jgive_align_center <?php echo $class_pagination; ?> ">
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
		<input type="hidden" name="view" value="campaigns" />
		<input type="hidden" name="layout" value="all_list" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_cat'];?>" /> 
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" value="" />

	</form>
</div>
