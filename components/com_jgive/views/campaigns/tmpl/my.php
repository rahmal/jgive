<?php
/**
 * @version    SVN: <svn_id>
 * @package    JGive
 * @author     Techjoomla <contact@techjoomla.com>
 * @copyright  Copyright (C) 2012-2013 Techjoomla. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');

if(JVERSION>=3.0)
{
	JHtml::_('bootstrap.tooltip');
}
//jomsocial toolbar
echo $this->jomsocailToolbarHtml;
?>
<!--Added by SNeha-->
<?php
$params=JComponentHelper::getParams('com_jgive');
$show_selected_fields=$params->get('show_selected_fields');
$creatorfield=array();
$show_field=0;
$goal_amount=0;

if ($show_selected_fields)
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
?>
<!--added by Sneha Ends-->
<?php if(JVERSION<3.0): ?>
<div class="techjoomla-bootstrap">
<?php endif;?>

	<div id="jgive_campaigns_my" class="row-fluid">
		<div class="span12">

			<div class="row-fluid">
				<div class="span12">
					<h2 class="componentheading">
						<?php echo JText::_('COM_JGIVE_MY_CAMPAIGNS');?>
					</h2>

					<hr class="hr hr-condensed"/>
				</div>
			</div><!--row-fluid-->

			<!-- show message if no items found -->
			<?php if (empty($this->data)) : ?>
				<div class="alert"><?php echo JText::_('COM_JGIVE_NO_DATA_FOUND');?></div>
			<?php endif; ?>

			<div class="row-fluid">
					<div class="span12">

						<form action="" method="post" name="adminForm" id="adminForm">

							<div class="row-fluid">
								<div class="span12">

									<!-- show pagination limit box and filters -->
									<fieldset class="filters btn-toolbar clearfix">

										<div class="btn-group clearfix">
											<?php
											$campaignHelper=new campaignHelper();
											$campaign_type=$campaignHelper->filedToShowOrHide('campaign_type');
											if($this->params->get('show_type_filter_my_camp') AND $campaign_type)
											{
												echo JHtml::_('select.genericlist', $this->campaign_type_filter_options, "filter_campaign_type", ' size="1"
												onchange="this.form.submit();" name="filter_campaign_type"',"value", "text", $this->lists['filter_campaign_type']);
												?>
												&nbsp;
												<?php
											}
											?>
										</div>

										<div class="btn-group clearfix">
											<?php
											if($this->params->get('show_category_filter_my'))
											{
												echo JHtml::_('select.genericlist', $this->cat_options, "filter_campaign_cat", 'class="" size="1"
												onchange="this.form.submit();" name="filter_campaign_cat"',"value", "text",$this->lists['filter_campaign_cat']);
												?>
												&nbsp;
												<?php
											}
											?>
										</div>

										<div class="btn-group clearfix">
											<?php
											if($this->params->get('show_org_ind_type_filter_my'))
											{
												echo JHtml::_('select.genericlist', $this->filter_org_ind_type, "filter_org_ind_type_my", 'class="" size="1"
												onchange="this.form.submit();" name="filter_org_ind_type_my"',"value", "text",$this->lists['filter_org_ind_type_my']);
												?>
												&nbsp;
												<?php
											}
											?>
										</div>

										<?php
											if(JVERSION >= 3.0 )
											{
												?>
												<div class="btn-group pull-right hidden-phone">
													<label for="limit" class="element-invisible">
														<?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
													</label>
													<?php echo $this->pagination->getLimitBox(); ?>
												</div>
												<?php
											}
											else
											{
												?>
												<div class="btn-group pull-right hidden-phone">
													<?php echo $this->pagination->getLimitBox(); ?>
												</div>
												<?php
											}
										?>
									</fieldset>
								</div>
							</div><!--row-fluid-->

							<div class="row-fluid">
								<div class="span12">

									<table class="table table-striped table-bordered table-hover">
										<thead>
											<tr>

												<th>
													<?php echo JText::_('COM_JGIVE_GIVEBACK_NUMBER');?>
												</th>

												<th>
													<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_CAMPAIGN_DETAILS','title', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
												</th>

												<th class="hidden-phone">
													<?php echo JText::_('COM_JGIVE_EDIT_CAMPAIGN');?>
												</th>

												<th class="hidden-phone">
													<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_START_DATE','start_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
												</th>

												<th class="hidden-phone">
													<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_END_DATE','end_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
												</th>
												<!--if condition added by Sneha-->
												<?php if($show_field==1 OR $goal_amount==0 ): ?>
													<th class="hidden-phone">
														<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_GOAL_AMOUNT','goal_amount', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
													</th>
												<?php endif;?>
												<th>
													<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_AMOUNT_RECEIVED','amount_received', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
												</th>

												<th>
													<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_DONORS','donor_count', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
												</th>

												<th class="hidden-phone">
													<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_PUBLISHED','published', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
												</th>

											</tr>

										</thead>

										<tbody>
										<?php
										$i=1;
										foreach($this->data as $camp_data)
										{
											$data=$camp_data['campaign'];
											$images=$camp_data['images'];
											?>
											<tr>

												<td><?php echo $i;?></td>

												<td>
													<div>
														<a href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$data->id.'&Itemid='.$this->singleCampaignItemid),strlen(JUri::base(true))+1);?>">
															<?php echo $data->title;?>
														</a>
													</div>
													<div class="com_jgive_clear_both"></div>
												</td>

												<td class="hidden-phone">
													<a href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=create&cid='.$data->id.'&Itemid='.$this->createCampaignItemid),strlen(JUri::base(true))+1);?>">
														<?php echo JText::_('COM_JGIVE_EDIT_CAMPAIGN');?>
													</a>
												</td>

												<td class="hidden-phone"><?php echo JFactory::getDate($data->start_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));	?></td>

												<td class="hidden-phone"><?php echo JFactory::getDate($data->end_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));	?></td>
												<!--if condition added by Sneha-->
												<!--check if goal amount is present -->
												<?php if($show_field==1 OR $goal_amount==0 ): ?>
												<td class="hidden-phone">
													<?php 
													$jgiveFrontendHelper=new jgiveFrontendHelper();
													$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($data->goal_amount);
													echo $diplay_amount_with_format;
													?>
												</td>
												<?php endif; ?>
												<td><?php 
													$jgiveFrontendHelper=new jgiveFrontendHelper();
													$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($data->amount_received);
													echo $diplay_amount_with_format;
													?>
												</td>

												<td><?php echo $data->donor_count;?></td>

												<td class="hidden-phone">
													<?php
													if($data->published)
													{
														echo '<i class="icon-ok"></i>&nbsp;';
														echo JText::_('COM_JGIVE_YES');
													}
													else
													{
														echo '<i class="icon-remove"></i>&nbsp;';
														echo JText::_('COM_JGIVE_NO');
													}
													?>
												</td>

											</tr>
											<?php
											$i++;
										}
										?>
										</tbody>

									</table>

								</div><!--span12-->
							</div><!--row-fluid-->

							<?php
								if(JVERSION < '3.0')
								{
									$class_pagination='pager';
								}
								else
								{
									$class_pagination='pagination';
								}
							?>

							<div class="row-fluid">
								<div class="span12">

									<div class="<?php echo $class_pagination; ?>">
										<p class="counter pull-right">
											<?php echo $this->pagination->getPagesCounter(); ?>
										</p>
										<?php echo $this->pagination->getPagesLinks(); ?>
									</div>
									<hr class="hr hr-condensed"/>

								</div>
							</div><!--row-fluid-->

							<input type="hidden" name="option" value="com_jgive" />
							<input type="hidden" name="view" value="campaigns" />
							<input type="hidden" name="layout" value="my" />
							<input type="hidden" name="task" value="" />
							<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
							<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
							<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_cat'];?>" />
					</form>

				</div><!--span12-->
			</div><!--row-fluid-->

		</div><!--span12-->
	</div><!--row-fluid-->

<?php if(JVERSION<3.0): ?>
</div>
<?php endif; ?>
