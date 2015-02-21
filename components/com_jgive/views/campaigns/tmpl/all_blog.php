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
	JHtml::_('formbehavior.chosen', 'select');
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
			case 'max_donation':
				$max_donation_cnf=1;
			break;

			case 'long_desc':
				$long_desc_cnf=1;
			break;

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
<?php if(JVERSION<3.0): ?>
<div class="techjoomla-bootstrap">
<?php endif;?>
	<div id="all" class="row-fluid">
		<div class="span12">
			<div class="row-fluid">
				<div class="span12">
					<!--page header-->
					<h2 class="componentheading">
						<?php echo JText::_('COM_JGIVE_ALL_CAMPAIGNS');?>
					</h2>
					<hr/>
				</div><!--span12-->
			</div><!--row-fluid-->

			<form action="" method="post" name="adminForm3" id="adminForm3">
				<input type="hidden" name="option" value="com_jgive" />
				<input type="hidden" name="view" value="campaigns" />
				<input type="hidden" name="layout" value="all" />

			<?php 
			if ($this->params->get('show_filters') && $this->params->get('show_search_filter'))
			{ ?>
				<!--Added by Sneha for free text search-->
				<div class="row-fluid">
					<div class="pull-right">
						<input type="text" placeholder="Enter name.." name="search_list" id="search_list" value="<?php echo $this->lists['search_list']; ?>" class="input-medium pull-left" onchange="document.adminForm.submit();" />
						<div class="btn-group pull-right hidden-phone">
							<button type="button" onclick="this.form.submit();" class="btn tip hasTooltip" data-original-title="Search"><i class="icon-search"></i></button>
							<button onclick="document.id('search_list').value='';this.form.submit();" type="button" class="btn tip hasTooltip" data-original-title="Clear"><i class="icon-remove"></i></button>
						</div>
					</div>
				</div>
				<!--Added by Sneha for free text search-->	
			<?php
			} ?>

				<div class="row-fluid">
					<div class="span12">
						<?php
						if($this->params->get('show_sorting_options') || $this->params->get('show_filters'))
						{
							if($this->params->get('show_sorting_options'))
							{
								?>
								<div class="">
									<span><strong><?php echo JText::_('COM_JGIVE_ORDERING_OPTIONS');?></strong></span>
									<br/>

									<?php
										echo JHtml::_('select.genericlist', $this->ordering_options, "filter_order", ' size="1"
										onchange="this.form.submit();" name="filter_order"',"value", "text", $this->lists['filter_order']);
									?>
									&nbsp;
									<?php
										echo JHtml::_('select.genericlist', $this->ordering_direction_options, "filter_order_Dir", ' size="1"
										onchange="this.form.submit();" name="filter_order_Dir"',"value", "text", $this->lists['filter_order_Dir']);
									?>
								</div>
								<?php
							}

							if($this->params->get('show_filters'))
							{
								?>
								<div class="">
									<span><strong><?php echo JText::_('COM_JGIVE_FILTER_CAMPAIGNS');?></strong></span>
									<br/>

									<?php
									$type_filter_on=0;	//type

									$campaignHelper=new campaignHelper();
									$campaign_type=$campaignHelper->filedToShowOrHide('campaign_type');

									if($this->params->get('show_type_filter') AND $campaign_type)
									{
										$type_filter_on=1;
										echo JHtml::_('select.genericlist', $this->campaign_type_filter_options, "filter_campaign_type", ' size="1"
										onchange="this.form.submit();" name="filter_campaign_type"',"value", "text", $this->lists['filter_campaign_type']);
										?>
									&nbsp;
										<?php
									}
									?>

									<?php
									$promoter_filter_on=0;
									if($this->params->get('show_promoter_filter'))
									{
										$promoter_filter_on=1;
										echo JHtml::_('select.genericlist', $this->user_filter_options, "filter_user", ' size="1"
										onchange="this.form.submit();" name="filter_user"',"value", "text", $this->lists['filter_user']);
										?>
									&nbsp;
										<?php
									}
									else
									{
										$input=JFactory::getApplication()->input;
										$filter_user=$input->get('filter_user','','INT');

										if($filter_user)
										{
											$promoter_filter_on=1;
											echo JHtml::_('select.genericlist', $this->user_filter_options, "filter_user", ' size="1"
											onchange="this.form.submit();" name="filter_user"',"value", "text", $this->lists['filter_user']);
											?>
										&nbsp;
											<?php
										}
									}
									?>
									<?php
									//if promoter & type & organization filter is on then
									// line 1 type promoter
									// line 2 category & org filter
									$org_filter=0;
									if($this->params->get('show_org_ind_type_filter'))
										$org_filter=1;

									if($type_filter_on AND $promoter_filter_on AND $org_filter)
									{		echo '<br/>';
										echo '<div style="margin-top:1%">'; // start div org
									}
									?>

									<?php
									//category
									if($this->params->get('show_category_filter'))
									{
										 echo JHtml::_('select.genericlist', $this->cat_options, "filter_campaign_cat", 'class="" size="1"
										onchange="this.form.submit();" name="filter_campaign_cat"',"value", "text",$this->lists['filter_campaign_cat']);
									}
									?>

									<?php
									// add space inbetween category & organization filter
									$org_filter=0;
									if($this->params->get('show_org_ind_type_filter'))
										$org_filter=1;

									if($org_filter){
										?>
									&nbsp;
										<?php
									}
									?>

									<?php
									//organization_individual_type
									if($this->params->get('show_org_ind_type_filter'))
									{
										 echo JHtml::_('select.genericlist', $this->filter_org_ind_type, "filter_org_ind_type", 'class="" size="1"
										onchange="this.form.submit();" name="filter_org_ind_type"',"value", "text",$this->lists['filter_org_ind_type']);

										if($type_filter_on AND $promoter_filter_on AND $org_filter)
										{
											echo '</div>'; // end div org
										}
									}?>



									<?php
										//country state city
									if($this->params->get('show_place_filter'))
									{?>
									<div style="margin-top:1%">
									<?php
										echo JHtml::_('select.genericlist', $this->countryoption, "campaign_countries", ' size="1"
										onchange="this.form.submit();" name="campaign_countries"',"value", "text", $this->lists['campaign_countries']);
										//state
										?>
										&nbsp;
										<?php
											echo JHtml::_('select.genericlist', $this->campaign_states, "campaign_states", ' size="1"
											onchange="this.form.submit();" name="campaign_states"',"value", "text", $this->lists['campaign_states']);
											//city
										?>
										&nbsp;
										<?php
											echo JHtml::_('select.genericlist', $this->campaign_city, "campaign_city", ' size="1"
											onchange="this.form.submit();" name="campaign_city"',"value", "text", $this->lists['campaign_city']);
									?>
									</div>
									<?php }?>

								</div>
							<?php
							}
							if(JVERSION >= 3.0 )
							{
								?>
								<br/>
								<div class="btn-group pull-right hidden-phone">
									<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
									<?php echo $this->pagination->getLimitBox(); ?>
								</div>
								<?php
							}
						}
						?>
						<div class="com_jgive_clear_both"></div>
					</div><!--span12-->
				</div><!--row-fluid-->
				<hr/>
			</form>

			<div class="row-fluid">
				<div class="span12">
					<?php
					foreach($this->data as $cdata)
					{
						?>
						<div class="row-fluid">
							<!--<div class="com_jgive_border">-->
							<div class="span12 com_jgive_border">
								<div class="row-fluid">
									<div class="span12">
										<div class='com_jgive_campaign_title'>
											<h4>
												<?php
												// Show the star to know the campaigns marked as Featured
													$title=JText::_('COM_JGIVE_FEATURED');
													$campaignHelper=new campaignHelper();
													$result=$campaignHelper->isFeatured( $cdata['campaign']->id );
													echo $result ? $imgpath='<img src="'.JUri::root().'components/com_jgive/assets/images/featured.png"  title="'.$title .'">':'';
												?>
												<a href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$cdata['campaign']->id.'&Itemid='.$this->singleCampaignItemid),strlen(JUri::base(true))+1);?>">
													<?php echo $cdata['campaign']->title;?>
												</a>
											</h4>
											<?php
											//generate unique ad url for social sharing

											require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."integrations.php");
											$ad_url='index.php?option=com_jgive&view=campaign&layout=single&cid='.$cdata['campaign']->id;
											//Integration with Jlike
											if(file_exists(JPATH_SITE.'/'.'components/com_jlike/helper.php'))
											{
												$jlikehtml=jgiveFrontendHelper::DisplayjlikeButton($ad_url,$cdata['campaign']->id,$cdata['campaign']->title);
												if($jlikehtml)
													echo $jlikehtml;

											}
											//Integration with Jlike

											$ad_url=JUri::root().substr(JRoute::_($ad_url),strlen(JUri::base(true))+1);


											$add_this_share='';
											$params=JComponentHelper::getParams('com_jgive');
											$pid=$params->get('addthis_publishid','GET','STRING');


											?>
											<br>
											<?php



											if($params->get('social_sharing'))
											{
												if($params->get('social_shring_type')=='addthis')
												{
													$add_this_share='
													<!-- AddThis Button BEGIN -->
													<div class="addthis_toolbox addthis_default_style">

													<a class="addthis_button_facebook_like" fb:like:layout="button_count" class="addthis_button" addthis:url="'.$ad_url.'"></a>
													<a class="addthis_button_google_plusone" g:plusone:size="medium" class="addthis_button" addthis:url="'.$ad_url.'"></a>
													<a class="addthis_button_tweet" class="addthis_button" addthis:url="'.$ad_url.'"></a>
													<a class="addthis_button_pinterest_pinit" class="addthis_button" addthis:url="'.$ad_url.'"></a>
													<a class="addthis_counter addthis_pill_style" class="addthis_button" addthis:url="'.$ad_url.'"></a>
													</div>
													<script type="text/javascript">
														var addthis_config ={ pubid: "'.$pid.'"};
													</script>
													<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid="'.$pid.'"></script>
													<!-- AddThis Button END -->' ;

													$add_this_js='http://s7.addthis.com/js/300/addthis_widget.js';
													$integrationsHelper=new integrationsHelper();
													$integrationsHelper->loadScriptOnce($add_this_js);
													//output all social sharing buttons
													foreach($cdata['images'] as $img)
													{
														break;
													}



													if(file_exists($img->path))
													{
														$image_linnk=$img->path;
													}
													else
													{
														$path='images'.DS.'jGive'.DS;
														//get original image name to find it resize images (S,M,L)
														$org_file_after_removing_path=trim(str_replace($path,'M_',$img->path));
														$image_linnk=$path.$org_file_after_removing_path; 
													}

													echo' <div id="rr" style="">
														<div class="social_share_container">
														<meta property="og:title" content="The Rock"/>
														<div class="social_share_container_inner" onmouseover="onmouseoverfn(\''.$cdata['campaign']->id.'\',\''.$cdata['campaign']->title.'\',\''.JUri::base().$image_linnk.'\' )">'.
															$add_this_share.
														'</div>
													</div>
													</div>
													';
												}
												else
												{
													echo '<div class="com_jgive_horizontal_social_buttons">';
													echo '<div class="com_jgive_float_left">
															<div class="fb-like" data-href="'.$ad_url.'" data-send="true" data-layout="button_count" data-width="450" data-show-faces="true"></div>
														</div>';
													echo '<div class="com_jgive_float_left">
															&nbsp; <div class="g-plus" data-action="share" data-annotation="bubble" data-href="'.$ad_url.'"></div>
														</div>';
													echo '<div class="com_jgive_float_left">
															&nbsp; <a href="https://twitter.com/share" class="twitter-share-button" data-url="'.$ad_url.'" data-counturl="'.$ad_url.'"  data-lang="en">Tweet</a>
														</div>';
													echo '</div>
														<div class="com_jgive_clear_both"></div>';
												}
											}
											?>

										</div>
									</div><!--span12-->
								</div><!--row-fluid-->

									<hr/>
								<div class="row-fluid">
									<div class="span2 com_jgive_campaign_image_block">
										<?php
											foreach($cdata['images'] as $img)
											{

												if($img->gallery_image==0)
												{
													if(file_exists($img->path))
													{
														echo "<img class='img-polaroid com_jgive_img_96_96'src='".JUri::base().$img->path."' />";
														break;//print only 1 image
													}
													else
													{
														$path='images'.DS.'jGive'.DS;
														//get original image name to find it resize images (S,M,L)
														$org_file_after_removing_path=trim(str_replace($path,'M_',$img->path));
														$img_link= JUri::base().$path.$org_file_after_removing_path; 

														echo "<img class='img-polaroid com_jgive_img_96_96'src='".$img_link."' />";
														break;//print only 1 image

													}
												}

											}
										?>
									</div><!--span2-->

									<div class="span9 offset1 com_jgive_justify">
										<?php echo nl2br($cdata['campaign']->short_description);?>
									</div><!--span9-->

								</div><!--row-fluid-->

								<div class="row-fluid">
									<div class="span12">
										<table class="table table-bordered">
											<tr>
												<!--if condition added by Sneha, to goal amount-->
												<?php if($show_field==1 OR $goal_amount==0 ): ?>
													<td width="25%" class="com_jgive_td_amt com_jgive_td_center">
														<?php echo JText::_('COM_JGIVE_GOAL_AMOUNT');?>
													</td>

													<td width="25%" class="com_jgive_td_amt_right com_jgive_td_center">
														<?php 
														$jgiveFrontendHelper=new jgiveFrontendHelper();
														$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cdata['campaign']->goal_amount);
														echo $diplay_amount_with_format;
														?>
													</td>
												<?php endif; ?>
												<!--End if added by Sneha-->
												<td width="50%" class="com_jgive_td_center" rowspan="5">
													<div class="row-fluid">
														<div class="span12">
														<!--<div>-->
															<?php
																$css_start_date='';
																$css_end_date='';
																$css_max_donors='';
																//check if exeeding goal amount is allowed
																//if not check for received amount to decide about hiding donate button
																$flag=0;
																$date_expire=0;
																if($cdata['campaign']->allow_exceed==0)
																{
																	 if($cdata['campaign']->amount_received>=$cdata['campaign']->goal_amount){
																		 $flag=1;
																	 }
																}

																if($cdata['campaign']->max_donors>0)
																{
																	 if($cdata['campaign']->donor_count >= $cdata['campaign']->max_donors){
																		 $flag=1;
																		 $css_max_donors="class='text-error'";
																	 }
																}

																//if both start date, and end date are present
																$curr_date='';
																if((int)$cdata['campaign']->start_date && (int)$cdata['campaign']->end_date) //(int) typecasting is important
																{
																	$start_date=JFactory::getDate($cdata['campaign']->start_date)->Format(JText::_('Y-m-d'));
																	$end_date=JFactory::getDate($cdata['campaign']->end_date)->Format(JText::_('Y-m-d'));
																	$curr_date=JFactory::getDate()->Format(JText::_('Y-m-d'));
																	//if current date is less than start date, don't show donate button
																	if($curr_date<$start_date){
																		$flag=1;
																		$css_start_date="class='text-error'";
																	}
																	//if current date is more than end date, don't show donate button
																	if($curr_date>$end_date){
																		$flag=1;
																		$date_expire=1;
																		$css_end_date="class='text-error'";
																	}
																	?>

																	<p <?php echo $css_start_date;?>>
																		<strong>
																			<?php echo JText::_('COM_JGIVE_START_DATE');?>:
																		</strong>
																		<?php echo JFactory::getDate($cdata['campaign']->start_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));?>
																	</p>

																	<p <?php echo $css_end_date;?>>
																		<strong>
																			<?php echo JText::_('COM_JGIVE_END_DATE');?>:
																		</strong>
																		<?php echo JFactory::getDate($cdata['campaign']->end_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));?>
																	</p>

																<?php
															}
															?>
														</div><!--span12-->
													</div><!--row-fluid-->


													<?php
														//calculate progress bar data
														$recPer=intval ((100 * $cdata['campaign']->amount_received) / $cdata['campaign']->goal_amount);
														if($recPer>100){
															$recPer=100;
															$progresslabel=JText::_('COM_JGIVE_MORE_THAN_HUNDRED').' %';
														}else{
															$progresslabel=$recPer.'%';
														}
													?>
													<!--if condition added by Sneha, to hide progress bar and percent-->
													<?php if($show_field==1 OR $goal_amount==0 ): ?>
														<div class="row-fluid">
															<?php if(JVERSION>=3.0)
																	$span='';
																	else
																	$span='span12';
															?>
															<div class="<?php echo $span;?>">
																<div class="progress progress-striped" >
																	<div class="bar bar-info" style="width:<?php echo $recPer;?>%;">
																		<b class="com_jgive_progress_text"><?php echo $progresslabel;?></b>
																	</div>
																</div>
															</div><!--span12-->
														</div><!--row-fluid-->
													<?php endif; ?>
													<div class="row-fluid">
														<div class="span12">
															<?php
															if($flag==0)
															{
																?>
																<form action="index.php" method="post" name="adminForm" id="adminForm">
																	<input type="hidden" name="cid" value="<?php echo $cdata['campaign']->id; ?>" />
																	<button class="btn btn-success com_jgive_button" type="submit">
																		<?php
																			echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_BUTTON_DONATE') : JText::_('COM_JGIVE_BUTTON_INVEST'));
																		?>
																	</button>
																	<a class="btn btn-primary com_jgive_button" href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$cdata['campaign']->id.'&Itemid='.$this->singleCampaignItemid),strlen(JUri::base(true))+1);?>">
																		<?php echo JText::_('COM_JGIVE_READ_MORE');?>
																	</a>
																	<input type="hidden" name="option" value="com_jgive" />
																	<input type="hidden" name="controller" value="donations" />
																	<input type="hidden" name="task" value="donate" />
																</form>
																<?php
															}
															else
															{
																?>
																<input type="button" class="btn disabled com_jgive_button" value="<?php
																	echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATIONS_CLOSED') : JText::_('COM_JGIVE_INVESTMENTS_CLOSED'));
																?>"/>
																<a class="btn btn-primary com_jgive_button" href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$cdata['campaign']->id.'&Itemid='.$this->singleCampaignItemid),strlen(JUri::base(true))+1);?>">
																	<?php echo JText::_('COM_JGIVE_READ_MORE');?>
																</a>
																<?php
															}
															?>
														</div><!--span12-->
													</div><!--row-fluid-->
												</td>
											</tr>

											<tr>
												<td width="25%" class="com_jgive_td_amt com_jgive_td_center">
													<?php echo JText::_('COM_JGIVE_AMOUNT_RECEIVED');?>
												</td>
												<td width="25%" class="com_jgive_td_amt_right com_jgive_td_center">
													<?php 
													$jgiveFrontendHelper=new jgiveFrontendHelper();
													$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cdata['campaign']->amount_received);
													echo $diplay_amount_with_format;
													?>
												</td>
											</tr>

											<tr>
												<td width="25%" class="com_jgive_td_amt com_jgive_td_center">
													<?php echo JText::_('COM_JGIVE_REMAINING_AMOUNT');?>
												</td>
												<td width="25%" class="com_jgive_td_amt_right com_jgive_td_center">
													<?php
													if($cdata['campaign']->amount_received>$cdata['campaign']->goal_amount){
														echo JText::_('COM_JGIVE_NA');
													}
													else{

													$jgiveFrontendHelper=new jgiveFrontendHelper();
													$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cdata['campaign']->remaining_amount);
													echo $diplay_amount_with_format;


														//echo $cdata['campaign']->remaining_amount.' '.$this->currency_code;
													}
													?>
												</td>
											</tr>
											<tr>
												<td width="25%" class="com_jgive_td_amt com_jgive_td_center">
													<?php
														$time_curr_date=strtotime($curr_date);
														$time_end_date=strtotime($cdata['campaign']->end_date);
														$interval = ($time_end_date-$time_curr_date);
														$days_left=floor($interval/(60*60*24));
														if((int)($time_curr_date) && (int)($time_end_date))
														{
															echo JText::_('COM_JGIVE_DAYS_LEFT');
														}
													?>
												</td>
												<td width="25%" class="com_jgive_td_amt_right com_jgive_td_center">
													<?php
														if($date_expire)
															echo JText::_('COM_JGIVE_NA');
														else if((int)($time_curr_date) && (int)($time_end_date))
														{
															echo $days_left>0 ?  $days_left: JText::_('COM_JGIVE_NA');
														}
													?>
												</td>
											</tr>
											<tr>
												<td width="25%" class="com_jgive_td_amt com_jgive_td_center" colspan="2">
													<?php
													echo JText::_('COM_JGIVE_TOTAL').(($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATIONS') : JText::_('COM_JGIVE_INVESTMENTS'));

													if($show_field==1 OR $max_donation_cnf==0 )
														echo ' / '. JText::_('COM_JGIVE_MAX_ALLOWED').(($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATIONS') : JText::_('COM_JGIVE_INVESTMENTS'));

													?>
													<br/>
													<span <?php echo $css_max_donors;?>>
														<?php
														echo $cdata['campaign']->donor_count;

														if($show_field==1 OR $max_donation_cnf==0 )
														{
															if($cdata['campaign']->max_donors>0)
																echo ' / '.$cdata['campaign']->max_donors;
															else
																echo ' / '. JText::_('COM_JGIVE_NA');
														}
														?>
													</span>
												</td>
											</tr>

										</table>
									</div><!--span12-->
								</div><!--row-fluid-->

							</div><!--span12-->
						</div><!--row-fluid-->

						<?php
					}
						?>
				</div><!--span12-->
			</div><!--row-fluid-->

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
