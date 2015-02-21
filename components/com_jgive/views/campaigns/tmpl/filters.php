<div class="span3">
	<form action="" method="post" name="adminForm3" id="adminForm3">
		<input type="hidden" name="option" value="com_jgive" />
		<input type="hidden" name="view" value="campaigns" />
		<input type="hidden" name="layout" value="all" />

			<?php
			if(JVERSION >= 3.0 )
			{
				?>
				<div class="btn-group pull-left hidden-phone">
					<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
				<br/>
				<br/>
				<?php
			} ?>

			<?php

			if ($this->params->get('show_filters') && $this->params->get('show_search_filter'))
			{ ?>
				<!--Added by Sneha for free text search-->
				<div class="row-fluid">
					<div class="pull-left">
						<input type="text" placeholder="<?php echo JText::_('COM_JGIVE_ENTER_CAMPAIGN_NAME'); ?>" name="search_list" id="search_list" value="<?php echo $this->lists['search_list']; ?>" class="input-small pull-left" onchange="document.adminForm.submit();" />
						<div class="btn-group pull-right hidden-phone">
							<button type="button" onclick="this.form.submit();" class="btn tip hasTooltip" data-original-title="Search"><i class="icon-search"></i></button>
							<button onclick="document.id('search_list').value='';this.form.submit();" type="button" class="btn tip hasTooltip" data-original-title="Clear"><i class="icon-remove"></i></button>
						</div>
					</div>
				</div>
				<!--Added by Sneha for free text search-->

			<?php
			} ?>


			<?php
			if($this->params->get('show_sorting_options') || $this->params->get('show_filters'))
			{

				if($this->params->get('show_sorting_options'))
				{
					?>
					<div class="" style="">
						<span><strong><?php echo JText::_('COM_JGIVE_ORDERING_OPTIONS');?></strong></span>
						<br/>

						<?php
							echo JHtml::_('select.genericlist', $this->ordering_options, "filter_order", ' size="1"
							onchange="this.form.submit();"
							class="input-medium jgive_filter_width" name="filter_order"',"value", "text", $this->lists['filter_order']);
						?>
						&nbsp;
						<?php
							echo JHtml::_('select.genericlist', $this->ordering_direction_options, "filter_order_Dir", ' size="1"
							onchange="this.form.submit();" class="input-medium jgive_filter_width" name="filter_order_Dir"',"value", "text", $this->lists['filter_order_Dir']);
						?>
					</div>
					<?php
				}

				if($this->params->get('show_filters'))
				{
					?>
					<div class="">
						<br/>
						<span><strong><?php echo JText::_('COM_JGIVE_FILTER_CAMPAIGNS');?></strong></span>
						<br/><br/>

						<?php
						$type_filter_on=0;	//type
						?>

						<?php
						$promoter_filter_on=0;
						if($this->params->get('show_promoter_filter'))
						{
							$promoter_filter_on=1;
							echo JHtml::_('select.genericlist', $this->user_filter_options, "filter_user", ' size="1"
							onchange="this.form.submit();" class="input-medium jgive_filter_width" name="filter_user"',"value", "text", $this->lists['filter_user']);
						}
						else
						{
							$input=JFactory::getApplication()->input;
							$filter_user=$input->get('filter_user','','INT');
							if($filter_user)
							{
								$promoter_filter_on=1;
								echo JHtml::_('select.genericlist', $this->user_filter_options, "filter_user", ' size="1"
								onchange="this.form.submit();" class="input-medium jgive_filter_width" name="filter_user"',"value", "text", $this->lists['filter_user']);
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
							 echo JHtml::_('select.genericlist', $this->filter_org_ind_type, "filter_org_ind_type", 'class="input-medium jgive_filter_width" size="1"
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
							onchange="this.form.submit();" class="input-medium jgive_filter_width" name="campaign_countries"',"value", "text", $this->lists['campaign_countries']);
							//state
							?>
							&nbsp;
							<?php
								echo JHtml::_('select.genericlist', $this->campaign_states, "campaign_states", ' size="1"
								onchange="this.form.submit();" class="input-medium jgive_filter_width"  name="campaign_states"',"value", "text", $this->lists['campaign_states']);
								//city
							?>
							&nbsp;
							<?php
								echo JHtml::_('select.genericlist', $this->campaign_city, "campaign_city", ' size="1"
								onchange="this.form.submit();" class="input-medium jgive_filter_width" name="campaign_city"',"value", "text", $this->lists['campaign_city']);
						?>
						</div>
						<?php }?>

					</div>
				<?php
				}
			}
			?>

			<!-- -Quick Search Filter-->
			<br/>
			<ul class="com_jgive_list_style_none" style="">
				<?php
					echo ' <li>'.JText::_('COM_JGIVE_CAMPAIGNS_TO_SHOW').'</li>';
					$cat_url='index.php?option=com_jgive&view=campaigns&layout=all&campaigns_to_show=&Itemid='.$this->singleCampaignItemid;
					$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);

					if ($this->lists['campaigns_to_show']=='')
					{
						echo ' <li><b><a href="'.$cat_url.'">'.JText::_('COM_JGIVE_RESET_FILTER_TO_ALL').'</a></b></li>';
					}
					else
					{
						echo ' <li><a href="'.$cat_url.'">'.JText::_('COM_JGIVE_RESET_FILTER_TO_ALL').'</a></li>';
					}


					for($i=1;$i<count($this->campaigns_to_show);$i++)
					{
						$cat_url='index.php?option=com_jgive&view=campaigns&layout=all&campaigns_to_show='.$this->campaigns_to_show[$i]->value.'&Itemid='.$this->singleCampaignItemid;
						$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);

						if($this->lists['campaigns_to_show']==$this->campaigns_to_show[$i]->value)
						{
							echo ' <li><b><a href="'.$cat_url.'">'. $this->campaigns_to_show[$i]->text.'</a></b></li>';
						}
						else
						{
							echo ' <li><a href="'.$cat_url.'">'. $this->campaigns_to_show[$i]->text.'</a></li>';
						}
					}
				?>
			</ul>
			<!-- -Quick Search Filter-->


			<!-- -Campaigns Type Filter-->
			<br/>
			<?php
			//organization_individual_type
			$campaignHelper=new campaignHelper();
			$campaign_type=$campaignHelper->filedToShowOrHide('campaign_type');

			if($this->params->get('show_type_filter') AND $campaign_type)
			{
				?>
				<ul class="com_jgive_list_style_none">
					<?php
					echo ' <li>'.JText::_('COM_JGIVE_CAMP_TYPE').'</li>';
					$cat_url='index.php?option=com_jgive&view=campaigns&layout=all&filter_campaign_type=&Itemid='.$this->singleCampaignItemid;
					$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);

					if($this->lists['filter_campaign_type']=='')
						echo ' <li><b><a href="'.$cat_url.'">'.JText::_('COM_JGIVE_RESET_FILTER_TO_ALL').'</a></b></li>';
					else
						echo ' <li><a href="'.$cat_url.'">'.JText::_('COM_JGIVE_RESET_FILTER_TO_ALL').'</a></li>';

					for($i=1;$i<count($this->campaign_type_filter_options);$i++)
					{
						$cat_url='index.php?option=com_jgive&view=campaigns&layout=all&filter_campaign_type='.$this->campaign_type_filter_options[$i]->value.'&Itemid='.$this->singleCampaignItemid;
						$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);
						 if($this->lists['filter_campaign_type']==$this->campaign_type_filter_options[$i]->value)
							echo '<li><b><a href="'.$cat_url.'">'. $this->campaign_type_filter_options[$i]->text.'</a></b></li>';
						 else
							echo '<li><a href="'.$cat_url.'">'. $this->campaign_type_filter_options[$i]->text.'</a></li>';
					}
					?>
				</ul>
			<?php
			} ?>
			<!-- -Campaigns Type Filter-->


			<!-- -Campaigns Category Filter-->
			<?php
			//category
			if($this->params->get('show_category_filter'))
			{ ?>
				<br/>
				<ul class="com_jgive_list_style_none">
					<?php
					echo ' <li>'.JText::_('COM_JGIVE_FILTER_CAMP_CAT').'</li>';
					$cat_url='index.php?option=com_jgive&view=campaigns&layout=all&filter_campaign_cat=&Itemid='.$this->singleCampaignItemid;
					$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);

					if($this->lists['filter_campaign_cat']=='')
						echo ' <li><b><a href="'.$cat_url.'">'.JText::_('COM_JGIVE_RESET_FILTER_TO_ALL').'</a></b></li>';
					else
						echo ' <li><a href="'.$cat_url.'">'.JText::_('COM_JGIVE_RESET_FILTER_TO_ALL').'</a></li>';

					for($i=1;$i<count($this->cat_options);$i++)
					{
						$cat_url='index.php?option=com_jgive&view=campaigns&layout=all&filter_campaign_cat='.$this->cat_options[$i]->value.'&Itemid='.$this->singleCampaignItemid;
						$cat_url=JUri::root().substr(JRoute::_($cat_url),strlen(JUri::base(true))+1);

						if($this->lists['filter_campaign_cat']==$this->cat_options[$i]->value)
							echo ' <li><b><a href="'.$cat_url.'">'. $this->cat_options[$i]->text.'</a></b></li>';
						else
							echo ' <li><a href="'.$cat_url.'">'. $this->cat_options[$i]->text.'</a></li>';
					}
					?>
				</ul>
			<?php }?>
			<!-- -Campaigns Category Filter-->

	</form>
</div>
