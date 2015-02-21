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
echo '<div id="fb-root"></div>';
$fblike_tweet = JUri::root().'components/com_jgive/assets/javascript/fblike.js';
echo "<script type='text/javascript' src='".$fblike_tweet."'></script>";

if(JVERSION>=3.0)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	//JHtml::_('formbehavior.chosen', 'select');
}
$setdata=JRequest::get('get');

if(JVERSION >= '1.6.0'){
       $core_js = JUri::root().'media/system/js/core.js';
       $flg=0;
       $document=JFactory::getDocument();
       foreach($document->_scripts as $name=>$ar)
       {
               if($name == $core_js )
                       $flg=1;
       }
       if($flg==0)
				echo "<script type='text/javascript' src='".$core_js."'></script>";
}

$params=JComponentHelper::getParams('com_jgive');
$show_field=0;
$max_donation_cnf=0;
$show_goal_amount=0;
$show_selected_fields=$params->get('show_selected_fields');
if($show_selected_fields)
{
	$creatorfield=$params->get('creatorfield');
	if(isset($creatorfield))
	foreach($creatorfield as $tmp)
	{
		switch($tmp)
		{
			case 'max_donation':
				$max_donation_cnf=1;
			break;

			case 'long_desc':
				$long_desc_cnf=1;
			break;

			case 'goal_amount':
				$show_goal_amount=1;
			break;
		}
	}
}
else
{
	$show_field=1;
}

?>
<?php if(JVERSION<3.0): ?>
<div class="techjoomla-bootstrap">
<?php endif;?>
	<div id="all" class="row-fluid">
		<div class="span12">
			<!--row-fluid-->
			<div class="row-fluid">
				<div class="span12">
					<!--page header-->
					<h2 class="componentheading">
						<?php echo JText::_('COM_JGIVE_ALL_CAMPAIGNS');?>
					</h2>
					<hr/>
				</div>
				<!--span12-->
			</div>
			<!--row-fluid-->

			<!--row-fluid-->
			<div class="row-fluid">
				<?php
				//Get the filter html
				$html='';
				ob_start();
				include(JPATH_SITE .DS. 'components' .DS. 'com_jgive' .DS. 'views' .DS. 'campaigns' .DS. 'tmpl' .DS. 'filters.php');
				$html=ob_get_contents();
				ob_end_clean();

				$filter_alignment=$params->get('filter_alignment');

				if (empty($filter_alignment))
				{
					$filter_alignment='right';
				}

				//Render filters html left side of pings
				if($filter_alignment=='left')
				{
					echo $html;
				}
				 ?>

				<div class="span9">

					<div id="container">
					<?php
						$pin_padding=$params->get('pin_padding');

						if(empty($pin_padding))
						{
							$pin_padding=5;
						}
						else
						{
							$pin_padding=$pin_padding/2;
						}
					?>

						<?php
						foreach($this->data as $cdata)
						{

							$featured = 0;
							$jgive_featured_campaign='';
							//Featured Icon & border

							if($cdata['campaign']->featured)
							{
								$featured = 1;
								$jgive_featured_campaign = "jgive_featured_campaign";
							}
						?>

						<!--jgive_pin_item-->
						<div class="jgive_pin_item" style="padding-bottom:<?php echo $pin_padding.'px'; ?>;">
							<!--thumbnail-->
							<div class="thumbnail jgive_pin_layout_element <?php echo $jgive_featured_campaign; ?> " style="padding:0px;">

								<!-- Image-->
								<div style="padding:4px">

								<?php if($featured == 1)
								{ ?>
									<img src="<?php echo JUri::root().'components'.DS.'com_jgive'.DS.'assets'.DS.'images'.DS.'featured.png'; ?>" style="position:absolute;" width="22" height="22" title="<?php echo JText::_('COM_JGIVE_FEATURED_CAMPAIGN'); ?>"/>
									<?php
								}
								 ?>
									<a href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$cdata['campaign']->id.'&Itemid='.$this->singleCampaignItemid),strlen(JUri::base(true))+1);?>">
										<img alt="200x150"  class="jgive_thmb_style" src="<?php
										foreach($cdata['images'] as $images)
										{
											if($images->gallery_image==0)
											{
												if(file_exists($images->path))
												{
													echo $images->path;
													break;
												}
												else
												{
													$path='images'.DS.'jGive'.DS;
													//get original image name to find it resize images (S,M,L)
													$org_file_after_removing_path=trim(str_replace($path,'M_',$images->path));
													echo JUri::base().$path.$org_file_after_removing_path;
													break;
												}

											}
										}
										?>" />
									</a>
								</div>
								<!-- Image-->

								<!--caption -->
								<div class="caption">

									<!--jgive_caption-->
									<div class="jgive_caption">
										<a href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$cdata['campaign']->id.'&Itemid='.$this->singleCampaignItemid),strlen(JUri::base(true))+1);?>">
										  <?php
											if(strlen($cdata['campaign']->title)>=60)
												echo substr($cdata['campaign']->title,0,60).'...';
											else
												echo $cdata['campaign']->title;
											?>
										</a>
									</div>
									<!--jgive_caption-->

									<!--jgive_short_desc-->
									<div class="jgive_short_desc">
										<?php
											if(strlen($cdata['campaign']->short_description)>=120)
												echo substr($cdata['campaign']->short_description,0,120).'...';
											else
												echo $cdata['campaign']->short_description;
										?>
									</div>
									<!--jgive_short_desc-->

									<!--jgive_place -->
									<div class="jgive_place" >
										<?php if (JVERSION>=3.0){?>
											<i class="icon icon-location"></i>
										<?php }else { ?>
											<i class="icon-map-marker"></i>
										<?php } ?>
										<?php echo $cdata['campaign']->city;  ?>, <?php echo $cdata['campaign']->country;  ?>
									</div>
									<!--jgive_place -->

								</div>
								<!--caption -->

								<!--modal-footer jgive_amount -->
								<div class="modal-footer jgive_amount">
								<!--if condition added by Sneha, to hide progress bar and percent-->
								<?php if($show_field==1 OR $show_goal_amount==0 ): ?>
									<?php
									$donated_per=0;
									$goal_amount = (float)$cdata['campaign']->goal_amount;
									if(!empty($cdata['campaign']->amount_received) && $goal_amount>0)
									{
										$donated_per= ($cdata['campaign']->amount_received/$cdata['campaign']->goal_amount)*100;
									}

									$donated_per=number_format((float)$donated_per, 2, '.', '');
									?>
									<div class="jgive_funded jgive_text_aling" ><b><?php echo $donated_per;?>%</b>&nbsp; <small class="mod_jgive_pin_layout_font_weight "><?php echo JText::_('COM_JGIVE_LAYOUT_FUNDED');?></small>
									</div>

									<div class="progress progress-success progress-striped jgive_donation_per jgive_progress" >

										<div class="bar" style="width: <?php echo $donated_per;?>%;">
										</div>
									</div>
								<?php endif; ?>
									<div class="row-fluid">
										<?php
										//calculate days left
											$date_expire=0;
											$curr_date=JFactory::getDate()->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
											$end_date=JFactory::getDate($cdata['campaign']->end_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));

											if($curr_date>$end_date){
												$date_expire=1;
											}

											$time_curr_date=strtotime($curr_date);
											$time_end_date=strtotime($cdata['campaign']->end_date);
											$interval = ($time_end_date-$time_curr_date);
											$days_left=floor($interval/(60*60*24));

											$days='';
											$lable= JText::_('COM_JGIVE_DAYS_LEFT');
											if((int)($time_curr_date) && (int)($time_end_date))
											{
												$days= JText::_('COM_JGIVE_DAYS_LEFT');
											}


											if($date_expire)
												$days= JText::_('COM_JGIVE_NA');
											else if((int)($time_curr_date) && (int)($time_end_date))
											{
												$days= $days_left>0 ?  $days_left: JText::_('COM_JGIVE_NA');
											}
										?>

										<div class="span6 jgive_text_aling"><b><?php echo $days; ?>&nbsp;<?php echo JText::_('COM_JGIVE_LAYOUT_DAYS'); ?></b><br/><small class="mod_jgive_pin_layout_font_weight"><?php echo JText::_('COM_JGIVE_DAYS__REMAINING'); ?></small>
										</div>

										<div class="span6 jgive_text_aling">
											<b>
											<?php
											$jgiveFrontendHelper=new jgiveFrontendHelper();
											$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cdata['campaign']->amount_received);
											echo $diplay_amount_with_format;
											 ?>
											</b>
												<br/>
												<small class="mod_jgive_pin_layout_font_weight"><?php echo JText::_('COM_JGIVE_LAYOUT_RAISED'); ?> </small>
										</div>

									</div>
									<!--row-fluid-->

								</div>
								<!--modal-footer jgive_amount -->

							</div>

							<!--thumbnail-->
						</div>
						<!--jgive_pin_item-->
						<?php
						}
						?>
					</div>
					<!--container -->
				</div>
				<!--span9 -->


				<?php
				//Render filters html right side of pings
				if($filter_alignment=='right')
				{
					echo $html;
				}
				?>
			</div>
			<!--row-fluid-->


			<form action="" method="post" name="adminForm" id="adminForm">

				<div class="row-fluid">
					<div class="span12">
						<?php
							if(JVERSION<3.0)
								$class_pagination='pager';
							else
								$class_pagination='';
						?>
						<div class="<?php echo $class_pagination; ?> com_jgive_align_center">
							<?php echo $this->pagination->getListFooter(); ?>
						</div>
					</div><!--span12-->
				</div><!--row-fluid-->

				<input type="hidden" name="option" value="com_jgive" />
				<input type="hidden" name="view" value="campaigns" />
				<input type="hidden" name="layout" value="all" />
				<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_cat'];?>" />
			</form>

		</div><!--span12-->
	</div><!--row-fluid-->
<?php if(JVERSION<3.0): ?>
</div><!--bootstrap-->
<?php endif; ?>

<?php

$document=JFactory::getDocument();
$document->addScript(JUri::root().'components/com_jgive/assets/javascript/masonry.pkgd.min.js');

//Get pin width
$pin_width=$params->get('pin_width');

if(empty($pin_width))
{
	$pin_width=240;
}

//Get pin padding
$pin_padding=$params->get('pin_padding');

if(empty($pin_padding))
{
	$pin_padding=5;
}

//Calulate columnWidth (columnWidth = pin_width+pin_padding)
$columnWidth=$pin_width+$pin_padding;
?>

<style>
.jgive_pin_item { width: <?php echo $pin_width . 'px'; ?> !important; }
</style>

<script type="text/javascript">

	techjoomla.jQuery(document).ready(function()
	{
		setTimeout(function(){
			var container = document.querySelector('#container');
			var msnry = new Masonry( container, {
			  // options
			  columnWidth: <?php echo $columnWidth; ?>,
			  itemSelector: '.jgive_pin_item'
			});
		},1000);

		setTimeout(function(){
			var container = document.querySelector('#container');
			var msnry = new Masonry( container, {
			  // options
			  columnWidth: <?php echo $columnWidth; ?>,
			  itemSelector: '.jgive_pin_item'
			});
		},2000);

		setTimeout(function(){
			var container = document.querySelector('#container');
			var msnry = new Masonry( container, {
			  // options
			  columnWidth: <?php echo $columnWidth; ?>,
			  itemSelector: '.jgive_pin_item'
			});
		},3000);

		setTimeout(function(){
			var container = document.querySelector('#container');
			var msnry = new Masonry( container, {
			  // options
			  columnWidth: <?php echo $columnWidth; ?>,
			  itemSelector: '.jgive_pin_item'
			});
		},5000);

	});


</script>

