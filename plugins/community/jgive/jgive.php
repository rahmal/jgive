<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
}
/*load language file for plugin frontend*/
$lang=JFactory::getLanguage();
$lang->load('plg_community_jgive',JPATH_ADMINISTRATOR);
//load Campaigns helper
$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'campaign.php';
if(!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath );
	JLoader::load('campaignHelper');
}

class plgCommunityjgive extends CApplications
{

	var $_cache=null;
	var $name="jGive Campaigns";
	var $_name='jgive';
	var $_path='';

    function __construct(& $subject, $config)
    {
        parent::__construct($subject, $config);
    }

	function onProfileDisplay()
	{
		$user=CFactory::getRequestUser();//Payee user
		return $this->_getHTML($user->id);
	}

	function _getHTML( $user_id )
	{
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

		$db=JFactory::getDBO();

		//get count of campaigns by profile user
		$query="SELECT COUNT(c.id) AS total
		FROM #__jg_campaigns AS c
		WHERE c.published=1
		AND c.creator_id=".$user_id;
		$db->setQuery($query);
		$total_campaigns=$db->loadResult();

		//get campaigns data by profile user
		$query="SELECT c.id, c.title, c.goal_amount, c.allow_exceed, c.start_date, c.end_date, c.max_donors
		FROM #__jg_campaigns AS c
		WHERE c.published=1
		AND c.creator_id=".$user_id."
		LIMIT 0,".(int)$this->params->get('count');//use count set in plugin
		$db->setQuery($query);
		$data=$db->loadObjectList();

		//set html
		ob_start();
		if($data)
		{
			//load css
			$document=JFactory::getDocument();
			//load component css
			$document->addStyleSheet(JURI::root().'components/com_jgive/assets/css/jgive.css');
			//load techjoomla bootstrapper
			include_once JPATH_ROOT.'/media/techjoomla_strapper/strapper.php';
			TjAkeebaStrapper::bootstrap();
			?>
			<style type="text/css">
				.techjoomla-bootstrap .table th, .techjoomla-bootstrap .table td {
					border:0px!important;
				}
				.com_jgive_progress_text{
					font-size:9px;
					color:#000000;
				}
				.td_amt{
					width:50%;
					font-size:9px;
					text-align: right!important;
				}
				.td_amt_right{
					width:50%;
					font-size:9px;
					text-align: right!important;
				}
			</style>

			<?php
			//load jgive front end helper
			$helperFrontPath=JPATH_SITE.DS."components".DS."com_jgive".DS."helper.php";

			if(!class_exists('jgiveFrontendHelper'))
			{
			  //require_once $path;
			   JLoader::register('jgiveFrontendHelper', $helperFrontPath );
			   JLoader::load('jgiveFrontendHelper');
			}

			$jgiveFrontendHelper=new jgiveFrontendHelper();
			//get items ids
			$this->singleCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
			$this->allCampaignsItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
			//use campaign helper
			$campaignHelper=new campaignHelper();
			$cdata=array();
			foreach($data as $d)//modifiy the data
			{
				//get campaign amounts
				$amounts=$campaignHelper->getCampaignAmounts($d->id);
				$d->amount_received=$amounts['amount_received'];
				$d->remaining_amount=$amounts['remaining_amount'];
				$d->donor_count=$campaignHelper->getCampaignDonorsCount($d->id);
				$cdata[$d->id]['campaign']=$d;//push modified data in cdata
				//get campaign images
				$cdata[$d->id]['images']=$campaignHelper->getCampaignImages($d->id);
			}
			//get currency from component config
			$params=JComponentHelper::getParams('com_jgive');
			$this->currency_code=$params->get('currency');
			?>

			<div class="techjoomla-bootstrap">

					<?php
					foreach($cdata as $c)
					{
						$data=$c['campaign'];
						$images=$c['images'];
						?>
						<table class="table">
						<tr>
							<td colspan="2">
								<div>
									<?php
									// Show the star to know the campaigns marked as Featured
										$title=JText::_('PLG_JGIVE_FEATURED');
										$featured=$campaignHelper->isFeatured( $data->id );
										if($featured)
											echo $imgpath='<img src="'.JURI::root().'components/com_jgive/assets/images/featured.png"  title="'.$title .'">';
									?>

									<a href="<?php echo JURI::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$data->id.'&Itemid='.$this->singleCampaignItemid),strlen(JURI::base(true))+1);?>">
										<b><?php echo $data->title;?></b>
									</a>
								</div>
							</td>
						</tr>

						<tr>
							<td  width="30%">
								<div>
									<?php
										foreach($images as $img)
										{

											if($img->gallery_image==0)
											{
												if(file_exists($img->path))
												{
													echo "<img class='com_jgive_img_48_48' src='".JURI::base().$img->path."' />";
													break;//print only 1 image
												}
												else
												{
													$path='images'.DS.'jGive'.DS;
													//get original image name to find it resize images (S,M,L)
													$org_file_after_removing_path=trim(str_replace($path,'S_',$img->path));
													$img_link= JUri::base().$path.$org_file_after_removing_path;

													echo "<img class='img-polaroid com_jgive_img_96_96'src='".$img_link."' />";
													break;//print only 1 image
												}
											}
										}
									?>
								</div>

								<br/>
								<?php
								//check if exeeding goal amount is allowed
								//if not check for received amount to decide about hiding donate button
								$flag=0;
								if($data->allow_exceed==0 && ((isset($show_field) && $show_field==1) || ((isset($goal_amount)) && $goal_amount==0 ))) //condition changed by Sneha
								{
									 if($data->amount_received>=$data->goal_amount){
										 $flag=1;
									 }
								}

								if($data->max_donors>0)
								{
									 if($data->donor_count >= $data->max_donors){
										 $flag=1;
									 }
								}

								//if both start date, and end date are present
								if((int)$data->start_date && (int)$data->end_date) //(int) typecasting is important
								{
									$start_date=JFactory::getDate($data->start_date)->Format(JText::_('Y-m-d'));
									$curr_date=JFactory::getDate()->Format(JText::_('Y-m-d'));
									$end_date=JFactory::getDate($data->end_date)->Format(JText::_('Y-m-d'));
									//if current date is less than start date, don't show donate button
									if($curr_date<$start_date){
										$flag=1;
									}
									//if current date is more than end date, don't show donate button
									if($curr_date>$end_date){
										$flag=1;
									}
								}

								if($flag==0)
								{
								?>
									<div>
										<form action="" method="post" name="adminForm" id="adminForm">
											<input type="hidden" name="cid" value="<?php echo $data->id;?>">
											<button class="btn btn-mini btn-success" type="submit"><?php echo JText::_('PLG_JGIVE_BUTTON_DONATE'); ?></button>
											<input type="hidden" name="option" value="com_jgive">
											<input type="hidden" name="controller" value="donations">
											<input type="hidden" name="task" value="donate">
										</form>
									</div>
									<?php
								}
								else
								{
									?>
									<input type="button" class="btn btn-mini disabled" value="<?php echo JText::_('PLG_JGIVE_DONATIONS_CLOSED');?>" />
									<?php
								}
								?>
							</td>

							<td  width="70%">

								<?php
									//calculate progress bar data
									$recPer=intval ((100 * $data->amount_received) / $data->goal_amount);
									//$remPer= (100 - $recPer);
									if($recPer>100){
										$recPer=100;
										$progresslabel=JText::_('PLG_JGIVE_MORE_THAN_HUNDRED').' %';
									}else{
										$progresslabel=$recPer.'%';
									}
								?>
								<!--if added by Sneha-->
								<?php if((isset($show_field) && $show_field==1) OR ((isset($goal_amount) && $goal_amount==0 ))): ?>
									<div class="progress progress-striped" >
										<div class="bar bar-info" style="width:<?php echo $recPer;?>%;">
											<b class="com_jgive_progress_text"><?php echo $progresslabel;?></b>
										</div>
										<!--<div class="bar" style="width:<?php echo $remPer;?>%;"></div>-->
									</div>
								<?php endif; ?>
								<div width="100%">
									<table class="table">

										<!--if added by Sneha-->
										<?php if((isset($show_field) && $show_field==1) || ((isset($goal_amount) && $goal_amount==0 ))):

										?>
											<tr>
												<td class="td_amt">
													<?php echo JText::_('PLG_JGIVE_GOAL_AMOUNT');?>
												</td>
												<td class="td_amt_right">
													<?php
													$jgiveFrontendHelper=new jgiveFrontendHelper();
													echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($data->goal_amount);
													?>
												</td>
											</tr>
										<?php endif; ?>
										<tr>
											<td class="td_amt">
												<?php echo JText::_('PLG_JGIVE_AMOUNT_RECEIVED');?>
											</td>
											<td class="td_amt_right">
												<?php
													$jgiveFrontendHelper=new jgiveFrontendHelper();
													echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($data->amount_received);
													?>
											</td>
										</tr>
										<!--if added by Sneha-->
										<?php if((isset($show_field) && $show_field==1) OR ((isset($goal_amount) && $goal_amount==0 ))): ?>
											<tr>
												<td class="td_amt">
													<?php echo JText::_('PLG_JGIVE_REMAINING_AMOUNT');?>
												</td>
												<td class="td_amt_right">
													<?php
													if($data->amount_received>$data->goal_amount){
														echo JText::_('PLG_JGIVE_NA');
													}
													else{
														$jgiveFrontendHelper=new jgiveFrontendHelper();
														echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($data->remaining_amount);
													}
													?>
												</td>
											</tr>
										<?php endif; ?>
									</table>
								</div>
							</td>
						</tr>
						</table>
						<?php
					}//end of loop
						?>


				<?php
					//show link if all campaigns are not shown
					if($total_campaigns && $total_campaigns > $this->params->get('count'))
					{
						?>
						<div class="app-box-footer">
							<a href="<?php echo JURI::root().substr(JRoute::_('index.php?option=com_jgive&view=campaigns&layout=all&user_filter='.$user_id.'&Itemid='.$this->allCampaignsItemid),strlen(JURI::base(true))+1);?>">
								<?php echo JText::_('PLG_JGIVE_VIEW_ALL_CAMPAIGNS_FROM_USER');?> (<?php echo $total_campaigns;?>)
							</a>
						</div>
						<?php
					}
				?>
			</div> <!--end of bootstrap div-->
		<?php
		}
		$html=ob_get_contents();
		ob_end_clean();
		return $html;
	}//get html ends
}
