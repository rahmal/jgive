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
$cdata=$this->cdata;
if(JVERSION>=3.0)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');
}


echo '<div id="fb-root"></div>';
$fblike_tweet = JUri::root().'components/com_jgive/assets/javascript/fblike.js';
echo "<script type='text/javascript' src='".$fblike_tweet."'></script>";

$document=JFactory::getDocument();
$document->addStyleSheet(JUri::root().'components/com_jgive/assets/css/magnific-popup.css');
$magific = JUri::root().'components/com_jgive/assets/javascript/jquery.magnific-popup.min.js';
$document->addScript($magific);
//echo "<script type='text/javascript' src='".$magific."'></script>";

$params=JComponentHelper::getParams('com_jgive');
$donor_records_config = $params->get('donor_records');

if(!$donor_records_config)
{
	$donor_records_config = 10;
}

$show_field=0;
$max_donation_cnf=0;
$long_desc_cnf=0;
$show_public_cnf=0;
$address_cnf=0;
$address2_cnf=0;
$zip_cnf=0;
$phone_cnf=0;
$group_name_cnf=0;
$website_address_cnf=0;
$give_back_cnf=0;
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

			case 'show_public':
				$show_public_cnf=1;
			break;

			case 'address':
				$address_cnf=1;
			break;

			case 'address2':
				$address2_cnf=1;
			break;

			case 'zip':
				$zip_cnf=1;
			break;

			case 'phone':
				$phone_cnf=1;
			break;

			case 'group_name':
				$group_name_cnf=1;
			break;

			case 'website_address':
				$website_address_cnf=1;
			break;

			case 'give_back':
				$give_back_cnf=1;
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

//print_r($cdata);
?>

<script type="text/javascript">

var imagesrc;
	techjoomla.jQuery(document).ready(function() {
		techjoomla.jQuery('.popup-gallery').magnificPopup({
			delegate: 'a',
			type: 'image',
			tLoading: 'Loading image #%curr%...',
			mainClass: 'mfp-img-mobile',
			gallery: {
				enabled: true,
				navigateByImgClick: true,
				preload: [0,1] // Will preload 0 - before current, and 1 after the current image
			},
			image: {
				tError: '<a href="%url%">The image #%curr%</a> could not be loaded.',
				titleSrc: function(item) {
					return item.el.attr('title') + '<small></small>';
				}
			}
		});
	});

	function callDonateForm(url)
	{
		window.location.assign(url);
	}
</script>

<?php
	//jomsocial toolbar
	echo $this->jomsocailToolbarHtml;

?>

<div class="techjoomla-bootstrap">
	<div id="single" class="row-fluid">
		<div class="span12">
			<div class="row-fluid">
				<div class="span12">

					<!--page header-->
					<h2 class="componentheading">

						<?php
						// Show the star to know the campaigns marked as Featured
							$title=JText::_('COM_JGIVE_FEATURED');
							$campaignHelper=new campaignHelper();
							$result=$campaignHelper->isFeatured( $cdata['campaign']->id );
							echo $result ? $imgpath='<img src="'.JUri::root().'components/com_jgive/assets/images/featured.png"  title="'.$title .'">':'';
						?>

						<?php

							echo $cdata['campaign']->title;
							//generate unique ad url for social sharing
							require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."integrations.php");

							$ad_url='index.php?option=com_jgive&view=campaign&layout=single&cid='.$cdata['campaign']->id;
							$ad_url=JUri::root().substr(JRoute::_($ad_url),strlen(JUri::base(true))+1);
							$add_this_share='';

							$pid=$params->get('addthis_publishid','GET','STRING');
						 ?>
						</h2>

						<div class="well">
							<!-- Campaign Categories-->
							<?php if(!empty($cdata['campaign']->catname)) { ?>
								<h6>
									<?php echo JText::_('COM_JGIVE_CATEGORY');?>
									<?php
									$cat_url='index.php?option=com_jgive&view=campaigns&layout=all&filter_campaign_cat='.$cdata['campaign']->category_id;
									$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);
									 echo ' : <a href="'.$cat_url.'">'. $cdata['campaign']->catname.'</a>';
									?>
								</h6>
							<?php } ?>
							<!-- org ind type-->
							<?php if(!empty($cdata['campaign']->org_ind_type)) { ?>
								<h6>
									<?php echo JText::_('COM_JGIVE_ORG_IND_TYPE');?>
									<?php
									$org_url='index.php?option=com_jgive&view=campaigns&layout=all&filter_org_ind_type='.$cdata['campaign']->org_ind_type;
									$org_url=JUri::root().substr(JRoute::_($org_url),strlen(JUri::base(true))+1);
									if($cdata['campaign']->org_ind_type=='non_profit')
										$org_ind_type=JText::_('COM_JGIVE_ORG_NON_PROFIT');
									else if($cdata['campaign']->org_ind_type=='self_help')
										$org_ind_type=JText::_('COM_JGIVE_SELF_HELP');
									else
										$org_ind_type=JText::_('COM_JGIVE_SELF_INDIVIDUALS');
									 echo ' : <a href="'.$org_url.'">'.$org_ind_type.'</a>';
									?>
								</h6>
							<?php } ?>


						<?php

						$doc = JFactory::getDocument();
						foreach($cdata['images'] as $img)
						{
							break;
						}

						$path='images'.DS.'jGive'.DS;
						//get original image name to find it resize images (S,M,L)
						$org_file_after_removing_path=trim(str_replace($path,'',$img->path));

						// set metadata
						$config=JFactory::getConfig();
						if(JVERSION>=3.0)
							$site_name=$config->get( 'sitename' );
						else
							$site_name=$config->getvalue( 'config.sitename' );

						$doc->addCustomTag( '<meta property="og:title" content="'.$cdata['campaign']->title.'" />' );
						$doc->addCustomTag( '<meta property="og:image" content="'.JUri::base().$path.'S_'.$org_file_after_removing_path.'" />' );
						$doc->addCustomTag( '<meta property="og:url" content="'.$ad_url.'" />' );
						$doc->addCustomTag( '<meta property="og:description" content="'.nl2br($cdata['campaign']->short_description).'" />' );
						$doc->addCustomTag( '<meta property="og:site_name" content="'.$site_name.'" />' );
						$doc->addCustomTag( '<meta property="og:type" content="article" />' );

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
								echo' <div id="rr" style="">
									<div class="social_share_container">
									<div class="social_share_container_inner">'.
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
										<div class="fb-like" data-href="'.$ad_url.'" data-send="true" data-layout="standard" data-width="450" data-show-faces="true"></div>
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

			<div class="row-fluid">
				<div class="span8">

					<ul id="myTab" class="nav nav-tabs">

							<?php
						$long_desc_hidden=0;
						//if($show_field==1 OR $long_desc_cnf==0 ):
						$long_desc_hidden=1;
							?>
							<li class="active">
								<a href="#camp_details" data-toggle="tab"><?php echo JText::_('COM_JGIVE_CAMPAIGN_DETAILS');?></a>
							</li>
						<?php //endif;?>

						<?php
						//restrict access to view donations
						if($params->get('img_gallery'))
						{
							?>
							<li>
								<a href="#camp_gallery_images" data-toggle="tab">
									<?php
										echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_PHOTO_GALLERY') : JText::_('COM_JGIVE_IMG_GALLERY_DESC'));
									?>
								</a>
							</li>
							<?php
						}
						?>

						<?php
						//restrict access to view donations
						if($this->logged_userid==$cdata['campaign']->creator_id)
						{
							?>
							<li>
								<a href="#camp_donors_report" data-toggle="tab">
									<?php
										echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONORS_REPORT') : JText::_('COM_JGIVE_INVESTORS_REPORT'));
									?>
								</a>
							</li>
							<?php
						}
						?>

						<li>
							<a href="#camp_promoter" data-toggle="tab"><?php echo JText::_('COM_JGIVE_CAMPAIGN_PROMOTER');?></a>
						</li>
							<?php
						$class='';
						//if($long_desc_hidden==0)
						{
							//$class='class="active"';
						}
							?>
						<li <?php echo $class;?> >
							<a href="#camp_donors" data-toggle="tab">
								<?php
									echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONORS') : JText::_('COM_JGIVE_INVESTORS'));
								?>
							</a>
						</li>

					</ul>

				</div><!--span8-->
			</div><!--row-fluid-->

			<div class="row-fluid">
				<div class="span8">
					<div id="myTabContent" class="tab-content">
								<!--tab1-->

								<div class="tab-pane fade in active" id="camp_details">
									<div class="row-fluid">
										<div class="span12">

											<div>&nbsp;</div>
											<div id="image_video">
												<?php
												if(($cdata['images'][0]->video_img==0) || (empty($cdata['images'][0]->video_url)))
												{
													foreach($cdata['images'] as $img)
													{
														if($img->gallery_image==0)
														{
															$path='images'.DS.'jGive'.DS;
															//get original image name to find it resize images (S,M,L)
															$org_file_after_removing_path=trim(str_replace($path,'',$img->path));

															if(file_exists($img->path)) //if loop for old version compability (where img resize not available means no L , M ,S before image name)
															{
																echo "<img class='img-polaroid com_jgive_img_410_610' src='".JUri::base().$img->path."'/>";
																break;
															}
															else
															{
																echo "<img class='img-polaroid com_jgive_img_410_610' src='".JUri::base().$path.'L_'.$org_file_after_removing_path."'/>";
																break;
															}
															//print only 1 image
														}

													}
												}
												else
												{
												?>
													<iframe frameborder="0" width="600" height="413" src="<?php echo $cdata['images'][0]->video_url; ?>"></iframe>
												<?php
												}
												?>
											</div>

											<div>&nbsp;</div>
										<?php if($show_field==1 OR $long_desc_cnf==0 ): ?>
											<div class="com_jgive_border">

												<p class="com_jgive_justify">
													<?php echo $cdata['campaign']->long_description; ?>
												</p>
											</div>
										<?php endif;?>
										</div><!--span12-->
									</div><!--row-fluid-->
								</div>


							<?php if($params->get('img_gallery')): ?>
								<div class="tab-pane fade in" id="camp_gallery_images">
									<div class="row-fluid">
										<div class="span12">
											<div class="com_jgive_border">
												<ul class="thumbnails popup-gallery">
												<?php
													$img_path_array=array();
													$i=0;
													foreach($cdata['images'] as $img)
													{
														$img_path_array[]=JUri::base().$img->path;
														if($img->gallery_image==1)
														{
														?>
														 <li class="span3" >
															<a href="<?php echo JUri::base().$img->path; ?>" title="">
															<img class='img-rounded com_jgive_img_128_128' src="<?php echo JUri::base().$img->path; ?>" width="75" height="75">
															</a>
														</li>
														<?php
														$i++;
														}
													}
													//print_r($img_path_array);
												?>
												</ul>
											</div>
										</div><!--span12-->
									</div><!--row-fluid-->
								</div>
							<?php endif;?>

								<?php
								//restrict access to view donations
								if($this->logged_userid==$cdata['campaign']->creator_id)
								{
									?>
									<!--tab3-->
									<div class="tab-pane fade" id="camp_donors_report">

										<div class="row-fluid">
											<div class="span12">

												<div class="com_jgive_border">
													<?php
														/*
														//restrict access to view donations
														if($this->logged_userid==$cdata['campaign']->creator_id || $cdata['campaign']->allow_view_donations)
														{
														*/
														if(count($cdata['donors']) >0)
														{
														?>
															<table id="tbl_camp_donors_report" class="table table-striped table-bordered table-hover" >
																<tr>
																	<th><?php echo JText::_('COM_JGIVE_GIVEBACK_NUMBER');?></th>
																	<th><?php echo JText::_('COM_JGIVE_DONOR_NAME');?></th>
																	<!--<th><?php //echo JText::_('COM_JGIVE_DONOR_ADDRESS');?></th>-->
																	<th><?php echo JText::_('COM_JGIVE_DONATED_AMOUNUT');?></th>
																	<th><?php echo JText::_('COM_JGIVE_DONATION_DATE'); ?></th>
																</tr>

																<?php
																$i=1;
																foreach($cdata['donors'] as $donor)
																{
																?>
																<tr>
																	<td><?php echo $i;?></td>
																	<td>
																		<?php
																		$extra='';
																		if(!$donor->avatar){//if no avatar, use default avatar
																			$donor->avatar=JUri::root().'components/com_jgive/assets/images/default_avatar.png';
																		}
																		$title=$donor->first_name.' '.$donor->last_name;
																		if(!empty($donor->profile_url) && $donor->user_id!=0)
																		{
																			?>
																			<a href="<?php echo $donor->profile_url; ?>" target="_blank">
																				<?php echo $title;?>
																			</a>
																			<?php
																		}
																		else{
																			echo $title;
																		}
																		?>
																		<br/>
																		<img class="com_jgive_img_48_48" src="<?php echo $donor->avatar; ?>" />
																		<br/>
																		<?php
																		if($donor->annonymous_donation)
																		{
																			?>
																			<i><?php echo JText::_("COM_JGIVE_ANNONYMOUS_DONATION_MSG_OWNER");?></i>
																			<br/>
																			<?php
																		}
																		?>
																	</td>
																	<td>
																		<?php
																			echo JText::_('COM_JGIVE_DONATED_AMT').':';
																			echo $donor->amount.' '.$this->currency_code;
																		?>
																		<br>
																		<?php
																			echo JText::_('COM_JGIVE_GIVEBACK_SELECTED').':';

																			if($donor->giveback_id)
																			{
																				echo JText::_('COM_JGIVE_YES');
																				?>
																				<br>

																				<?php
																					echo JText::_('COM_JGIVE_GIVEBACK_MIN_VALUE').':';
																					echo $donor->giveback_value.' '.$this->currency_code;
																				?>
																				<br>
																				<?php
																					echo JText::_('COM_JGIVE_GIVEBACK_DESC').':';

																					if (strlen($donor->gb_description>50))
																					{
																						echo substr($donor->gb_description,0,50).'...';
																					}
																					else
																					{
																						echo $donor->gb_description;
																					}
																			}
																			else
																			{
																				echo JText::_('COM_JGIVE_NO');
																			}
																		?>

																	</td>
																	<td><?php echo $donor->cdate;?></td>

																</tr>
																<?php
																	$i++;
																}
																?>

															</table>
															<input type="hidden" id="jgive_index" value="<?php echo $i; ?>" />

															<?php

															if($cdata['orders_count'] > $donor_records_config)
															{  ?>
																<button id="viewMoreRec" class="btn row-fluid" type="button" onclick="viewMoreDonorReports()">
																	<?php
																		echo JText::_('COM_JGIVE_SHOW_MORE_DONORS');
																	?>
																</button><?php
															}
														}
														else{
															echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_NO_DONATIONS') : JText::_('COM_JGIVE_NO_INVESTMENTS'));
														}
													?>
												</div>
											</div><!--span12-->
										</div><!--row-fluid-->

									</div>
									<!--end of tab3-->
									<?php
								}
								?>

								<!--tab4-->
								<div class="tab-pane fade" id="camp_promoter">

									<div class="row-fluid">
										<div class="span12">

											<table class="table table-striped table-bordered table-hover" >
												<tr>
													<td><?php echo JText::_('COM_JGIVE_PROMOTER_DETAILS');?></td>

													<td>
														<?php
														if($cdata['campaign']->creator_profile_url)
														{
															?>
															<a href="<?php echo $cdata['campaign']->creator_profile_url; ?>" target="_blank">
																<?php echo $cdata['campaign']->first_name.' '.$cdata['campaign']->last_name;?>
															</a>
															<?php
														}else
														{
															echo $cdata['campaign']->first_name.' '.$cdata['campaign']->last_name;

														}?>
														<br/>

														<?php if($show_field==1 OR $group_name_cnf==0 ): ?>
															<?php if(!empty($cdata['campaign']->group_name)) {
																	echo $cdata['campaign']->group_name;
																	echo"<br/>";
															}?>
															<?php endif; ?>

														<?php $userinfo=JFactory::getUser($cdata['campaign']->creator_id); echo $userinfo->email; echo"<br/>";?>

														<?php if($show_field==1 OR $website_address_cnf==0 ): ?>
															<?php if(!empty($cdata['campaign']->website_address)) {
																$cdata['campaign']->website_address=str_replace('http://','',$cdata['campaign']->website_address);
															?>
																<a href="http://<?php echo $cdata['campaign']->website_address; ?>"><?php echo $cdata['campaign']->website_address; ?></a>
															<?php
																echo"<br/>";
															}?>
														<?php endif;?>

														<?php if($show_field==1 OR $phone_cnf==0 ): ?>
															<?php if(!empty($cdata['campaign']->phone)){
																 echo $cdata['campaign']->phone;
																 echo"<br/>";
															 }?>
														 <?php endif;?>

														<?php
														if($cdata['campaign']->creator_avatar)
														{
															?>
															<img class="com_jgive_img_48_48" src="<?php echo $cdata['campaign']->creator_avatar; ?>" />
															<?php
														}
														?>
													</td>
												</tr>
												<tr>
													<td><?php echo JText::_('COM_JGIVE_ADDRESS');?></td>
													<td>
														<address>

															<?php if($show_field==1 OR $address_cnf==0 ): ?>
																<?php echo $cdata['campaign']->address.' '.$cdata['campaign']->address2;?>
																<br/>
															<?php endif; ?>

															<?php echo $cdata['campaign']->city;?>
															<br/>
															<?php echo $cdata['campaign']->state;?>
															<br/>
															<?php echo $cdata['campaign']->country;?>
															<br/>

															<?php if($show_field==1 OR $zip_cnf==0 ): ?>
																<?php echo $cdata['campaign']->zip;?>
															<?php endif; ?>

														</address>

													</td>
												</tr>
												<tr>
													<td><?php echo JText::_('COM_JGIVE_CAMPAIGN_SINGLE_PROMOTER_CAMP'); ?></td>
													<td><?php
														$cat_url='index.php?option=com_jgive&view=campaigns&layout=all&filter_user='.$cdata['campaign']->creator_id.'&Itemid='.$this->allCampsitemid;
														$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);
														echo '<a href="'.$cat_url.'">'.JText::_('COM_JGIVE_CAMPAIGN_SINGLE_OTHER_OTHER_PROMOTER_CAMPAIGN').'</a>';
														?>
													</td>
												</tr>
											</table>

										</div><!--span12-->
									</div><!--row-fluid-->

								</div>
								<!--end of tab4-->

								<!--tab5-->
								<?php

								// if campaign details is hide then show default active div as campaign donors
								$donar_tab_class='';
								if($class)
								{
									$donar_tab_class='tab-pane fade in active';
								}
								else
								{
									$donar_tab_class='tab-pane fade';
								}
								?>
								<div class="<?php echo $donar_tab_class;  ?>" id="camp_donors">

									<div class="row-fluid">
										<div id="jgive_donors_pic" class="span12">

											<?php
											//restrict access to view donations
											if($this->logged_userid==$cdata['campaign']->creator_id || $cdata['campaign']->allow_view_donations)
											{
												if(count($cdata['donors']) >0)
												{
													$j = 0;
													foreach($cdata['donors'] as $donor)
													{
														if(!$donor->avatar){//if no avatar, use default avatar
															$donor->avatar=JUri::root().'components/com_jgive/assets/images/default_avatar.png';
														}
														if($donor->annonymous_donation)
														{
															$title=JText::_("COM_JGIVE_DONOR_ANNONYMOUS_NAME").' - '.$donor->amount.' '.$this->currency_code;
															//if annonymous_donation, use annonymous avatar, reset url to blank
															$donor->avatar=JUri::root().'components/com_jgive/assets/images/annonymous.png';
															$donor->profile_url='';
														}
														else
														{
															$title=$donor->first_name.' '.$donor->last_name.' - '.$donor->amount.' '.$this->currency_code;
														}
														?>
														<div class="com_jgive_border_ano_donor">
															<?php
															if(!empty($donor->profile_url) && $donor->user_id!=0)
															{
																?>
																<a href="<?php echo $donor->profile_url; ?>" target="_blank">
																	<img class="com_jgive_img_48_48" src="<?php echo $donor->avatar; ?>" title="<?php echo $title;?>"/>
																</a>
																<?php
															}
															else
															{
																?>
																<img class="com_jgive_img_48_48" src="<?php echo $donor->avatar; ?>" title="<?php echo $title;?>"/>
																<?php
															}
															?>
														</div>
														<?php
														$j++;
													}//end for loop
												}//end if donor count
												else
												{
													echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_NO_DONATIONS') : JText::_('COM_JGIVE_NO_INVESTMENTS'));
													echo "<br/>";
													echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_BE_THE_FIRST_DONOR') : JText::_('COM_JGIVE_BE_THE_FIRST_DONOR'));
												}
											}
											else{
												echo JText::_('COM_JGIVE_DONATIONS_ACCESS_LOCKED');
											}
											?>

										</div><!--span12-->
										<br/>
										<input type="hidden" id="donors_pro_pic_index" value="<?php echo $j; ?>" />

										<?php

										if($cdata['orders_count'] > $donor_records_config)
										{  ?>
											<button id="btn_showMorePic" class="btn row-fluid" type="button" onclick="viewMoreDonorProPic()">
												<?php
													echo JText::_('COM_JGIVE_SHOW_MORE_DONORS');
												?>
											</button><?php
										} ?>

									</div><!--row-fluid-->
								</div>
								<!--end of tab5-->
						</div><!--myTabContent-->
				</div><!-- span8 -->

				<div class="span4">
					<div class="row-fluid">
						<div class="jgive span12 well" style="padding:15px;">
							<div class="jgive-raised">
								<?php
									$jgiveFrontendHelper=new jgiveFrontendHelper();
									$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cdata['campaign']->amount_received);
									echo $diplay_amount_with_format;
								?>
							</div>
							<!--if added by Sneha-->
							<?php if($show_field==1 OR $goal_amount==0 ): ?>
							<div class="jgive-raised-of  " style="margin-top:4%;">
								<?php
								echo  JText::_('COM_JGIVE_RAISED');

								$jgiveFrontendHelper=new jgiveFrontendHelper();
								$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($cdata['campaign']->goal_amount);
								echo $diplay_amount_with_format;
								?>
							</div>
							<?php endif; ?>
							<div class="progress progress-success">
								<div class="bar" style="width:<?php
										echo (($cdata['campaign']->amount_received/$cdata['campaign']->goal_amount)*100).'%'; ?>"></div>
							</div>

							<div class="jgive-days-raised row-fluid" >
								<div class="jgive-days-wrapper span6">
									<?php if(JVERSION>=3.0){ ?>
									<i class="icon-clock"></i>
									<?php }else{ ?>
										<i class=" icon-large icon-time"></i>
									<?php }?>
									&nbsp;&nbsp;&nbsp;
									<?php
									$curr_date=JFactory::getDate()->Format(JText::_('Y-m-d'));
									$time_curr_date=strtotime($curr_date);
									$time_end_date=strtotime($cdata['campaign']->end_date);
									$interval = ($time_end_date-$time_curr_date);
									$days_left=floor($interval/(60*60*24));
									?>
									<span class="jgive-days " style="margin-top:2%;"><?php echo $days_left>0 ?  $days_left: JText::_('COM_JGIVE_NA');?></span>

									<div class="tac jgive-fzmfwbu" style="margin-top:15%;"><?php echo JText::_('COM_JGIVE_DAYS_LEFT'); ?></div>
								</div>
								<!--if added by Sneha-->
								<?php if($show_field==1 OR $goal_amount==0 ): ?>
								<div class="jgive-percent-wrapper span6">
									<div class="jgive-percent">
										<img src="<?php echo JUri::base(); ?>components/com_jgive/assets/images/piggy-bank.png" width="27" height="20">
										&nbsp;<?php
										$goal_amount = (float)$cdata['campaign']->goal_amount;
										if(!empty($cdata['campaign']->amount_received) && $goal_amount>0)
										{
											echo number_format(($cdata['campaign']->amount_received/$cdata['campaign']->goal_amount)*100,2);
											echo '%';
										}
										else
										{
											echo '0.00%';
										}


										?>
									</div>
									<div class="tac jgive-fzmfwbu pt5" style="margin-top:15%;"><?php echo JText::_('COM_JGIVE_FUNDED'); ?></div>
								</div>
								<?php endif; ?>
							</div>
							<div class="clearfix"></div>
							<hr/>
							<div class="jgive-funding-type">
							<?php echo JText::_('COM_JGIVE_FUND_TYPE');
								if($cdata['campaign']->type=='donation')
									echo JText::_('COM_JGIVE_CAMPAIGN_TYPE_DONATION');
								else
									echo JText::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT');
							?>
							</div>
							<hr/>

							<?php
								$css_start_date='';
								$css_end_date='';
								$css_max_donors='';

								//check if exeeding goal amount is allowed
								//if not check for received amount to decide about hiding donate button
								$flag=0;
								$date_expire=0;
								//Condition changed by Sneha
								if($cdata['campaign']->allow_exceed==0 && ($show_field==1 || $goal_amount==0 ))
								{
									 if($cdata['campaign']->amount_received>=$cdata['campaign']->goal_amount){
										 $flag=1;
									 }
								}

								if($cdata['campaign']->max_donors>0)
								{
									 if(count($cdata['donors']) >= $cdata['campaign']->max_donors){
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
								}
							?>
							<div class="row-fluid">
								<div class="span12">
									<?php
									$btn_block='';

									$class="span6";
									$close_btn_style='';
									if(!($cdata['campaign']->creator_id==$this->logged_userid))
									{
										$btn_block='jgive-btn-block';
										$class="";
										$close_btn_style='width:100% !important';
									}
									if($flag==0)
									{
										?>
										<form action="index.php" method="post" name="adminForm" id="adminForm">
											<input type="hidden" id="cid" name="cid" value="<?php echo $cdata['campaign']->id; ?>" />

											<div class="row-fluid center">
												<button class="btn btn-success  btn-large   <?php echo $class; echo $btn_block;  ?>"  style="<?php ?>"type="submit" ><?php
												echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_BUTTON_DONATE') : JText::_('COM_JGIVE_BUTTON_INVEST'));
												?>
												</button>
												<!--added edit button-->
												<?php
												if($cdata['campaign']->creator_id==$this->logged_userid)
												{
													?>
													<a class=" btn btn-primary span6 btn-large <?php echo $class; ?> " style="<?php  ?>"
													href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=create&cid='.$cdata['campaign']->id.'&Itemid='.$this->createCampaignItemid),strlen(JUri::base(true))+1);?>">
													<?php echo JText::_('COM_JGIVE_EDIT_CAMPAIGN');?>
													</a>
													<?php
												}
												?>
											</div>
											<input type="hidden" name="option" value="com_jgive" />
											<input type="hidden" name="controller" value="donations" />
											<input type="hidden" name="task" value="donate" />
										</form>
										<?php
									}
									else
									{
										?>
										<input type="button" class="btn disabled com_jgive_button <?php echo $btn_block; ?>"  style="<?php echo $close_btn_style; ?>" value="<?php
											echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATIONS_CLOSED') : JText::_('COM_JGIVE_INVESTMENTS_CLOSED'));
										?>"/>
										<!--added edit button-->
										<?php
										if($cdata['campaign']->creator_id==$this->logged_userid)
										{
											?>
											<a class="btn btn-primary  com_jgive_button" style="<?php ?>"
											href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=create&cid='.$cdata['campaign']->id.'&Itemid='.$this->createCampaignItemid),strlen(JUri::base(true))+1);?>">
											<?php echo JText::_('COM_JGIVE_EDIT_CAMPAIGN');?>
											</a>
											<?php
										}
										?>
										<?php
									}
									?>
								</div><!--span12-->
							</div><!--row-fluid-->
							<div class="jgive-funding-type-info">
								 <?php echo $cdata['campaign']->type=='donation' ? JText::_('COM_JGIVE_DONATED_MSG') :JText::_('COM_JGIVE_INVESTED_MSG');
								  echo JFactory::getDate($cdata['campaign']->end_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));?>.    </div>
						</div>
					</div>
				</div>
				<?php
				if($show_field==1 OR $give_back_cnf==0 )
				{
					if(count($cdata['givebacks'])>=1){ ?>
					<div class="span4  pull-right">
						<h3 class="module-title"><?php echo JText::_('COM_JGIVE_GIVEBACK_DETAILS'); ?></h3>
						<div class="jgivegivback" >
							<!--<div class="jgive-reward_title center">Pledge jgive-rewards</div>-->
							<?php
							$i=0;

							foreach($cdata['givebacks'] as $giveback)
							{
								//$overallDonations
								//Added by Sneha
								$sold_givebacks = $giveback->quantity;
								$give_back_flag=0;
								$giveback_tooltip = JText::_('COM_JGIVE_BY_GIVEBACK');

								if ($sold_givebacks == $giveback->total_quantity  || $sold_givebacks > $giveback->total_quantity)
								{
									$give_back_flag=1;
									$giveback_tooltip = JText::_('COM_JGIVE_GIVEBACK_SOLD_OUT');
								}

								$url = JUri::root().'index.php?option=com_jgive&controller=donations&task=donate&cid='.$cdata['campaign']->id.'&giveback_id='.	$giveback->id;

								$onclick = 'onclick="callDonateForm(\''.$url.'\')"';

								//Disable onlclick
								if ($give_back_flag==1 || $flag==1)
								{
									$onclick='';
								}


							 ?>
								<!--jgive-reward -->

								<div class="media jgive-reward " <?php echo $onclick; ?> title="<?php echo  $giveback_tooltip; ?>" style="margin-bottom:5px;">

									<?php
									if($giveback->image_path)
									{
										if(file_exists(JPATH_ROOT.DS.$giveback->image_path))
										{
										 ?>
											<a class="pull-left thumbnail" <?php if($onclick) echo 'href="'.$url.'"'; ?> >
												<img class="media-object " data-src="holder.js/64x64" alt="64x64" style="width: 64px; height: 64px;" src="<?php echo JUri::base().$giveback->image_path; ?>">
											</a>
										<?php
										}
									}
									?>
									<div class="media-body ">
										<h4 class="media-heading">
										<?php echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATE_LABEL') : JText::_('COM_JGIVE_INVEST_LABEL')); ?> <?php echo $giveback->amount.' '. $this->currency_code.' '.JText::_('COM_JGIVE_OR_MORE'); ?></h4>

										<?php if ($give_back_flag==1)
										{ ?>
											<br/>
											<span class="alert alert-error" >
												<?php echo JText::_('COM_JGIVE_BACK_SOLD_OUT');?>
											</span>
										<?php } ?>

									</div>

									<div style="clear:both"></div>

									<span class="rdesc">
										<?php echo $giveback->description; ?>
									</span>
									<br/>
									<br/>
									<span class="thumbnail">

										<?php echo '<b>'.$sold_givebacks.'</b>';?>

										<?php
										//if max donors data available menas giveback has sell has also limit
										//e.g if maximum allowed donations is 10 menas maximum giveback should be 10
										// Maximum donor has no limit (field) then giveback can also be unlimited hence if max donor data not available then don't show giveback total

										$toDisplay='';
										if($show_field==1 OR $max_donation_cnf==0 )
										{
											echo ' / <b>'.$giveback->total_quantity.'</b>';
											$toDisplay=JText::_('COM_JGIVE_GIVEBACK_TOTAL');
										}
										echo '<br/>';
										echo ''.JText::_('COM_JGIVE_GIVEBACK_SOLD').'';

										if($toDisplay)
										{
											echo ' /'.$toDisplay;
										}
										?>


									</span>

								</div>
								<!--jgive-reward -->

							<?php
							$i++;
							} ?>
						</div>
					</div>
				<?php }
				} ?>
			</div>
		</div><!--span12-->

		<div class="row-fluid">
				<div class="span12">

					<table class="table table-striped table-hover" >
						<tr>
							<td>
								<b><?php echo JText::_('COM_JGIVE_PROMOTER_DETAILS');?></b>
								<br/>
								<br/>

								<div class="row-fluid">
									<?php
									if($cdata['campaign']->creator_avatar)
									{
									?>
									<div class="span4">
										<img class="com_jgive_img_48_48" src="<?php echo $cdata['campaign']->creator_avatar; ?>" />
									</div>
									<?php
									}
									?>
									<div class="span8">
										<?php
										if($cdata['campaign']->creator_profile_url)
										{
											?>
											<a href="<?php echo $cdata['campaign']->creator_profile_url; ?>" target="_blank">
												<?php echo $cdata['campaign']->first_name.' '.$cdata['campaign']->last_name;?>
											</a>
											<?php
										}else
										{
											echo $cdata['campaign']->first_name.' '.$cdata['campaign']->last_name;

										}?>
										<br/>
										<?php if($show_field==1 OR $group_name_cnf==0 ): ?>
											<?php if(!empty($cdata['campaign']->group_name)) {
													echo $cdata['campaign']->group_name;
													echo"<br/>";
											}?>
											<?php endif; ?>

										<?php $userinfo=JFactory::getUser($cdata['campaign']->creator_id); echo $userinfo->email; echo"<br/>";?>

										<?php if($show_field==1 OR $website_address_cnf==0 ): ?>
											<?php if(!empty($cdata['campaign']->website_address)) {
												$cdata['campaign']->website_address=str_replace('http://','',$cdata['campaign']->website_address);
											?>
												<a href="http://<?php echo $cdata['campaign']->website_address; ?>"><?php echo $cdata['campaign']->website_address; ?></a>
											<?php
												echo"<br/>";
											}?>
										<?php endif;?>

										<?php if($show_field==1 OR $phone_cnf==0 ): ?>
											<?php if(!empty($cdata['campaign']->phone)){
												 echo $cdata['campaign']->phone;
												 echo"<br/>";
											 }?>
										 <?php endif;?>

										<!-- //show link to other campaign of campaign promoter-->

										<?php
											$cat_url='index.php?option=com_jgive&view=campaigns&layout=all&filter_user='.$cdata['campaign']->creator_id.'&Itemid='.$this->allCampsitemid;
											$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);
											echo '<a href="'.$cat_url.'">'.JText::_('COM_JGIVE_CAMPAIGN_SINGLE_OTHER_OTHER_CAMPAIGN_OF_PROMOTER').'</a>';
										?>

									</div>
								</div>
							</td>
						</tr>
					</table>

				</div><!--span12-->
			</div><!--row-fluid-->

	</div><!--row-fluid-->
</div><!--bootstrap-->

<?php
	//Integration with Jlike
	if(file_exists(JPATH_SITE.'/'.'components/com_jlike/helper.php'))
	{
		$jgiveFrontendHelper =new jgiveFrontendHelper();
		$jlikehtml=$jgiveFrontendHelper->DisplayjlikeButton($ad_url,$cdata['campaign']->id,$cdata['campaign']->title);
		if($jlikehtml)
		echo $jlikehtml;
	}
	//Integration with Jlike
?>

<script type="text/javascript">

	var gbl_jgive_index = 0 ;

	var orders_count = <?php echo $cdata['orders_count'] ?>;

	console.log(orders_count);

	function viewMoreDonorReports()
	{
		//load the more comments for view more
		var cid = document.getElementById('cid').value;

		if(gbl_jgive_index == 0)
		{
			gbl_jgive_index = document.getElementById('jgive_index').value;
		}

		techjoomla.jQuery.ajax({
			url:'<?php echo JUri::root();?>index.php?option=com_jgive&controller=campaign&task=viewMoreDonorReports&tmpl=component&format=row',
			type:'POST',
			dataType:'json',
			data:
			{
				cid:cid,
				jgive_index:gbl_jgive_index
			},
			success:function(data)
			{
				gbl_jgive_index = data['jgive_index'];
				techjoomla.jQuery("#tbl_camp_donors_report tbody").append(data['records']);

				if(!data['records'] || orders_count <= gbl_jgive_index)
				{
					techjoomla.jQuery("#viewMoreRec").hide();
				}
			},
			error:function(data)
			{
				console.log('error');
			}
		});
	}

	var gbl_jgive_pro_pic = 0 ;

	function viewMoreDonorProPic()
	{
		//load the more comments for view more
		var cid = document.getElementById('cid').value;

		if(gbl_jgive_pro_pic == 0)
		{
			gbl_jgive_pro_pic = document.getElementById('donors_pro_pic_index').value;
		}

		techjoomla.jQuery.ajax({
			url:'<?php echo JUri::root();?>index.php?option=com_jgive&controller=campaign&task=viewMoreDonorProPic&tmpl=component&format=row',
			type:'POST',
			dataType:'json',
			data:
			{
				cid:cid,
				jgive_index:gbl_jgive_pro_pic
			},
			success:function(data)
			{
				gbl_jgive_pro_pic = data['jgive_index'];
				techjoomla.jQuery("#jgive_donors_pic ").append(data['records']);

				if(!data['records'] || orders_count <= gbl_jgive_pro_pic)
				{
					techjoomla.jQuery("#btn_showMorePic").hide();
				}
			},
			error:function(data)
			{
				console.log('error');
			}
		});
	}
</script>
