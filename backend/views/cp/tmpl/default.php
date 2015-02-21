<?php
defined('_JEXEC') or die('Restricted access');
//jimport('joomla.html.pane');
jimport( 'joomla.html.html.tabs' );
$document=JFactory::getDocument();
$document->addStyleSheet(JUri::base().'components/com_jgive/assets/css/jgive.css');

/* joomla 2.5
$pane=JPane::getInstance('tabs', array('startOffset'=>0));
$xml=JFactory::getXMLParser('Simple');
$currentversion = '';
$xml->loadFile(JPATH_SITE.'/administrator/components/com_jgive/jgive.xml');
 2.5  */

/* joomla  3.0 */
$xml=JFactory::getXML(JPATH_SITE.'/administrator/components/com_jgive/jgive.xml');
$currentversion=(string)$xml->version;
/* 3  */
if($xml->document)
foreach($xml->document->_children as $var)
	{
		if($var->_name=='version')
			$currentversion = $var->_data;
	}

$options = array(


    'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
    'useCookie' => true, // this must not be a string. Don't use quotes.
);
?>
<script type="text/javascript">

	function vercheck()
	{
		callXML('<?php echo $currentversion; ?>');
		if(document.getElementById('NewVersion').innerHTML.length<220)
		{
			document.getElementById('NewVersion').style.display='inline';
		}
	}

	function callXML(currversion)
	{
		if (window.XMLHttpRequest)
			{
		 	 xhttp=new XMLHttpRequest();
			}
		else // Internet Explorer 5/6
			{
		 	xhttp=new ActiveXObject("Microsoft.XMLHTTP");
			}

	xhttp.open("GET","<?php echo JUri::base(); ?>index.php?option=com_jgive&task=getVersion",false);
	xhttp.send("");
	latestver=xhttp.responseText;

	if(latestver!=null)
	  {
		if(currversion == latestver)
		{
			document.getElementById('NewVersion').innerHTML='<span style="display:inline; color:#339F1D;">&nbsp;<?php echo JText::_("COM_JGIVE_LAT_VERSION");?> <b>'+latestver+'</b></span>';
		}
		else
		{
			document.getElementById('NewVersion').innerHTML='<span style="display:inline; color:#FF0000;">&nbsp;<?php echo JText::_("COM_JGIVE_LAT_VERSION");?> <b>'+latestver+'</b></span>';
		}
	  }
     }
</script>
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
<?php if(JVERSION<3.0):?>
<div id="cpanel" style="float: left; width: 100%;">
		<div id= "cp1" style="float: left; width: 60%;">
			<div style="float: left;">
				<div class="icon">
					<a href="index.php?option=com_jgive&view=campaigns&layout=all_list">
					<img src="<?php echo JUri::base()?>components/com_jgive/assets/images/icon-48-campaigns.png" alt="<?php echo JText::_("COM_JGIVE_CAMPAIGNS");?>"/>
					<span><?php echo JText::_("COM_JGIVE_CAMPAIGNS");?></span>
					</a>
				</div>
			</div>
			<div style="float: left;">
				<div class="icon">
					<a href="index.php?option=com_categories&view=categories&extension=com_jgive">
					<img src="<?php echo JUri::base()?>components/com_jgive/assets/images/icon-48-categories.png" alt="<?php echo JText::_("COM_JGIVE_REPORTS");?>"/>
					<span><?php echo JText::_("COM_JGIVE_CATEGORIES");?></span>
					</a>
				</div>
			</div>
			<div style="float: left;">
				<div class="icon">
					<a href="index.php?option=com_jgive&view=reports&layout=default">
					<img src="<?php echo JUri::base()?>components/com_jgive/assets/images/icon-48-reports.png" alt="<?php echo JText::_("COM_JGIVE_REPORTS");?>"/>
					<span><?php echo JText::_("COM_JGIVE_REPORTS");?></span>
					</a>
				</div>
			</div>
			<div style="float: left;">
				<div class="icon">
					<a href="index.php?option=com_jgive&view=reports&layout=payouts">
					<img src="<?php echo JUri::base()?>components/com_jgive/assets/images/icon-48-payouts.png" alt="<?php echo JText::_("COM_JGIVE_PAYOUT_REPORTS");?>"/>
					<span><?php echo JText::_("COM_JGIVE_PAYOUT_REPORTS");?></span>
					</a>
				</div>
			</div>
			<div style="float: left;">
				<div class="icon">
					<a href="index.php?option=com_jgive&view=donations">
					<img src="<?php echo JUri::base()?>components/com_jgive/assets/images/icon-48-donations.png" alt="<?php echo JText::_("COM_JGIVE_DONATIONS");?>"/>
					<span><?php echo JText::_("COM_JGIVE_DONATIONS");?></span>
					</a>
				</div>
			</div>
		</div>

		<div id="cp2" class="cp2" style="float: left; width: 40%;padding-bottom: 10px; ">
		<?php
		//echo $pane->startPane( 'pane' );
		//echo $pane->startPanel( JText::_('COM_JGIVE_ABOUT'), 'panel1' );?>

		<h1 style="color:#0B55C4;"><?php echo JText::_('COM_JGIVE_ABOUT1');?></h1>
		<!--
		<h3><b><?php echo JText::_('COM_JGIVE_ABOUT1');?></b></h3>
		<ol>
			<li><?php echo JText::_('COM_JGIVE_ABOUT1');?></li>
			<li><?php echo JText::_('COM_JGIVE_ABOUT1');?></li>
			<li><?php echo JText::_('COM_JGIVE_ABOUT1');?></li>
		</ol>
		<p><?php echo JText::_('COM_JGIVE_ABOUT1');?></p>
		<p><?php echo JText::_('COM_JGIVE_ABOUT1');?></p>
		-->
		<?php
		//echo $pane->endPanel();
		?>
		</div>
</div>

<br/>
<div class="row-fluid2">
	<div class="span12">
		<div class="alert alert-info">
			<a class="btn"
			href="index.php?option=com_jgive&task=updateAllCampaignsSuccessStatus"
			target="_blank">
				<?php echo JText::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_1'); ?>
			</a>
			 <?php echo JText::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_2'); ?>
		</div>
	</div>
</div>
<br/>

<table style="margin-bottom: 5px; width: 100%; border-top: thin solid #e5e5e5; table-layout: fixed;">
	<tbody>
		<tr>
			<td style="text-align: left; width: 33%;">
				<a href="index.php?option=com_jgive&view=campaigns&layout=all_list">
				<a href="http://techjoomla.com/index.php?option=com_billets&view=tickets&layout=form&Itemid=18" target="_blank"><?php echo JText::_("COM_JGIVE_TECHJ_SUP"); ?></a>
				<br />
				<a href="http://twitter.com/techjoomla" target="_blank"><?php echo JText::_("COM_JGIVE_TJ_FOL_ON_TWIT"); ?></a>
				<br />
				<a href="http://www.facebook.com/techjoomla" target="_blank"><?php echo JText::_("COM_JGIVE_TJ_FOL_ON_FB"); ?></a>
				<br />
				<a href="http://extensions.joomla.org/extensions/extension-specific/jomsocial-extensions/17320" target="_blank"><?php echo JText::_( "COM_JGIVE_TJ_JED_FED" ); ?> </a>
			</td>
			<td style="text-align: center; width: 50%;"><?php echo JText::_("COM_JGIVE_TJ_PROD_INTRO" ); ?>
				<br />
				<?php echo JText::_("COM_JGIVE_TJ_COPYRIGHT"); ?>
				<br />
				<?php echo JText::_("COM_JGIVE_TJ_VERSION").' '.$currentversion; ?>
				<br />
				<span class="latestbutton" style="color: #0B55C4; cursor: pointer;" onclick="vercheck();"> <?php echo JText::_('COM_JGIVE_TJ_CHECK_LATEST_VERSION');?></span>
				<span id='NewVersion' style='padding-top: 5px; color: #000000; font-weight: bold; padding-left: 5px;'></span>
			</td>
			<td style="text-align: right; width: 33%;">
				<a href='http://techjoomla.com/' taget='_blank'> <img src="<?php echo JUri::base() ?>components/com_jgive/assets/images/techjoomla.png" alt="TechJoomla" style="vertical-align:text-top;"/></a>
			</td>
		</tr>
	</tbody>
</table>

<?php else: ?>
<div class="adminform row-fluid" >
	<div class= "cpanel-left span9 hidden-phone" >
		<div class="cpanel row-fluid">
			<div class="icon-wrapper span2">
				<div class="icon">
					<a class="thumbnail btn" href="index.php?option=com_jgive&view=campaigns&layout=all_list">
					<img src="<?php echo JUri::base()?>components/com_jgive/assets/images/icon-48-campaigns.png" alt="<?php echo JText::_("COM_JGIVE_CAMPAIGNS");?>"/>
					<span><?php echo JText::_("COM_JGIVE_CAMPAIGNS");?></span>
					</a>
				</div>
			</div>
			<div class="icon-wrapper span2">
				<div class="icon">
				<a class="thumbnail btn" href="index.php?option=com_categories&view=categories&extension=com_jgive">
				<img src="<?php echo JUri::base()?>components/com_jgive/assets/images/icon-48-categories.png" alt="<?php echo JText::_("COM_JGIVE_REPORTS");?>"/>
				<span><?php echo JText::_("COM_JGIVE_CATEGORIES");?></span>
				</a>
				</div>
			</div>
			<div class="icon-wrapper span2">
				<div class="icon">
				<a class="thumbnail btn" href="index.php?option=com_jgive&view=reports&layout=default">
				<img src="<?php echo JUri::base()?>components/com_jgive/assets/images/icon-48-reports.png" alt="<?php echo JText::_("COM_JGIVE_REPORTS");?>"/>
				<span><?php echo JText::_("COM_JGIVE_REPORTS");?></span>
				</a>
				</div>
			</div>
			<div class="icon-wrapper span2">
				<div class="icon">
				<a class="thumbnail btn" href="index.php?option=com_jgive&view=reports&layout=payouts">
				<img src="<?php echo JUri::base()?>components/com_jgive/assets/images/icon-48-payouts.png" alt="<?php echo JText::_("COM_JGIVE_PAYOUT_REPORTS");?>"/>
				<span><?php echo JText::_("COM_JGIVE_PAYOUT_REPORTS");?></span>
				</a>
				</div>
			</div>
			<div class="icon-wrapper span2">
				<div class="icon">
				<a class="thumbnail btn" href="index.php?option=com_jgive&view=donations&layout=all">
				<img src="<?php echo JUri::base()?>components/com_jgive/assets/images/icon-48-donations.png" alt="<?php echo JText::_("COM_JGIVE_DONATIONS");?>"/>
				<span><?php echo JText::_("COM_JGIVE_DONATIONS");?></span>
				</a>
				</div>
			</div>

		</div>
	</div>
	<div class="cpanel-right span3 width-40" >

		<?php
	echo JHtml::_('tabs.start', 'tab_group_id', $options);
		echo JHtml::_('tabs.panel', JText::_('COM_JGIVE_ABOUT1'), 'panel1');
		//echo $select=comquick2cartHelper::selectZooApps();

		?>
		<h3 style="color:#0B55C4;"><?php echo JText::_('');?></h3>
		<h4><b><?php echo JText::_('');?></b></h4>
		<br/><br/><br/><br/><br/>
		<!--
		<ol>
			<li><?php echo JText::_('');?></li>
			<li><?php echo JText::_('');?></li>
			<li><?php echo JText::_('');?></li>
		</ol>
		-->
		<p><?php echo JText::_('');?></p>
		<p><?php echo JText::_('');?></p>
		<?php
	echo JHtml::_('tabs.end');
		?>
		</div>
</div>

<div class="row-fluid">
	<div class="span12">
		<div class="alert alert-info">
			<a class="btn"
			href="index.php?option=com_jgive&task=updateAllCampaignsSuccessStatus"
			target="_blank">
				<?php echo JText::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_1'); ?>
			</a>
			 <?php echo JText::_('COM_JGIVE_UPDATE_CAMP_SUCCESS_STATUS_TASK_2'); ?>
		</div>
	</div>
</div>

<table style="margin-bottom: 5px; width: 100%; border-top: thin solid #e5e5e5; table-layout: fixed;">
	<tbody>
		<tr>
			<td style="text-align: left; width: 33%;">
				<a href="http://techjoomla.com/index.php?option=com_billets&view=tickets&layout=form&Itemid=18" target="_blank"><?php echo JText::_("COM_JGIVE_TECHJ_SUP"); ?></a>
				<br />
				<a href="http://twitter.com/techjoomla" target="_blank"><?php echo JText::_("COM_JGIVE_TJ_FOL_ON_TWIT"); ?></a>
				<br />
				<a href="http://www.facebook.com/techjoomla" target="_blank"><?php echo JText::_("COM_JGIVE_TJ_FOL_ON_FB"); ?></a>
				<br />
				<a href="http://extensions.joomla.org/extensions/communication/instant-messaging/9344" target="_blank"><?php echo JText::_( "COM_JGIVE_TJ_JED_FED" ); ?> </a>
			</td>
			<td style="text-align: center; width: 50%;"><?php echo JText::_("COM_JGIVE_ABOUT1" ); ?>
				<br />
				<?php echo JText::_("COM_JGIVE_TJ_COPYRIGHT"); ?>
				<br />
				<?php echo JText::_("COM_JGIVE_TJ_VERSION").' '.$currentversion; ?>
				<br />
				<span class="latestbutton" style="color: #0B55C4; cursor: pointer;" onclick="vercheck();"> <?php echo JText::_('COM_JGIVE_TJ_CHECK_LATEST_VERSION');?></span>
				<span id='NewVersion' style='padding-top: 5px; color: #000000; font-weight: bold; padding-left: 5px;'></span>
			</td>
			<td style="text-align: right; width: 33%;">
				<a href='http://techjoomla.com/' taget='_blank'> <img src="<?php echo JUri::base() ?>components/com_jgive/assets/images/techjoomla.png" alt="TechJoomla" style="vertical-align:text-top;"/></a>
			</td>
		</tr>
	</tbody>
</table>


<?php endif;?>

