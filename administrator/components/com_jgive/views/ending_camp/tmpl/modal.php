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

//load frontend donations view - layout all
 $document = JFactory::getDocument();
 //JToolBarHelper::DeleteList(JText::_('COM_JGIVE_DELETE_CONFRIM'),'remove','JTOOLBAR_DELETE');
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

JToolBarHelper::preferences( 'com_jgive' );
if(JVERSION>=3.0)
{
	JHtml::_('bootstrap.tooltip');
	JHtml::_('behavior.multiselect');
	JHtml::_('formbehavior.chosen', 'select');
}
$app = JFactory::getApplication();
if ($app->isSite())
{
	JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}
$function  = $app->input->getCmd('function', 'jSelectBook_');
$campaignHelper =new campaignHelper();



?>
<script type="text/javascript">

Joomla.submitbutton = function(action){
	var form = document.adminForm;
	if(action=='placeOrder')
	{
		Joomla.submitform(action );
	}
	else
		Joomla.submitform(action );
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
			<?php echo JText::_('COM_JGIVE_ALL_CAMPAIGNS');?>
		</div>
		<hr/>
		<?php
	}
	?>


	<form action="index.php" method="post" name="adminForm" id="adminForm">
		
	<?php if(JVERSION >= 3.0 ){ ?> 
	<div class="btn-group pull-right hidden-phone">
		<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
		<?php echo $this->pagination->getLimitBox(); ?>
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
	<table class="<?php echo $tblclass; ?>">
		<?php if(JVERSION<3.0):?>
			<tr>
				<td colspan="11">
					<div class="com_jgive_float_right">
							<?php /* 
							if(JVERSION<3.0):
								
								echo JHtml::_('select.genericlist', $this->campaign_type_filter_options, "filter_campaign_type", ' size="1"
								onchange="document.adminForm.submit();" name="filter_campaign_type"',"value", "text", $this->lists['filter_campaign_type']);
								
								echo JHtml::_('select.genericlist', $this->cat_options, "filter_campaign_cat", 'class="" size="1"
								onchange="document.adminForm.submit();" name="filter_campaign_cat"',"value", "text",$this->lists['filter_campaign_cat']);
								
							endif;*/
							?>
					</div>
				</td>
			</tr>
		<?php endif;?>
		<?php if(JVERSION>=3.0):?>
		<thead>
		<?php endif;?>	
			<tr>
				<th width=""><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_CAMPAIGN_DETAILS','title', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width=""><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_START_DATE','start_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width=""><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_END_DATE','end_date', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width=""><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_GOAL_AMOUNT','goal_amount', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width=""><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_AMOUNT_RECEIVED','amount_received', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
				<th width=""><?php echo JHtml::_( 'grid.sort', 'COM_JGIVE_ID','id', $this->lists['filter_order_Dir'], $this->lists['filter_order']); ?></th>
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
						<td>
							<div><!--
								<a target="_blank" href="<?php //echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$data->id.'&Itemid='.$this->singleCampaignItemid),strlen(JUri::base(true))+1);?>" title="<?php //echo JText::_('COM_JGIVE_CLICK_TO_VIEW_CAMP_TOOLTIP');?>">
									<?php //echo $data->title;?>
								</a>-->

								<a class="pointer" onclick="if (window.parent) window.parent.<?php echo $this->escape($function);?>('<?php echo $data->id; ?>', '<?php echo $this->escape(addslashes($data->title)); ?>','<?php echo JUri::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$data->id.'&Itemid='.$this->singleCampaignItemid),strlen(JUri::base(true))+1);?>');">
									<?php echo $this->escape($data->title); ?>
								</a>
							</div>
							<div class="com_jgive_clear_both"></div>
						</td>
						<!--Added by Sneha-->
						<td><?php echo JFactory::getDate($data->start_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));	?></td>

						<td><?php echo JFactory::getDate($data->end_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));	?></td>

						<td><?php echo $data->goal_amount.' '.$this->currency_code;?></td>

						<td><?php echo $data->amount_received.' '.$this->currency_code;?></td>

						<td><?php echo $data->id;?></td>
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
					<td colspan="11">
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
						<?php echo $this->pagination->getListFooter(); ?>
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
		<input type="hidden" name="view" value="campaigns" />
		<input type="hidden" name="layout" value="modal" />
		<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
		<input type="hidden" name="defaltevent" value="<?php echo $this->lists['filter_campaign_cat'];?>" /> 
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" id="controller" name="controller" value="campaigns" />
	</form>
</div>

