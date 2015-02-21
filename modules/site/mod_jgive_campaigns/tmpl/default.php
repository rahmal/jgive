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
//load techjoomla bootstrapper
include_once JPATH_ROOT.'/media/techjoomla_strapper/strapper.php';
TjAkeebaStrapper::bootstrap();
//load component css
$document->addStyleSheet(JURI::root().'components/com_jgive/assets/css/jgive.css');
//load module css
///var/www/j3_testing/modules/mod_jgive_campaigns/css
$document->addStyleSheet(JURI::root().'modules/mod_jgive_campaigns/css/jgive_campaign.css');

//load Campaigns helper
$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'campaign.php';
if(!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath );
	JLoader::load('campaignHelper');
}

//Added By Sneha
$params_jgive=JComponentHelper::getParams('com_jgive');

//get the data to idetify which field to show
$show_selected_fields=$params_jgive->get('show_selected_fields');
$creatorfield=array();
$show_field=0;
$goal_amount=0;

if ($show_selected_fields)
{
	$creatorfield=$params_jgive->get('creatorfield');

	if(isset($creatorfield))
	foreach($creatorfield as $tmp)
	{
		switch($tmp)
		{
			case 'goal_amount': /*Added by SNeha*/
				$goal_amount=1;
			break;
		}
	}
}
else
{
	$show_field=1;
}
//Ends added Sneha
?>

<?php
	require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helper.php");
	//get items ids
	//@create jgiveFrontendHelper object
	$jgiveFrontendHelper=new jgiveFrontendHelper();
	$singleCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=single');
	$allCampaignsItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
	//use campaign helper
	require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."campaign.php");
	$campaignHelper=new campaignHelper();
	$cdata=array();
	foreach($result as $d)//modifiy the data
	{
		//get campaign amounts
		$amounts=$campaignHelper->getCampaignAmounts($d->id);
		$d->amount_received=$amounts['amount_received'];
		$d->remaining_amount=$amounts['remaining_amount'];
		$d->donor_count=$campaignHelper->getCampaignDonorsCount($d->id);
	}
	if($orderby=='amount_received' or $orderby='remaining_amount')
	{
		$Modulehelper=JPATH_SITE.DS.'modules'.DS.'mod_jgive_campaigns'.DS.'helper.php';
		if(!class_exists('modJGiveHelper'))
		{
			JLoader::register('modJGiveHelper', $Modulehelper );
			JLoader::load('modJGiveHelper');
		}
		$filter=$orderby;
		$modJGiveHelper=new modJGiveHelper();
		if(!empty($result))
		$result=$modJGiveHelper->multi_d_sort($result,$filter,$orderby_dir,$count);
	}

	//get currency from component config
	//@TO Do change component parameter variable name
	$com_params=JComponentHelper::getParams('com_jgive');
	$currency_code=$com_params->get('currency');
?>

<div class="techjoomla-bootstrap <?php echo $params->get('moduleclass_sfx'); ?>">
	<?php
	$arraycnt=count($result);
	$arraycnt<$count ? $count=$arraycnt:$count;
	//echo "<pre>";print_r($result);echo "</pre>";die;
	for($i=0;$i<$count;$i++)
	{
	?>
		<div>
			<?php
			// Show the star to know the campaigns marked as Featured
				$title=JText::_('MOD_JGIVE_FEATURED');
				$featured=$campaignHelper->isFeatured( $result[$i]->id );
				echo $featured ? $imgpath='<img src="'.JURI::root().'components/com_jgive/assets/images/featured.png"  title="'.$title .'">':'';
			?>
			<a href="<?php echo JURI::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$result[$i]->id.'&Itemid='.$singleCampaignItemid),strlen(JURI::base(true))+1);?>">
				<b><?php echo $result[$i]->title;?></b>
			</a>
		</div>
		<br/>
		<div id="containerdiv" >
			<div id="image-div">
				<?php if($result[$i]->path)
				{
					$path='images'.DS.'jGive'.DS;
					//get original image name to find it resize images (S,M,L)
					$org_file_after_removing_path=trim(str_replace($path,'',$result[$i]->path));

					if(file_exists($result[$i]->path)) //if loop for old version compability (where img resize not available means no L , M ,S before image name)
					{
						echo "<img class='com_jgive_img_48_48' src='".JURI::base().$result[$i]->path."' />";
					}
					else
					{
						echo "<img class='com_jgive_img_48_48' src='".JUri::base().$path.'S_'.$org_file_after_removing_path."' />";
					}
				}
				?>
			</div>

			<?php
			//check if exeeding goal amount is allowed
			//if not check for received amount to decide about hiding donate button
			$flag=0;
			if($result[$i]->allow_exceed==0)
			{
				 if($result[$i]->amount_received>=$result[$i]->goal_amount){
					 $flag=1;
				 }
			}
			if($result[$i]->max_donors>0)
			{
				 if($result[$i]->donor_count >= $result[$i]->max_donors){
					  $flag=1;
				 }
			}
			//if both start date, and end date are present
			if((int)$result[$i]->start_date && (int)$result[$i]->end_date) //(int) typecasting is important
			{
				$result[$i]->start_date;
				$result[$i]->end_date;
				$start_date=JFactory::getDate($result[$i]->start_date)->Format(JText::_('Y-m-d'));
				$curr_date=JFactory::getDate()->Format(JText::_('Y-m-d'));
				$end_date=JFactory::getDate($result[$i]->end_date)->Format(JText::_('Y-m-d'));
				//if current date is less than start date, don't show donate button
				if($curr_date<$start_date){
					$flag=1;
				}
				//if current date is more than end date, don't show donate button
				if($curr_date>$end_date){
					$flag=1;
				}
			}?>
			<?php
				//calculate progress bar data
				$recPer=0;
				$goal_amount = (float)$result[$i]->goal_amount;
				if(!empty($result[$i]->amount_received) && $goal_amount>0)
				{
					$recPer=intval ((100 * $result[$i]->amount_received) / $result[$i]->goal_amount);
				}
				if($recPer>100){
					$recPer=100;
					$progresslabel=JText::_('MOD_JGIVE_MORE_THAN_HUNDRED').' %';
				}else{
					$progresslabel=$recPer.'%';
				}
			?>
			<!--if added by Sneha-->
			<?php if($show_field==1 OR $goal_amount==0 ): ?>
				<div id="prgressbar-div" class="progress progress-striped" >
					<div class="bar bar-info" style="width:<?php echo $recPer;?>%;">
						<b class="com_jgive_progress_text"><?php echo $progresslabel;?></b>
					</div>
				</div>
				<div class="clearfix"></div>
			<?php endif; ?>
				<?php
			//Conditions added by Sneha
			if($params->get('show_goal_remaining'))
			{ ?>
				<div class="div_show_amount">

					<!-- goal amount-->
					<div class="div_padding">
						<div class="amount_lable" >
							<?php echo JText::_('MOD_JGIVE_GOAL_AMOUNT');?>
						</div>

						<div class="amount_data">
							<?php
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($result[$i]->goal_amount);
							?>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>
				<?php
			}
			if($params->get('show_received'))
			{ ?>
				<div class="div_show_amount">
					<!-- amount received-->
					<div class="div_padding">
						<div class="amount_lable">
							<?php echo JText::_('MOD_JGIVE_RECEIVED_AMOUNT');?>
						</div>

						<div class="amount_data">
							<?php
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($result[$i]->amount_received);
							?>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>
			<?php
					//Donate button style whene show amount is on
					$donate_button_style='show_amount_on';
			}
			else //Donate button style whene show amount is off
			{
				$donate_button_style='show_amount_off';
			}
			if($params->get('show_goal_remaining'))
			{ ?>
				<div class="div_show_amount">
					<!-- amount remaining-->
					<div class="div_padding">
						<div class="amount_lable">
							<?php echo JText::_('MOD_JGIVE_REMAINING_AMOUNT');?>
						</div>

						<div class="amount_data">
							<?php
							if($result[$i]->amount_received>$result[$i]->goal_amount){
								echo JText::_('MOD_JGIVE_NA');
							}
							else{
								$jgiveFrontendHelper=new jgiveFrontendHelper();
								echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($result[$i]->remaining_amount);
							}
							?>
						</div>
						<div class="clearfix"></div>
					</div>
				</div>
				<?php
			}
				?>

			<div class="<?php echo $donate_button_style; ?>">
				<?php
				if($flag==0)
				{
				?>
					<form action="" method="post" name="campaignForm" id="campaignForm">
						<input type="hidden" name="cid" value="<?php echo $result[$i]->id;?>">
						<button class="btn btn-mini btn-success" type="submit"><?php $result[$i]->type=="donation"? $donate =JText::_('MOD_JGIVE_DONATE') :	$donate=JText::_('MOD_JGIVE_INVEST'); echo $donate;	?></button>
						<input type="hidden" name="option" value="com_jgive">
						<input type="hidden" name="controller" value="donations">
						<input type="hidden" name="task" value="donate">
					</form>

					<?php
				}
				else
				{
					?>
					<input type="button" class="btn btn-mini disabled" value="<?php echo JText::_('MOD_JGIVE_DONATIONS_CLOSED');?>" />
					<?php
				}
				?>
			</div>
		</div>
		<div class="clearfix"></div>
	<br/>
		<?php
	}
	?>
</div>
