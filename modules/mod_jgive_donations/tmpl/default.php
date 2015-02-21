<?php
/**
* @package    jGive Donation
* @author     Techjoomla
* @copyright  Copyright 2013 - Techjoomla
* @license    http://www.gnu.org/licenses/gpl-3.0.html
**/
$document=JFactory::getDocument();

//no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!defined('DS'))
{
	define('DS',DIRECTORY_SEPARATOR);
}

$lang = JFactory::getLanguage();
$extension = 'mod_jgive_donations';
$base_dir = JPATH_SITE;
$language_tag = 'en-GB';
$reload = true;
$lang->load($extension, $base_dir, $language_tag, $reload);

include_once JPATH_ROOT.'/media/techjoomla_strapper/strapper.php';
TjAkeebaStrapper::bootstrap();
//load component css
$document->addStyleSheet(JURI::root().'components/com_jgive/assets/css/jgive.css');

$helperPath=JPATH_SITE.'/components'.DS.'com_jgive'.DS.'helper.php';
if(!class_exists('jgiveFrontendHelper'))
{
	JLoader::register('jgiveFrontendHelper', $helperPath );
	JLoader::load('jgiveFrontendHelper');
}
//load Campaigns helper
$jgiveFrontendHelper=new jgiveFrontendHelper();
$singleCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=single');
?>

<style type="text/css">
	.techjoomla-bootstrap .table th, .techjoomla-bootstrap .table td {
		border:0px!important;
	}
	.small{
		width:50%;
		font-size:11px;
	}
	.td_amt{
		width:50%;
		font-size:11px;
		text-align: right!important;
	}
	.th_donation{
		width:50%;
		font-size:11px;
	}
	.amount_data{
	text-align: right !important;
	width: 50%;
	float: right;
	}
</style>


<div class="techjoomla-bootstrap <?php echo $params->get('moduleclass_sfx'); ?>">

<?php

	$module_for=$params->get('module_for');

	if ($module_for=='my_donations')
	{
		if (!$userid)
		{
			echo JText::_('MOD_JGIVE_DONATION_LOGIN');
			echo '</div>';
			return;
		}
	}

?>

		<table class="table">
			<tr>
				<th class="th_donation">
					<?php

						if ($module_for=='my_donations')
						{
							echo JText::_('MOD_JGIVE_DONATION_ORDER_ID');
						}
						else if ($module_for=='last_donations' OR $module_for=='top_donations')
						{
							echo JText::_('MOD_JGIVE_DONATION_DONOR');
						}

					?>
				</th>
				<th class="th_donation"><?php echo JText::_('MOD_JGIVE_DONATION_CAMP_NAME');?></th>
				<th class="th_donation"><?php echo JText::_('MOD_JGIVE_DONATION_AMOUNT_DONATED');?></th>
			</tr>
			<?php
			$i=0;$total_amount=0;
			foreach($result as $row) {?>

			<tr>
				<td class="small">
					<div>
						<?php
							if($module_for=='my_donations')
								echo $row->order_id;
							else if($module_for=='last_donations' OR $module_for=='top_donations')
							{
								if($row->name)
								{
									echo $row->name;
								}
								else
								{
									echo JText::_('MOD_JGIVE_DONATION_GUEST');
								}
							}
						?>
					</div>
				</td>
				<td class="small">
					<div>
						<a href="<?php echo JURI::root().substr(JRoute::_('index.php?option=com_jgive&view=campaign&layout=single&cid='.$row->cid.'&Itemid='.$singleCampaignItemid),strlen(JURI::base(true))+1);?>">
						<b><?php echo $row->title; ?></b>
						</a>
					</div>
				</td>

				<td class="td_amt" style="word-break: break-word;">
					<div>
						<?php
						$com_jgive_params=JComponentHelper::getParams('com_jgive');

						$jgiveFrontendHelper=new jgiveFrontendHelper();
						echo $diplay_amount_with_format=$jgiveFrontendHelper->getFromattedPrice($row->amount);

						$total_amount=$total_amount+$row->amount;
						?>
					</div>
				</td>
			</tr>
		<?php }
			if($module_for=='my_donations')
			{ ?> <tr > <td colspan="3" class="small" style="text-align:right;"> <?php
				require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helper.php");
				//get items ids
				//@create jgiveFrontendHelper object
				$jgiveFrontendHelper=new jgiveFrontendHelper();
				$myDonationItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=donations&layout=my'); ?>
				<a href="<?php echo JURI::root().substr(JRoute::_('index.php?option=com_jgive&view=donations&layout=my&Itemid='.$myDonationItemid),strlen(JURI::base(true))+1)?>">
					<b><?php echo JText::_('MOD_JGIVE_DONATION_ALL');?></b>
				</a>
				</td>
				</tr>
				<?php
		} ?>
		</table>
</div>

