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

$document=JFactory::getDocument();

// Load jgive css.
// Backend css.
$document->addStyleSheet(JUri::root().'components/com_jgive/assets/css/jgive.css');

$params = JComponentHelper::getParams( 'com_jgive' );
$db=JFactory::getDBO();
$result=$this->donations;

// $donations_site=( isset($this->donations_site) )?$this->donations_site:0;
$Itemid=( isset($this->Itemid) )?$this->Itemid:0;
?>

<script type="text/javascript">
	Joomla.submitbutton = function(action)
	{

		if (action === 'deletedonations')
		{

			if (document.adminForm.boxchecked.value == 0)
			{
				alert("<?php echo JText::_('COM_JGIVE_MAKE_SEL');?>");
				return false;
			}

			var r = confirm("<?php echo JText::_('COM_JGIVE_DELETE_CONFIRM');?>");

			if (r === false)
			{
				return false;
			}
		}

		var form = document.adminForm;
		submitform( action );

		return true;
	}
</script>

<?php

//jomsocial toolbar
echo $this->jomsocailToolbarHtml;

?>

<?php if(JVERSION<3.0): ?>
<div class="techjoomla-bootstrap">
<?php endif;?>

	<div id="jgive_my_donations" class="row-fluid">
		<div class="span12">

			<div class="row-fluid">
				<div class="span12">
					<h2 class="componentheading">
						<?php echo JText::_('COM_JGIVE_MY_DONATIONS');?>
					</h2>

					<hr class="hr hr-condensed"/>
				</div>
			</div><!--row-fluid-->

			<!-- show message if no items found -->
			<?php if (empty($this->donations)) : ?>
				<div class="alert"><?php echo JText::_('COM_JGIVE_NO_DATA_FOUND');?></div>
			<?php endif; ?>

			<div class="row-fluid">
				<div class="span12">

					<form action="" name="adminForm" id="adminForm" class="form-validate" method="post">

						<div class="row-fluid">
							<div class="span12">

								<!-- show pagination limit box and filters -->
								<fieldset class="filters btn-toolbar clearfix">

									<div class="btn-group clearfix">
										<?php
											echo JHtml::_('select.genericlist', $this->sstatus, "payment_status", 'size="1"
									onchange="this.form.submit();" name="payment_status"',"value", "text", $this->lists['payment_status']);
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

											<th class="center com_jgive_td_center com_jgive_width1 hidden-phone">
												<?php echo JText::_('COM_JGIVE_NO'); ?>
											</th>

											<th>
												<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_DONATION_ID','id', $this->lists['order_Dir'], $this->lists['order']); ?>
											</th>

											<th class="com_jgive_width15 center com_jgive_td_center hidden-phone">
												<?php echo JHtml::_( 'grid.sort','COM_JGIVE_GATEWAY','processor', $this->lists['order_Dir'], $this->lists['order']); ?>
											</th>

											<th class="nowrap center com_jgive_td_center com_jgive_width10">
												<?php echo JHtml::_( 'grid.sort','COM_JGIVE_DONATION_STATUS','status', $this->lists['order_Dir'], $this->lists['order']); ?>
											</th>

											<th class="nowrap center com_jgive_td_center com_jgive_width10 hidden-phone">
												<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_DONATION_DATE','cdate', $this->lists['order_Dir'], $this->lists['order']); ?>
											</th>

											<th class="nowrap com_jgive_align_right com_jgive_width10">
												<?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_AMOUNT','amount', $this->lists['order_Dir'], $this->lists['order']); ?>
											</th>

										</tr>
									</thead>

									<tbody>
										<?php
										$id=1;
										foreach($result as $donations)
										{
										?>
										<tr>
												<td class="center com_jgive_td_center com_jgive_width1 hidden-phone">
													<?php echo $id++;?>
												</td>

												<td class="small">
													<?php

													if(!$donations->order_id)
													{
														$donations->order_id=$donations->id;
													}
													?>

													<a href="<?php echo JRoute::_('index.php?option=com_jgive&view=donations&layout=details&donationid='.$donations->id.'&Itemid='.$Itemid); ?>">
														<?php echo $donations->order_id; ?>
													</a>
												</td>

												<td class="small center com_jgive_td_center com_jgive_width15 hidden-phone">
													<?php

													$donationsHelper= new donationsHelper();
													// gettng plugin name which is set in plugin option
													$plgname=$donationsHelper->getPluginName($donations->processor);
													$plgname=!empty($plgname)?$plgname:$donations->processor;
													echo $plgname;
													?>

												</td>

												<td class="nowrap small center com_jgive_td_center com_jgive_width10">
													 <?php
													 $whichever = '';

													 switch($donations->status)
													 {
														case 'C' :
															$whichever =  JText::_('COM_JGIVE_CONFIRMED');
															$class="success";
														break;
														case 'RF' :
															$whichever = JText::_('COM_JGIVE_REFUND') ;
															$class="error";
														break;
														case 'P' :
															$whichever = JText::_('COM_JGIVE_PENDING') ;
															$class="warning";
														break;
													 }
													?>

													<span class="small badge badge-<?php echo $class;?>">
														<?php
														echo $whichever;
														?>
													</span>
												</td>

												<td class="nowrap small center com_jgive_td_center com_jgive_width10 hidden-phone">
													<?php echo JFactory::getDate($donations->cdate)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));?>
												</td>

												<td class="nowrap small com_jgive_align_right com_jgive_width10">
													<?php
														$jgiveFrontendHelper=new jgiveFrontendHelper();
														$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($donations->amount);
														echo $diplay_amount_with_format;
													?>
												</td>

										</tr>
										<?php
										} // End for.
										?>
									</tbody>
							</table>
							</div>
							<div class="row-fluid">

								<div class="offset7 span3">
									<span class="clearfix pull-right">
										<strong><?php echo JText::_( 'COM_JGIVE_TOTAL_DONATION'); ?></strong>
									</span>
								</div>

								<div class="span2">
									<span class="clearfix pull-right">
										<strong>
										<?php
											$totalpaid=0;
											if(!empty($result))
											{
												foreach($result as $data)
												{
													$totalpaid=$totalpaid+$data->amount;
												}
											}
											$jgiveFrontendHelper=new jgiveFrontendHelper();
											$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($totalpaid);
											echo $diplay_amount_with_format;
										?>
										</strong>
									</span>

								</div>
							</div><!--row-fluid-->

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
						<input type="hidden" id='hidid' name="id" value="" />
						<input type="hidden" id='hidstat' name="status" value="" />
						<input type="hidden" name="task" id="task" value="" />
						<input type="hidden" name="view" value="donations" />
						<input type="hidden" name="controller" value="donations" />
						<input type="hidden" name="boxchecked" value="0" />
						<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
						<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />

					</form>

				</div>
			</div><!--row-fluid-->

		</div>
	</div><!--row-fluid-->

<?php if(JVERSION<3.0): ?>
</div><!--bootstrap-->
<?php endif; ?>

