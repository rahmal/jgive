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

JToolBarHelper::preferences( 'com_jgive' );
//load frontend donations view - layout all
 $document = JFactory::getDocument();
 //Added by Sneha to hide unwanted filters
$menuCssOverrideJs="jQuery(document).ready(function(){
jQuery('#filter_campaign_type_chzn').hide();
jQuery('#filter_org_ind_type_chzn').hide();
});";
 $document->addScriptDeclaration($menuCssOverrideJs);
/*
 JToolBarHelper::DeleteList(JText::_('COM_JGIVE_DELETE_CONFRIM'),'remove','JTOOLBAR_DELETE');
if(JVERSION >= '1.6.0'){
       $core_js = JUri::root().'media/system/js/core.js';
       $flg=0;
       foreach($document->_scripts as $name=>$ar)
       {
               if($name == $core_js )
                       $flg=1;
       }
       if($flg==0)
				echo "<script type='text/javascript' src='".$core_js."'></script>";
}

JToolBarHelper::publishList();
JToolBarHelper::unpublishList();
JToolBarHelper::preferences( 'com_jgive' );
**/
if(JVERSION>=3.0)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');
}

$campaignHelper =new campaignHelper();



?>
<script type="text/javascript">

	function jgive_action()
	{
		document.getElementById('task').value=" ";
		Joomla.submitform();
	}

</script>

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
			<?php echo JText::_('COM_JGIVE_ENDING_CAMPAIGNS');?>
		</div>
		<hr/>
		<?php
	}
	?>



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
		<?php //echo $this->pagination->getLimitBox(); ?>
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

	<form action="" method="post" name="adminForm" id="adminForm">

		<!--Added by Sneha for free text search-->
		<div class="row-fluid inline">
			<div class="pull-left">
				<input type="text" placeholder="<?php echo JText::_('COM_JGIVE_ENTER_CAMPAIGN_NAME'); ?>" name="search_list" id="search_list" value="<?php echo $this->lists['search_list']; ?>" class="input-medium pull-left" onchange="document.adminForm.submit();" />
				<div class="btn-group pull-right hidden-phone">
					<button type="button" onclick="jgive_action()" class="btn tip hasTooltip" data-original-title="Search"><i class="icon-search"></i></button>
					<button onclick="document.id('search_list').value='';jgive_action()" type="button" class="btn tip hasTooltip" data-original-title="Clear"><i class="icon-remove"></i></button>
				</div>
			</div>

				<div  class="pull-right">
					<div  class="pull-left">
					<div  class="pull-left">
					<?php
						echo JHTML::_('calendar', $this->date['start_date'], 'start_date', 'start_date', '%d-%m-%Y', array('class'=>'inputbox','placeholder'=>JText::_('COM_JGIVE_ENDING_CAMPAIGNS_FROM_DATE')));
					?>
					</div>
					<div  class="pull-left">
					<?php
						echo JHTML::_('calendar', $this->date['end_date'], 'end_date', 'end_date', '%d-%m-%Y', array('class'=>'inputbox','placeholder'=>JText::_('COM_JGIVE_ENDING_CAMPAIGNS_TILL_DATE')));
					?>
					</div>
					<input id="btnRefresh" class="btn  btn-small btn-primary" type="button" value=">>" style="font-weight: bold;" onclick="jgive_action()"/>
					</div>
				</div>

		</div>
		<!--Added by Sneha for free text search-->

	<table class="<?php echo $tblclass; ?>" width="100%">
		<?php if(JVERSION<3.0):?>
			<tr>
				<td class="center" colspan="11">
					<div class="com_jgive_float_right">
							<?php
							if(JVERSION<3.0):

								echo JHtml::_('select.genericlist', $this->campaign_type_filter_options, "filter_campaign_type", ' size="1"
								onchange="document.adminForm.submit();" name="filter_campaign_type"',"value", "text", $this->lists['filter_campaign_type']);

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
				<th class="center" width="15%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_CAMPAIGN_DETAILS','title', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<!--Added by Sneha-->
				<th class="center" width="10%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_START_DATE','start_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th class="center" width="10%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_END_DATE','end_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th class="center" width="15%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_GOAL_AMOUNT','goal_amount', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th class="center" width="15%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_AMOUNT_RECEIVED','amount_received', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th class="center" width="5%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_DONORS','donor_count', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th class="center" width="9%"><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_ID','id', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
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
						<td class="center">
							<div>
								<a target="_blank" href="<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$data->id),strlen(JUri::base(true))+1);?>" title="<?php echo JText::_('COM_JGIVE_CLICK_TO_VIEW_CAMP_TOOLTIP');?>">
									<?php echo $data->title;?>
								</a>
							</div>
							<div class="com_jgive_clear_both"></div>
						</td>

						<td class="center"><?php echo JFactory::getDate($data->start_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));	?></td>

						<td class="center"><?php echo JFactory::getDate($data->end_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));	?></td>

						<td class="center"><?php
							$jgiveFrontendHelper=new jgiveFrontendHelper();
							$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($data->goal_amount);
							echo $diplay_amount_with_format;
							?>
						</td>

						<td class="center"><?php

							$jgiveFrontendHelper=new jgiveFrontendHelper();
							$diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($data->amount_received);
							echo $diplay_amount_with_format;
							?>
						</td>

						<td class="center"><?php echo $data->donor_count;?></td>

						<td class="center"><?php echo $data->id;?></td>
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
					<td class="center" colspan="11">
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
						<?php //echo $this->pagination->getListFooter(); ?>
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
		<input type="hidden" name="view" value="ending_camp" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_cat'];?>" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" id="task" name="task" value="" />
		<input type="hidden" id="controller" name="controller" value="ending_camp" />


	</form>
</div>

