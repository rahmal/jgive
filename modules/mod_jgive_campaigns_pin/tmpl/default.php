<?php
/**
* @package    jGive Campaigns
* @author     Techjoomla
* @copyright  Copyright 2012 - Techjoomla
* @license    http://www.gnu.org/licenses/gpl-3.0.html
**/
$document=JFactory::getDocument();

//no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

include_once JPATH_ROOT.'/media/techjoomla_strapper/strapper.php';
//load techjoomla bootstrapper
TjAkeebaStrapper::bootstrap();
//load component css
$document=JFactory::getDocument();
$document->addStyleSheet(JURI::root().'components/com_jgive/assets/css/jgive.css');
//load module css
$document->addStyleSheet(JURI::root().'modules/mod_jgive_campaigns_pin/css/jgive_campaign.css');
$document->addScript(JUri::root().'components/com_jgive/assets/javascript/masonry.pkgd.min.js');
$com_jgive_params=JComponentHelper::getParams('com_jgive');

?>
<div class="techjoomla-bootstrap <?php echo $params->get('moduleclass_sfx'); ?>">
	<!--row-fluid-->
	<div class="row-fluid">

		<?php
			$camp_to_show='';
			$filter_name=JText::_('MOD_JGIVE_ALL_SUCC');
		?>
		<div class="span12" >
			<div class="pull-right">
				<?php
				$cat_url='index.php?option=com_jgive&view=campaigns&layout=all&campaigns_to_show='.$campaigns_to_show.'&Itemid='.$singleCampaignItemid;
				$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);
				echo ' <b><a href="'.$cat_url.'">'.JText::_('MOD_JGIVE_ALL_SUCC').'</a></b>';
				?>
			</div>
		</div>
		<div class="clearfix"></div>



		<!--container-->
		<div id="container<?php echo $module->id ;?>" class="thumbnails">

			<?php

			foreach($result as $row) {

			?>

				<!--mod_jgive_pin_layout_element-->
				<div class="thumbnail jgive_pin_item mod_jgive_pin_layout_element" style="padding:0px">

					<!--mod_jgive_pin_outer-->
					<div class="mod_jgive_pin_outer">
						<a href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$row['campaign']->id.'&Itemid='.$singleCampaignItemid),strlen(JUri::base(true))+1);?>">
						<img alt="300x200"  class="mod_jgive_pin_image" src="<?php
							foreach($row['images'] as $row_image)
							{
								if($row_image->gallery_image==0)
								{
									if(file_exists($row_image->path))
									{
										echo $row_image->path;
										break;
									}
									else
									{
										$path='images'.DS.'jGive'.DS;
										//get original image name to find it resize images (S,M,L)
										$org_file_after_removing_path=trim(str_replace($path,'M_',$row_image->path));
										$img_link= JUri::base().$path.$org_file_after_removing_path;
										echo $img_link;
										break;
									}

								}
							}
							?>">
						</a>
					</div>
					<!--mod_jgive_pin_outer-->

					<!-- caption -->
					<div class="caption">
						<div class="mod_jgive_pin_title">
						<a href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$row['campaign']->id.'&Itemid='.$singleCampaignItemid),strlen(JUri::base(true))+1);?>">
						  <?php
							if(strlen($row['campaign']->title)>=60)
								echo substr($row['campaign']->title,0,60).'...';
							else
								echo $row['campaign']->title;
							?>
						</a>
						</div>
						<div class="mod_jgive_pin_short_desc">
							<?php
								if(strlen($row['campaign']->short_description)>=120)
									echo substr($row['campaign']->short_description,0,120).'...';
								else
									echo $row['campaign']->short_description;
							?>
						</div>
						<div class="mod_jgive_pin_place" >
							<?php if (JVERSION>=3.0){?>
								<i class="icon icon-location"></i>
								<?php }else { ?>
								<i class="icon-map-marker"></i>
							<?php } ?>
							<?php echo $row['campaign']->city;  ?>, <?php echo $row['campaign']->country;  ?>
						</div>
					</div>
					<!-- caption -->

					<!--mod_jgive_pin_amount-->
					<div class="modal-footer mod_jgive_pin_amount" >
						<?php
						$donated_per =0;
						if(!empty($row['campaign']->goal_amount) && $row['campaign']->goal_amount >0)
						{
							$donated_per= ($row['campaign']->amount_received/$row['campaign']->goal_amount)*100;
							$donated_per=number_format((float)$donated_per, 2, '.', '').'%'; ?>

							<div class="mod_jgive_pin_outer" ><b><?php echo $donated_per;?></b>&nbsp; <small class="mod_jgive_pin_layout_font_weight"><?php echo JText::_('MOD_JGIVE_PIN_FUNDED');?></small>
							</div>

							<div class="progress progress-success progress-striped mod_jgive_pin_progress_bar" >

								<div class="bar" style="width: <?php echo $donated_per;?>%;">
								</div>
							</div>

						<?php
						} ?>

						<!--row-fluid-->
						<div class="row-fluid">
							<?php
							//calculate days left
								$date_expire=0;
								$curr_date=JFactory::getDate()->Format(JText::_('MOD_JGIVE_DATE_FORMAT_JOOMLA3'));
								$end_date=JFactory::getDate($row['campaign']->end_date)->Format(JText::_('MOD_JGIVE_DATE_FORMAT_JOOMLA3'));

								if($curr_date>$end_date){
									$date_expire=1;
								}

								$time_curr_date=strtotime($curr_date);
								$time_end_date=strtotime($row['campaign']->end_date);
								$interval = ($time_end_date-$time_curr_date);
								$days_left=floor($interval/(60*60*24));

								$lable= JText::_('MOD_JGIVE_DAYS_LEFT');
								if((int)($time_curr_date) && (int)($time_end_date))
								{
									$days= JText::_('MOD_JGIVE_DAYS_LEFT');
								}


								if($date_expire)
									$days= JText::_('MOD_JGIVE_NA');
								else if((int)($time_curr_date) && (int)($time_end_date))
								{
									$days= $days_left>0 ?  $days_left: JText::_('MOD_JGIVE_NA');
								}
							?>
							<div class="span6"><b><?php echo $days; ?>&nbsp;<?php echo JText::_('MOD_JGIVE_PIN_DAYS'); ?></b><br/><small class="mod_jgive_pin_layout_font_weight"><?php echo JText::_('MOD_JGIVE_DAYS__REMAINING'); ?></small>
							</div>
							<div class="span6"><b><?php
								$jgiveFrontendHelper=new jgiveFrontendHelper();
								echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($row['campaign']->amount_received);
								?> </b><br/>
								<small class="mod_jgive_pin_layout_font_weight"><?php echo JText::_('MOD_JGIVE_PIN_RAISED'); ?> </small>
							</div>

						</div>
						<!--row-fluid-->
					</div>
					<!--mod_jgive_pin_amount-->

				</div>
				<!--mod_jgive_pin_layout_element-->



		<?php } ?>
	</div>
		<!--container-->
	</div>
	<!--row-fluid-->

</div>
<?php

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
	.jgive_pin_item { width: <?php echo $pin_width . 'px'; ?> !important;}
</style>

<script type="text/javascript">
	techjoomla.jQuery(document).ready(function(){
		setTimeout(function(){
			var container = document.querySelector('#container<?php echo $module->id ;?>');
			var msnry = new Masonry( container, {
			  // options
			  columnWidth: <?php echo $columnWidth; ?>,
			  itemSelector: '.jgive_pin_item'
			});
		},2000);
	});
</script>
