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
define( 'COM_JGIVE_ICON_MINUS' , " icon-minus ");
define('COM_JGIVE_ICON_PLUS',"icon-plus-sign");

$document=JFactory::getDocument();
$user=JFactory::getUser();
$user_profile=JUserHelper::getProfile($user->id);
//load validation scripts
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.tooltip');
jimport( 'joomla.html.html.list' );
JHtml::_('behavior.modal', 'a.modal');
jimport('joomla.html.html.bootstrap');

$cdata='';
if(!empty($this->cdata))
{
	$cdata=$this->cdata;
}
if(!empty($cdata['campaign']->state))
$state=$cdata['campaign']->state;

if(!empty($cdata['campaign']->city))
$city=$cdata['campaign']->city;

$params=JComponentHelper::getParams('com_jgive');

//get the data to idetify which field to show

$show_selected_fields=$params->get('show_selected_fields');

$max_images_limit=$params->get('max_images',6);


$creatorfield=array();

$show_field=0;
$max_donation_cnf=0;
$min_donation_cnf=0;
$long_desc_cnf=0;
$allow_exceed_cnf=0;
$show_public_cnf=0;
$address_cnf=0;
$address2_cnf=0;
$zip_cnf=0;
$phone_cnf=0;
$group_name_cnf=0;
$website_address_cnf=0;
$internal_use_cnf=0;
$give_back_cnf=0;
$js_group=0;
$campaign_type_cnf=0;
$goal_amount=0;

if ($show_selected_fields)
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

			case 'min_donation':
				$min_donation_cnf=1;
			break;

			case 'long_desc':
				$long_desc_cnf=1;
			break;

			case 'allow_exceed':
				$allow_exceed_cnf=1;
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

			case 'internal_use':
				$internal_use_cnf=1;
			break;

			case 'give_back':
				$give_back_cnf=1;
			break;

			case 'js_group':
				$js_group=1;
			break;

			case 'campaign_type':
				$campaign_type_cnf=1;
			break;

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
// by default selected category at time of edit


$selected_cats=$this->cats;
//for icon
	if(JVERSION>=3.0)
	{
		$jgive_icon_plus=COM_JGIVE_ICON_PLUS;
	}
	else
	{ // for joomla3.0
		$jgive_icon_plus="icon-plus-sign ";
	}

?>

<style>
.invalid{border-color: #E9322D;color:red;}
</style>
<script type="text/javascript">


Joomla.submitbutton = function(action){
	var form = document.adminForm;
	if(action=='campaign.save' || action=='campaign.edit')
	{
		var goal_amount=techjoomla.jQuery('#goal_amount').val();
		var minimum_amount=techjoomla.jQuery('#minimum_amount').val();
		var daterangefrom=techjoomla.jQuery('#start_date').val();
		var daterangeto=techjoomla.jQuery('#end_date').val();

		if((parseFloat(goal_amount))<(parseFloat(minimum_amount)))
		{
			var msg="<?php echo JText::_('COM_JGIVE_GOAL_LESS_MINIMUM_AMT'); ?>";
			alert(msg);
			return false;
		}

		if(daterangefrom=='' && daterangeto==''){
		}
		var now=new Date();
		var year = now.getFullYear();
		var month = now.getMonth()+1;
		var date = now.getDate();
		if(date >=1 && date <=9)
		{
			var newdate = '0'+date;
		}
		else
		{
			var newdate = date;
		}
		if(month >=1 && month <=9)
		{
			var newmonth = '0'+month;
		}
		else
		{
			var newmonth = month;
		}

		today=year+'-'+newmonth+'-'+newdate;


		if((daterangefrom) > (daterangeto))
		{
			alert('<?php echo JText::_('COM_JGIVE_DATE_ERROR'); ?>');
			return false;
		}

		var validateflag = document.formvalidator.isValid(document.id('adminForm'));

		if(validateflag)
		{
			Joomla.submitform(action );
		}
		else
		{
			alert("<?php echo JText::_('COM_JGIVE_VALIDATATION_ERROR'); ?>");
			return false;
		}
	}
	else
	{
		Joomla.submitform(action );
	}
}

	var tabId=1;
	var lang_const_of="<?php echo JText::_('COM_JGIVE_STEPS');?>";
	techjoomla.jQuery(document).ready(function()
	{
		var state='',city='',category='';
		<?php if(!empty($state)) { ?>
		state="<?php echo $state;?>";
		<?php } ?>

		<?php if(!empty($city)) { ?>
		city="<?php echo $city;?>";

		<?php }
		if(!empty($selected_cats)) {
		} ?>



		generateState('country',state,city);
		// add required calss to category
		techjoomla.jQuery('#campaigncat_id').addClass("required");
		otherCity();

	});

	/*add clone script*/
	function addClone(rId,rClass)
	{
		var num=techjoomla.jQuery('.'+rClass).length;
		var removeButton="<div class='com_jgive_remove_button'>";
		removeButton+="<button class='btn btn-mini' type='button' id='remove"+num+"'";
		removeButton+="onclick=\"removeClone('jgive_container"+num+"','jgive_container');\" title=\"<?php echo JText::_('COM_JGIVE_REMOVE_TOOLTIP');?>\" >";
		removeButton+="<i class=\"icon-minus-sign\"></i></button>";
		removeButton+="</div>";
		var newElem=techjoomla.jQuery('#'+rId).clone().attr('id',rId+num);
		techjoomla.jQuery(newElem).children('.control-group').children('.controls').children('.control-group').children('.controls').children('.input-prepend,.input-append').children().each(function()
		{
			var kid=techjoomla.jQuery(this);
			if(kid.attr('id')!=undefined)
			{
				var idN=kid.attr('id');
				kid.attr('id',idN+num).attr('id',idN+num);
				kid.attr('title',idN+num).attr('title',idN+num);
				kid.attr('value','');
			}
		});
		techjoomla.jQuery(newElem).children('.control-group').children('.controls').children('.control-group').children('.controls').children().each(function()
		{
			var kid=techjoomla.jQuery(this);
			if(kid.attr('id')!=undefined)
			{
				var idN=kid.attr('id');
				kid.attr('id',idN+num).attr('id',idN+num);
				kid.attr('value','');
			}
		});

		techjoomla.jQuery('.'+rClass+':last').after(newElem);
		techjoomla.jQuery('div.'+rClass +' :last').append(removeButton);
	}

	function removeClone(rId,rClass){
		techjoomla.jQuery('#'+rId).remove();
	}

	/*
	To generate State list according to selected Country
	@param id of select list
	*/
	function generateState(countryId,state,city)
	{
		//alert(countryId);
		generateCity(countryId,city);
		var country=techjoomla.jQuery('#'+countryId).val();
		techjoomla.jQuery.ajax(
		{
			url:'<?php echo JUri::root();?>'+'index.php?option=com_jgive&task=loadState&country='+country+'&tmpl=component&format=raw',
			type:'GET',
			dataType:'json',
			success:function(data)
			{
				if (data === undefined || data == null || data.length <= 0)
				{
					var op='<option value="">'+"<?php echo JText::_('COM_JGIVE_STATE');?>"+'</option>';
					select=techjoomla.jQuery('#state');
					select.find('option').remove().end();
					select.append(op);
				}
				else{
					generateoption(data,countryId,state);
				}
			}
		});
	}
	function generateCity(countryId,city)
	{
		var country=techjoomla.jQuery('#'+countryId).val();
		techjoomla.jQuery.ajax(
		{
			url:'<?php echo JUri::root();?>'+'index.php?option=com_jgive&task=loadCity&country='+country+'&tmpl=component&format=raw',
			type:'GET',
			dataType:'json',
			success:function(data)
			{
				if (data === undefined || data == null || data.length <= 0)
				{
					var op='<option value="">'+"<?php echo JText::_('COM_JGIVE_CITY');?>"+'</option>';
					select=techjoomla.jQuery('#city');
					select.find('option').remove().end();
					select.append(op);
				}
				else{
					generateoptioncity(data,countryId,city);
				}
			}
		});
	}
	/*
	TO generate option
	@param: data=list of state/region in Json format
	countryID=called country select list
	Source ID which generate Option list
	*/
	//State
	function generateoption(data,countryId,state)
	{
		var options, index, select, option;
		if(countryId=='country'){
			select = techjoomla.jQuery('#state');
		}
		select.find('option').remove().end();
		options=data.options;
		for(index = 0; index < data.length; ++index)
		{
			var region=data[index];
			if(state==region['region'])
				var op="<option value="  +region['region_code']+  " selected='selected'>"  +region['region']+   '</option>'     ;
			else
				var op="<option value="  +region['region_code']+  ">"  +region['region']+   '</option>'     ;
			if(countryId=='country'){
				techjoomla.jQuery('#state').append(op);
			}
		}
	}
	// City
	function generateoptioncity(data,countryId,citydeafult)
	{
		var options, index, select, option;
		if(countryId=='country'){
			select = techjoomla.jQuery('#city');
		}
		select.find('option').remove().end();
		options=data.options;
		for(index = 0; index < data.length; ++index)
		{
			var city=data[index];
			if(citydeafult==city['city'])
			{
				var op="<option value="  +city['city_id']+  " selected='selected'>"  +city['city']+   '</option>'     ;
			}
			else
			{
				//alert('no re');
				var op="<option value="  +city['city_id']+  ">"  +city['city']+   '</option>'     ;
			}
			if(countryId=='country'){
				techjoomla.jQuery('#city').append(op);
			}
		}
	}

	function change_max_donors(el)
	{
		var selectBox = el;
		var selectedValue = selectBox.options[selectBox.selectedIndex].value;
		if(selectedValue=='investment'){
			techjoomla.jQuery('#max_donors').val(99);
		}else{
			techjoomla.jQuery('#max_donors').val(0);
		}
	}

	function validateForm()
	{
		var goal_amount=techjoomla.jQuery('#goal_amount').val();
		var minimum_amount=techjoomla.jQuery('#minimum_amount').val();
		var daterangefrom=techjoomla.jQuery('#start_date').val();
		var daterangeto=techjoomla.jQuery('#end_date').val();

		if((parseFloat(goal_amount))<(parseFloat(minimum_amount)))
		{
			var msg="<?php echo JText::_('COM_JGIVE_GOAL_LESS_MINIMUM_AMT'); ?>";
			alert(msg);
			return false;
		}

		if(daterangefrom=='' && daterangeto==''){
		}
		var now=new Date();
		var year = now.getFullYear();
		var month = now.getMonth()+1;
		var date = now.getDate();
		if(date >=1 && date <=9)
		{
			var newdate = '0'+date;
		}
		else
		{
			var newdate = date;
		}
		if(month >=1 && month <=9)
		{
			var newmonth = '0'+month;
		}
		else
		{
			var newmonth = month;
		}

		today=year+'-'+newmonth+'-'+newdate;
		/*
		if(((daterangefrom) < (today)) || ((daterangeto) < (today)))
		{
			alert('start date / end date can not be in past');
			return false;
		}
		**/

		if((daterangefrom) > (daterangeto))
		{
			alert('Start Date should not be greater than End Date');
			return false;
		}
	}
	function showTab(value)
	{
		jQuery('#submit-btn').hide();
		jQuery('#cancel-btn').hide();
		jQuery('#resent-btn').hide();

		jQuery("#tabContainer>ul>li.active").removeClass("active");
		if(value=='last')
		{
			jQuery('#steps').text('4 '+lang_const_of+' 4');
			jQuery('#jgive-progressbar').css('width','100%');
			jQuery('#tab'+tabId+'fb').hide();
			jQuery('#tab4fb').show();
			jQuery('#active'+tabId).removeClass('active');
			jQuery('#active4').addClass('active');
			tabId=4;

			//hide next & last btn
			jQuery('#next-btn').hide();
			jQuery('#last-btn').hide();
			//show form submit btn
			jQuery('#submit-btn').show();
			jQuery('#cancel-btn').show();
			jQuery('#resent-btn').show();
		}
		else if(value=='first')
		{
			jQuery('#steps').text('1 '+lang_const_of+' 4');
			jQuery('#jgive-progressbar').css('width','16%');
			jQuery('#tab'+tabId+'fb').hide();
			jQuery('#tab1fb').show();
			jQuery('#active'+tabId).removeClass('active');
			jQuery('#active1').addClass('active');
			tabId=1;
			jQuery('#next-btn').show();
			jQuery('#last-btn').show();
		}
		//alert(tabId);
		else if(value=='next')
		{
			switch(tabId)
			{
				case 1:
					jQuery('#steps').text('2 '+lang_const_of+' 4');
					tabId=tabId+1;
					jQuery('#jgive-progressbar').css('width','34%');
					jQuery('#tab1fb').hide();
					jQuery('#tab2fb').show();
					jQuery('#active1').removeClass('active');
					jQuery('#active2').addClass('active');
				break;

				case 2:
					jQuery('#steps').text('3 '+lang_const_of+' 4');
					tabId=tabId+1;
					jQuery('#jgive-progressbar').css('width','68%');
					jQuery('#tab2fb').hide();
					jQuery('#tab3fb').show();
					jQuery('#active2').removeClass('active');
					jQuery('#active3').addClass('active');
				break;

				case 3:
					jQuery('#steps').text('4 '+lang_const_of+' 4');
					tabId=tabId+1;
					jQuery('#jgive-progressbar').css('width','100%');
					jQuery('#tab3fb').hide();
					jQuery('#tab4fb').show();
					jQuery('#active3').removeClass('active');
					jQuery('#active4').addClass('active');
					//hide next & last btn
					jQuery('#next-btn').hide();
					jQuery('#last-btn').hide();
					//show form submit btn
					jQuery('#submit-btn').show();
					jQuery('#cancel-btn').show();
					jQuery('#resent-btn').show();
				break;

				case 4:
					jQuery('#tab4fb').show();
					jQuery('#active4').addClass('active');
					//hide next & last btn
					jQuery('#next-btn').hide();
					jQuery('#last-btn').hide();
					//show form submit btn
					jQuery('#submit-btn').show();
					jQuery('#cancel-btn').show();
					jQuery('#resent-btn').show();
				break;
			}
		}
		else if(value=='previous')
		{
			switch(tabId)
			{
				case 2:
					jQuery('#steps').text('1 '+lang_const_of+' 4');
					jQuery('#jgive-progressbar').css('width','16%');
					tabId=tabId-1;
					jQuery('#tab2fb').hide();
					jQuery('#tab1fb').show();
					jQuery('#active2').removeClass('active');
					jQuery('#active1').addClass('active');
				break;

				case 3:
					jQuery('#steps').text('2 '+lang_const_of+' 4');
					jQuery('#jgive-progressbar').css('width','34%');
					tabId=tabId-1;
					jQuery('#tab3fb').hide();
					jQuery('#tab2fb').show();
					jQuery('#active3').removeClass('active');
					jQuery('#active2').addClass('active');

				break;

				case 4:
					jQuery('#steps').text('3 '+lang_const_of+' 4');
					jQuery('#jgive-progressbar').css('width','68%');
					tabId=tabId-1;
					jQuery('#tab4fb').hide();
					jQuery('#tab3fb').show();
					jQuery('#active4').removeClass('active');
					jQuery('#active3').addClass('active');

					jQuery('#next-btn').show();
					jQuery('#last-btn').show();
				break;
			}
		}
	}
	function tabClick(value)
	{
		value=parseInt(value);
		jQuery("#tabContainer>ul>li.active").removeClass("active");

		jQuery('#tab'+tabId+'fb').hide();
		jQuery('#tab'+value+'fb').show();
		jQuery('#active'+tabId).removeClass('active');
		jQuery('#active'+value).addClass('active');
		tabId=value;

		jQuery('#submit-btn').hide();
		jQuery('#cancel-btn').hide();
		jQuery('#resent-btn').hide();
		//jgive-progressbar
		switch(value)
		{
			case 1:
				jQuery('#steps').text('1 '+lang_const_of+' 4');
				jQuery('#jgive-progressbar').css('width','16%');
				//hide next & last btn
				jQuery('#next-btn').show();
				jQuery('#last-btn').show();
			break;

			case 2:
				jQuery('#steps').text('2 '+lang_const_of+' 4');
				jQuery('#jgive-progressbar').css('width','34%');
				//hide next & last btn
				jQuery('#next-btn').show();
				jQuery('#last-btn').show();
			break;

			case 3:
				jQuery('#steps').text('3 '+lang_const_of+' 4');
				jQuery('#jgive-progressbar').css('width','68%');
				//hide next & last btn
				jQuery('#next-btn').show();
				jQuery('#last-btn').show();
			break;

			case 4:
				jQuery('#steps').text('4 '+lang_const_of+' 4');
				jQuery('#jgive-progressbar').css('width','100%');

				//hide next & last btn
				jQuery('#next-btn').hide();
				jQuery('#last-btn').hide();

				//show form submit btn
				jQuery('#submit-btn').show();
				jQuery('#cancel-btn').show();
				jQuery('#resent-btn').show();
			break;
		}
	}

	var imageid=0;
	function addmoreImg(rId,rClass)
	{
			var selected_imgs=techjoomla.jQuery('.qtc_img_checkbox:checked').length;
			var visible_file=techjoomla.jQuery('.filediv').length;
			var allowed_img=<?php echo $max_images_limit;?> ;
			var remaing_imgs= new Number(allowed_img - selected_imgs - visible_file);
			if(remaing_imgs > 0)
			{
				imageid++;
				//var num=techjoomla.jQuery('.'+rClass).length;
				var num=imageid;
				/*console.log('div total= '+num);*/
				var pre = new Number(num - 1);
				var removeButton="<span class=''>";
				removeButton+="<button class='btn btn-danger btn-mini' type='button' id='remove"+num+"'";
				removeButton+="onclick=\"removeClone('filediv"+num+"','jgive_container');\" title=\"<?php echo JText::_('COM_JGIVE_REMOVE_TOOLTIP');?>\" >";
				removeButton+="<i class=\"<?php echo COM_JGIVE_ICON_MINUS;?> icon-white \"></i></button>";
				removeButton+="</span>";

				// create the new element via clone(), and manipulate it's ID using newNum value
				//if(num==1)
				{
					var newElem = techjoomla.jQuery('#' +rId).clone().attr('id', rId + num);
					var delid=rId;
				}
				//else
				/*{
					var newElem = techjoomla.jQuery('#' +rId+pre).clone().attr('id', rId + num);
					var delid=rId + pre;
				}*/
				newElem.find('.addmore').attr('id','addmoreid'+ num);
				//newElem.find(':file').attr('name','jgive_img_gallery'+ imageid);
				removeClone('addmoreid'+pre ,'addmoreid'+pre );
				techjoomla.jQuery('.'+rClass+':last').after(newElem);
				techjoomla.jQuery('#'+rId+num).append(removeButton);
			}
			else
			{
				alert("<?php echo JText::sprintf('COM_JGIVE_U_ALLOWD_TO_UPLOAD_IMGES',$max_images_limit)?>");
			}
	}

	function otherCity()
	{
		if(document.adminForm.other_city_check.checked===true)
		{
			jQuery("#other_city").show();
			jQuery("#hide_city").hide();
		}
		else
		{
			jQuery("#hide_city").show();
			jQuery("#other_city").hide();
		}
	}
	function addRemoveRequired()
	{
		video=parseInt(techjoomla.jQuery('input:radio[name="video_img"]:checked').val());
		if(video==1)
		{
			techjoomla.jQuery('#video_url_imput_id').addClass('required');
		}
		else
		{
			techjoomla.jQuery('#video_url_imput_id').removeClass('required');
		}
	}
	function jSelectUser_jform_created_by(id, title)
	{
			var old_id = document.getElementById("promoter_id").value;
			if (old_id != id) {
				document.getElementById("promoter_id").value = id;
				document.getElementById("promoter_name").value = title;

			}
		SqueezeBox.close();
	}

</script>

<div class="techjoomla-bootstrap">

	<div class="span12">
		<div class="page-header">
			<h3>
				<?php
				if($this->task=='save'){
					echo JText::_('COM_JGIVE_CREATE_NEW_CAMPAIGN');
				}elseif($this->task=='edit'){
					echo JText::_('COM_JGIVE_EDIT_CAMPAIGN_HEADER');
				}
				?>
			</h3>
		</div>
		<div class="">

			<form action="" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data"
			class="form-horizontal form-validate form-validate" onsubmit="return validateForm();">

				<div class="">
					<div class="navbar-inner">
						<div class="" id="tabContainer">
							<ul class="nav nav-pills " id="myTab ">
								<li class="active" id="active1" onclick="tabClick('1')"><a data-toggle="tab" href="#tab1fb"><?php echo JText::_('COM_JGIVE_CAMPAIGN_DETAIL');?></a></li>
								<li id="active2" onclick="tabClick('2')"><a data-toggle="tab" href="#tab2fb"><?php echo JText::_('COM_JGIVE_PROMOTER_DETAILS');?></a> </li>

								<?php
								$params=JComponentHelper::getParams('com_jgive');
								//if($params->get('show_give_back'))
								if($show_field==1 OR $give_back_cnf==0 )
								{
								?>
								<li id="active3" onclick="tabClick('3')"><a data-toggle="tab" href="#tab3fb"><?php echo JText::_('COM_JGIVE_GIVE_BACK_DETAILS');?></a></li>
								<?php
								} ?>

								<li id="active4" onclick="tabClick('4')"><a data-toggle="tab" href="#tab4fb"><?php echo JText::_('COM_JGIVE_MEDIA');?></a></li>
							</ul>
							<div  id="steps" class="number-page pull-right jgive_steps"><b>1 <?php echo JText::_('COM_JGIVE_STEPS'); ?> 4</b></div>
						</div>
					</div>
				</div>
				<div class="progress jgive_progress progress-info progress-mini no-margin" id="bar">
					<div class="bar jgive-progressbar" id="jgive-progressbar"></div>
				</div>
				<div class="tab-content section-content item">

					<div id="tab1fb" class="tab-pane active">
						<div class="row-fluid">
							<div class="span12">
								<fieldset>
									<legend><i class="fontello-icon-bag"></i> <span> <?php echo JText::_('COM_JGIVE_CAMPAIGN_DETAIL');?></span></legend>
									<div class="control-group">
										<label class="control-label" for="title" title="<?php echo JText::_('COM_JGIVE_TITLE_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_TITLE');?>
										</label>
										<div class="controls">
											<input type="text" id="title" name="title" class="required" required="required" maxlength="250" placeholder="<?php echo JText::_('COM_JGIVE_TITLE');?>"
											value="<?php if(isset($cdata['campaign']->title)) echo $cdata['campaign']->title;?>">
										</div>
									</div>

									<?php
										$donation=$investment='';
										if(isset($cdata['campaign']->type))
										{
											if($cdata['campaign']->type=='donation')
												$donation='selected';
											else
												$investment='selected';
										}
										else{
											$donation='selected';
										}
									?>
									<div class="control-group">
										<label class="control-label" for="type" title="<?php echo JText::_('COM_JGIVE_TYPE_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_TYPE');?>
										</label>
										<div class="controls">
											<?php
											$params=JComponentHelper::getParams('com_jgive');
											$count=count($params->get('camp_type'));
											//check for admin set the allowed campaigns to created are multiple
											if($count>1) { ?>
											<select id="type" name="type" class="required" onchange="change_max_donors(this)">
												<option id="donation" value="donation" <?php echo $donation;?>><?php echo JText::_('COM_JGIVE_CAMPAIGN_TYPE_DONATION');?></option>
												<option id="investment" value="investment" <?php echo $investment;?>><?php echo JText::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT');?></option>
											</select>
											<?php } else { //if admin set single type of campaigns to created then show this type in text box. ?>
											<input type="text" name="type"  value="<?php $type_array=$params->get('camp_type'); echo $type_array[0]=='investment' ? JText::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT'):JText::_('COM_JGIVE_CAMPAIGN_TYPE_DONATION'); ?>" disabled="disabled">
											<?php } ?>
										</div>
									</div>

									<div class="control-group">
										<label class="control-label" for="campaign_category" title="<?php echo JText::_('COM_JGIVE_CATEGORY_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_CATEGORY');?>
										</label>
										<div class="controls">
											<?php
												echo  $this->cats;
											?>
										</div>
									</div>
									<?php
										$non_profit=$self_help=$individuals='';
										if(isset($cdata['campaign']->org_ind_type))
										{
											if($cdata['campaign']->org_ind_type=='non_profit')
												$non_profit='selected';
											else if($cdata['campaign']->org_ind_type=='self_help')
												$self_help='selected';
											else if($cdata['campaign']->org_ind_type=='individuals')
												$individuals='selected';
										}
									?>
									<div class="control-group">
										<label class="control-label" for="org_ind_type" title="<?php echo JText::_('COM_JGIVE_TYPE_ORG_INDIVIDUALS_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_TYPE_ORG_INDIVIDUALS');?>
										</label>
										<div class="controls">
											<select id="org_ind_type" name="org_ind_type">
												<option id="non_profit" value="non_profit" <?php echo $non_profit;?>><?php echo JText::_('COM_JGIVE_ORG_NON_PROFIT'); ?></option>
												<option id="self_help" value="self_help" <?php echo $self_help;?>><?php echo JText::_('COM_JGIVE_SELF_HELP'); ?></option>
												<option id="individuals" value="individuals" <?php echo $individuals;?>><?php echo JText::_('COM_JGIVE_SELF_INDIVIDUALS'); ?></option>
											</select>
										</div>
									</div>

								<?php if($show_field==1 OR $max_donation_cnf==0 ): ?>
									<div class="control-group">
										<label class="control-label" for="max_donors" title="<?php echo JText::_('COM_JGIVE_MAX_DONORS_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_MAX_DONORS');?>
										</label>
										<div class="controls">
											<input type="text" id="max_donors" name="max_donors" class="required validate-numeric" maxlength="11" placeholder="<?php echo JText::_('COM_JGIVE_MAX_DONORS');?>"
											value="<?php
											if(isset($cdata['campaign']->max_donors))
												echo $cdata['campaign']->max_donors;
											else
												echo 0;
											?>">
										</div>
									</div>
								<?php endif;?>
								<!--if added by Sneha-->
								<?php if($show_field==1 OR $goal_amount==0 ): ?>
									<div class="control-group">
										<label class="control-label" for="goal_amount" title="<?php echo JText::_('COM_JGIVE_GOAL_AMOUNT_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_GOAL_AMOUNT');?>
										</label>
										<div class="controls">
											<div class="input-append">
												<input type="text" id="goal_amount" name="goal_amount" class="required validate-numeric" maxlength="11" placeholder="<?php echo JText::_('COM_JGIVE_GOAL_AMOUNT');?>"
												value="<?php if(isset($cdata['campaign']->goal_amount)) echo $cdata['campaign']->goal_amount;?>">
												<span class="add-on"><?php echo $this->currency_code;?></span>
											</div>
										</div>
									</div>
								<?php endif;?>
								<!--End added by Sneha-->
								<?php if($show_field==1 OR $min_donation_cnf==0 ): ?>
									<div class="control-group">
										<label class="control-label" for="minimum_amount" title="<?php echo JText::_('COM_JGIVE_MINIMUM_AMOUNT_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_MINIMUM_AMOUNT');?>
										</label>
										<div class="controls">
											<div class="input-append">
												<input type="text" id="minimum_amount" name="minimum_amount" class="required validate-numeric" maxlength="11" placeholder="<?php echo JText::_('COM_JGIVE_MINIMUM_AMOUNT');?>"
												value="<?php
												if(isset($cdata['campaign']->minimum_amount))
													echo $cdata['campaign']->minimum_amount;
												else
													echo 0;
												?>">
												<span class="add-on"><?php echo $this->currency_code;?></span>
											</div>
										</div>
									</div>
								<?php endif;?>
									<div class="control-group">
										<label class="control-label" for="short_desc" title="<?php echo JText::_('COM_JGIVE_SHORT_DESC_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_SHORT_DESC');?>
										</label>
										<div class="controls">
											<textarea rows="3" cols="50" id="short_desc" name="short_desc" maxlength="250" class="required" placeholder="<?php echo JText::_('COM_JGIVE_SHORT_DESC');?>"><?php if(isset($cdata['campaign']->short_description)) echo $cdata['campaign']->short_description;?></textarea>
										</div>
									</div>
								<?php if($show_field==1 OR $long_desc_cnf==0 ): ?>
									<div class="control-group">
										<label class="control-label" for="long_desc" title="<?php echo JText::_('COM_JGIVE_LONG_DESC_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_LONG_DESC');?>
										</label>

										<div class="controls">
											<?php
												$params = array( 'safehtml'=> 'true' ,);
												$editor      =JFactory::getEditor();
												if(!isset($cdata['campaign']->long_description))
												{
													$cdata['campaign']=new Stdclass;
													$cdata['campaign']->long_description='';
												}
												echo $editor->display('long_desc',$cdata['campaign']->long_description,'95%','100',5,50,false);
											?>
										</div>
									</div>
								<?php endif;?>
								<!--rearange  -->
									<div class="control-group">
										<label class="control-label" for="start_date" title="<?php echo JText::_('COM_JGIVE_START_DATE_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_START_DATE');?>
										</label>
										<div class="controls">
											<?php
												$date=JFactory::getDate()->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));//set date to current date
												if(isset($cdata['campaign']->start_date))
												{
													$cdata['campaign']->start_date=str_replace('0000-00-00','',$cdata['campaign']->start_date);
													$date=JFactory::getDate($cdata['campaign']->start_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
												}
												echo $calendar=JHtml::_('calendar',$date,'start_date','start_date',JText::_('COM_JGIVE_DATE_FORMAT'));
												echo "<br/>";
												echo "<i>".JText::_('COM_JGIVE_DATE_FORMAT_DESC')."</i>";
											?>

										</div>
									</div>

									<div class="control-group">
										<label class="control-label" for="end_date" title="<?php echo JText::_('COM_JGIVE_END_DATE_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_END_DATE');?>
										</label>
										<div class="controls">
											<?php
												$end_date=JFactory::getDate()->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));//set date to current date
												if(isset($cdata['campaign']->end_date))
												{
													$cdata['campaign']->end_date=str_replace('0000-00-00','',$cdata['campaign']->end_date);
													$end_date=JFactory::getDate($cdata['campaign']->end_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
												}
												echo $calendar= JHtml::_('calendar',$end_date,'end_date','end_date',JText::_('COM_JGIVE_DATE_FORMAT'));
												echo "<br/>";
												echo "<i>".JText::_('COM_JGIVE_DATE_FORMAT_DESC')."</i>";
											?>
										</div>
									</div>

									<?php
									if(!$this->admin_approval)
									{
										$publish1=$publish2='';
										if(isset($cdata['campaign']->published))
										{
											if($cdata['campaign']->published)
												$publish1='checked';
											else
												$publish2='checked';
										}else{
											$publish1='checked';
										}
										?>
										<div class="control-group">
											<label class="control-label" for="publish1" title="<?php echo JText::_('COM_JGIVE_PUBLISH_TOOLTIP');?>">
												<?php echo JText::_('COM_JGIVE_PUBLISH');?>
											</label>
											<div class="controls">
												<label class="radio inline">
													<input type="radio" name="publish" id="publish1" value="1" <?php echo $publish1;?> >
														<?php echo JText::_('COM_JGIVE_YES');?>
												</label>
												<label class="radio inline">
													<input type="radio" name="publish" id="publish2" value="0" <?php echo $publish2;?>>
														<?php echo JText::_('COM_JGIVE_NO');?>
												</label>
											</div>
										</div>
									<?php
									}
									?>
<!--Condition changed by Sneha-->
								<?php if($show_field==1 OR ($allow_exceed_cnf==0 AND $goal_amount==0 )): ?>
									<?php
										$allow_exceed1=$allow_exceed2='';
										if(isset($cdata['campaign']->allow_exceed))
										{
											if($cdata['campaign']->allow_exceed)
												$allow_exceed1='checked';
											else
												$allow_exceed2='checked';
										}else{
											$allow_exceed1='checked';
										}
									?>
									<div class="control-group">
										<label class="control-label" for="allow_exceed1" title="<?php echo JText::_('COM_JGIVE_ALLOW_DONATIONS_EXCEED_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_ALLOW_DONATIONS_EXCEED');?>
										</label>
										<div class="controls">
											<label class="radio inline">
												<input type="radio" name="allow_exceed" id="allow_exceed1" value="1" <?php echo $allow_exceed1;?>>
													<?php echo JText::_('COM_JGIVE_YES');?>
											</label>
											<label class="radio inline">
												<input type="radio" name="allow_exceed" id="allow_exceed2" value="0" <?php echo $allow_exceed2;?>>
													<?php echo JText::_('COM_JGIVE_NO');?>
											</label>
										</div>
									</div>
								<?php endif; ?>

								<?php if($show_field==1 OR $show_public_cnf==0 ): ?>
									<?php
										$show_public1=$show_public2='';
										if(isset($cdata['campaign']->allow_view_donations))
										{
											if($cdata['campaign']->allow_view_donations)
												$show_public1='checked';
											else
												$show_public2='checked';
										}else{
											$show_public1='checked';
										}
									?>
									<div class="control-group">
										<label class="control-label"  for="show_public1" title="<?php echo JText::_('COM_JGIVE_SHOW_DONATIONS_TO_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_SHOW_DONATIONS_TO');?>
										</label>
										<div class="controls">
											<label class="radio inline">
												<input type="radio" name="show_public" id="show_public1" value="1" <?php echo $show_public1;?>>
													<?php echo JText::_('COM_JGIVE_YES');?>
											</label>
											<label class="radio inline">
												<input type="radio" name="show_public" id="show_public2" value="0" <?php echo $show_public2;?>>
													<?php echo JText::_('COM_JGIVE_NO');?>
											</label>
										</div>
									</div>
								<?php endif;?>
								<?php
								$params=JComponentHelper::getParams('com_jgive');
								$integration=$params->get('integration');
								 if($integration=='jomsocial'){
								 if($show_field==1 OR $js_group==0 ):
									$count=count($this->js_groups);
									if($count>=1) { ?>
									<div class="control-group">
										<label class="control-label" for="js_group" title="<?php echo JText::_('COM_JGIVE_SELECT_GROUP_TP');?>">
											<?php echo JText::_('COM_JGIVE_SELECT_GROUP');?>
										</label>
										<div class="controls">
											<select id="js_group" name="js_group" class="">
												<option value="0"><?php echo JText::_('COM_JGIVE_SELECT_JS_GROUP'); ?></option>
												<?php
												foreach($this->js_groups as $grp){
													$selected='';
													if($grp['id']==$cdata['campaign']->js_groupid)
														$selected='selected="selected"';
													 ?>
													<option value="<?php echo $grp['id']; ?>"<?php echo $selected;?> >
													<?php echo $grp['title'];?></option>
													<?php
												} ?>
											</select>
										</div>
									</div>
								<?php } ?>
								<?php endif;
								}
								?>
								</fieldset>
							</div>
						</div>
					</div>
					<!-- // tab1 -->

					<div id="tab2fb" class="tab-pane">
						<div class="row-fluid">
							<div class="span12">
								<fieldset>
									<legend><i class="fontello-icon-bag"></i> <span> <?php echo JText::_('COM_JGIVE_PROMOTER_DETAILS');?></span></legend>

									<div class="control-group jgive_select_user">
										<label class="control-label" for="promoter_name" title="<?php echo JText::_('COM_JGIVE_CAMPAIGN_PROMOTER_LABEL');?>">
											<?php echo JText::_('COM_JGIVE_CAMPAIGN_PROMOTER_LABEL_SELECT');?>
										</label>
										<div class="controls">

											<input type="text" id="promoter_name" name="promoter_name" class="required" disabled="disabled"
											placeholder="<?php echo JText::_('COM_JGIVE_CAMPAIGN_PROMOTER_LABEL');?>" value="<?php if(isset($cdata['campaign']->creator_id)) echo JFactory::getUser($cdata['campaign']->creator_id)->name; else echo JFactory::getUser()->name; ?>">

											<input type="hidden" id="promoter_id" name="promoter_id" class="required"
											 value="<?php if(isset($cdata['campaign']->creator_id)) echo $cdata['campaign']->creator_id;else echo JFactory::getUser()->id ?>">

												<a class="modal  button btn btn-info btn-small" rel="{handler: 'iframe', size: {x: 800, y: 500}}" href="index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=jform_created_by" title="Select User" class="modal_jform_created_by">
													<?php echo JText::_('COM_JGIVE_CAMPAIGN_PROMOTER_LABEL_SELECT');?></a>
										</div>
									</div>



									<div class="control-group">
										<label class="control-label" for="first_name" title="<?php echo JText::_('COM_JGIVE_FIRST_NAME_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_FIRST_NAME');?>
										</label>
										<div class="controls">
											<input type="text" id="first_name" name="first_name" class="required" placeholder="<?php echo JText::_('COM_JGIVE_FIRST_NAME');?>"
											value="<?php if(isset($cdata['campaign']->first_name)) echo $cdata['campaign']->first_name;?>">
										</div>
									</div>

									<div class="control-group">
										<label class="control-label" for="last_name" title="<?php echo JText::_('COM_JGIVE_LAST_NAME_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_LAST_NAME');?>
										</label>
										<div class="controls">
											<input type="text" id="last_name" name="last_name" class="required" placeholder="<?php echo JText::_('COM_JGIVE_LAST_NAME');?>"
											value="<?php if(isset($cdata['campaign']->last_name)) echo $cdata['campaign']->last_name;?>">
										</div>
									</div>
								<?php if($show_field==1 OR $address_cnf==0 ): ?>
									<div class="control-group">
										<label class="control-label" for="address" title="<?php echo JText::_('COM_JGIVE_ADDRESS_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_ADDRESS');?>
										</label>
										<div class="controls">
											<input type="text" id="address" name="address" class="required" placeholder="<?php echo JText::_('COM_JGIVE_ADDRESS');?>"
											value="<?php if(isset($cdata['campaign']->address)) echo $cdata['campaign']->address;?>">
										</div>
									</div>
								<?php endif;?>
								<?php if($show_field==1 OR $address2_cnf==0 ): ?>
									<div class="control-group">
										<label class="control-label" for="address2" title="<?php echo JText::_('COM_JGIVE_ADDRESS2_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_ADDRESS2');?>
										</label>
										<div class="controls">
											<input type="text" id="address2" name="address2" placeholder="<?php echo JText::_('COM_JGIVE_ADDRESS2');?>"
											value="<?php if(isset($cdata['campaign']->address2)) echo $cdata['campaign']->address2;?>">
										</div>
									</div>
								<?php endif;?>
									<div class="control-group">
										<label class="control-label" for="country" title="<?php echo JText::_('COM_JGIVE_COUNTRY_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_COUNTRY');?>
										</label>
										<div class="controls">
											<?php
											$countries=$this->countries;
											$default=NULL;
											if(isset($cdata['campaign']->country)){
												$default=$cdata['campaign']->country_id;
											}else{
												$default=$this->default_country;
											}
											$options=array();
											$options[]=JHtml::_('select.option',"",JText::_('COM_JGIVE_COUNTRY'));
											foreach($countries as $key=>$value)
											{
												$country=$countries[$key];
												$id=$country['country_id'];
												$value=$country['country'];
												$options[]=JHtml::_('select.option', $id, $value);
											}
											if(empty($cdata['campaign']->state))
											{	$cdata['campaign']->state='';
												$cdata['campaign']->city='';
											}
											echo $this->dropdown=JHtml::_('select.genericlist',$options,'country','required="required" aria-invalid="false" size="1" onchange="generateState(id,\''.$cdata['campaign']->state.'\',\''.$cdata['campaign']->city.'\')"','value','text',$default,'country');
											?>
										</div>
									</div>

									<div class="control-group">
										<label class="control-label" for="state" title="<?php echo JText::_('COM_JGIVE_STATE_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_STATE');?>
										</label>
										<div class="controls">
											<select name="state" id="state"></select>
										</div>
									</div>

									<div class="control-group" id="hide_city">
										<label class="control-label" for="city" title="<?php echo JText::_('COM_JGIVE_CITY_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_CITY');?>
										</label>
										<div class="controls">
											<select name="city" id="city"></select>
										</div>
									</div>

									<?php
										$other_city_checked='';
										if(!empty($cdata['campaign']->other_city))
										{
											$other_city_checked='checked="checked"';
										}
									?>
									<div class="control-group">
										<label class="control-label" for="other_city" title="<?php echo JText::_('COM_JGIVE_OTHER_CITY_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_OTHER_CITY');?>
										</label>
										<div class="controls">
											<input type="checkbox" name="other_city_check" id="other_city_check" <?php echo $other_city_checked;?>  onchange="otherCity()"/>
											<?php echo JText::_('COM_JGIVE_CHECK_OTHER_CITY_MSG'); ?> <br/><br/>
											<input type="text" name="other_city" id="other_city" placeholder="<?php echo JText::_('COM_JGIVE_ENTER_OTHER_CITY');?>" value="<?php echo $cdata['campaign']->city; ?>" >
										</div>
									</div>


								<?php if($show_field==1 OR $zip_cnf==0 ): ?>
									<div class="control-group">
										<label class="control-label" for="zip" title="<?php echo JText::_('COM_JGIVE_ZIP_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_ZIP');?>
										</label>
										<div class="controls">
											<input type="text" id="zip" name="zip" class="required" placeholder="<?php echo JText::_('COM_JGIVE_ZIP');?>"
											value="<?php if(isset($cdata['campaign']->zip)) echo $cdata['campaign']->zip;?>">
										</div>
									</div>
								<?php endif;?>
								<?php if($show_field==1 OR $phone_cnf==0 ): ?>
									<div class="control-group">
										<label class="control-label" for="phone" title="<?php echo JText::_('COM_JGIVE_PHONE_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_PHONE');?>
										</label>
										<div class="controls">
											<input type="text" id="phone" name="phone" class="required" placeholder="<?php echo JText::_('COM_JGIVE_PHONE');?>"
											value="<?php if(isset($cdata['campaign']->phone)) echo $cdata['campaign']->phone;?>">
										</div>
									</div>
								<?php endif;?>
								<!-- group name & website (blk) -->
								<?php if($show_field==1 OR $group_name_cnf==0 ): ?>
									<div class="control-group">
										<label class="control-label" for="group_name" title="<?php echo JText::_('COM_JGIVE_GROUP_NAME_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_GROUP_NAME');?>
										</label>
										<div class="controls">
											<input type="text" id="group_name" name="group_name" class="" placeholder="<?php echo JText::_('COM_JGIVE_GROUP_NAME');?>"
											value="<?php if(isset($cdata['campaign']->group_name)) echo $cdata['campaign']->group_name;?>">
										</div>
									</div>
								<?php endif; ?>
								<?php if($show_field==1 OR $website_address_cnf==0 ): ?>
									<div class="control-group">
										<label class="control-label" for="website_address" title="<?php echo JText::_('COM_JGIVE_WEBSITE_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_WEBSITE');?>
										</label>
										<div class="controls">
											<input type="text" id="website_address" name="website_address" class="" placeholder="<?php echo JText::_('COM_JGIVE_WEBSITE');?>"
											value="<?php if(isset($cdata['campaign']->website_address)) echo $cdata['campaign']->website_address;?>">
										</div>
									</div>
								<?php endif; ?>
									<?php
									if($this->send_payments_to_owner){
										$paypal_required='required';
									}else{
										$paypal_required=' ';
									}
									?>
									<div class="control-group">
										<label class="control-label" for="paypal_email" title="<?php echo JText::_('COM_JGIVE_PAYPAL_EMAIL_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_PAYPAL_EMAIL');?>
										</label>
										<div class="controls">
											<div class="input-prepend">
												<span class="add-on"><i class="icon-envelope"></i></span>
												<input type="text" id="paypal_email" name="paypal_email" class="<?php echo $paypal_required;?> validate-email" placeholder="<?php echo JText::_('COM_JGIVE_PAYPAL_EMAIL');?>"
												value="<?php if(isset($cdata['campaign']->paypal_email)) echo $cdata['campaign']->paypal_email;?>">
											</div>
										</div>
									</div>

								<?php if($show_field==1 OR $internal_use_cnf==0 ): ?>
									<div class="control-group">
										<label class="control-label" for="internal_use" title="<?php echo JText::_('COM_JGIVE_INTERNAL_USE_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_INTERNAL_USE');?>
										</label>
										<div class="controls">
											<textarea rows="5" cols="50" id="internal_use" name="internal_use" placeholder="<?php echo JText::_('COM_JGIVE_INTERNAL_USE_PLACEHOLDER');?>"><?php if(isset($cdata['campaign']->internal_use)) echo $cdata['campaign']->internal_use;?></textarea>
										</div>
									</div>
								<?php endif; ?>


								</fieldset>
							</div>
						</div>
					</div>
					<!-- // tab3 -->

				<?php
				$params=JComponentHelper::getParams('com_jgive');
				//if($params->get('show_give_back'))
				if($show_field==1 OR $give_back_cnf==0 )
				{
				?>

					<!--tab3fb-->

					<div id="tab3fb" class="tab-pane">
						<div class="row-fluid">
							<div class="span12">
								<fieldset>



									<div class="row-fluid">
										<div class="span12">
											<legend><i class="fontello-icon-bag"></i> <span> <?php echo JText::_('COM_JGIVE_GIVE_BACK_DETAILS');?></span></legend>
											<div class="control-group">
												<label class="control-label" title="<?php echo JText::_('COM_JGIVE_GIVE_BACK_TOOLTIP');?>">
													<strong><?php echo JText::_('COM_JGIVE_GIVE_BACK_DETAILS');?></strong>
												</label>
												<div class="controls">
												</div>
											</div>

											<?php

												if(isset($cdata['givebacks']))//for edit - recreate giveback blocks
												{
													$i=1;
													foreach($cdata['givebacks'] as $giveback)
													{
													?>
														<div id="jgive_container<?php echo $i;?>" class="jgive_container">
															<div class="com_jgive_repeating_block" >
																<div class="control-group">
																	<label class="control-label" for="give_back_value" title="<?php echo JText::_('COM_JGIVE_GIVE_BACK_VALUE_TOOLTIP');?>" >
																		<?php echo JText::_('COM_JGIVE_GIVEBACK_VALUE');?>
																	</label>
																	<div class="controls">
																		 <div class="input-prepend input-append">
																			<input type="hidden"  class="" name="ids[]" value="<?php echo $giveback->id; ?>" >
																			<input type="hidden"  class="" name="give_back_order[]" value="<?php echo $giveback->order; ?>" >

																			<input type="text" id="give_back_value<?php echo $i;?>" name="give_back_value[]"  placeholder="<?php echo JText::_('COM_JGIVE_GIVEBACK_VALUE');?>" value="<?php echo $giveback->amount;?>" class="validate-numeric">
																			<span class="add-on"><?php echo $this->currency_code;?></span>
																		</div>
																	</div>
																</div>
																<div class="control-group">
																	<label class="control-label"  title="<?php echo JText::_('COM_JGIVE_GIVE_BACK_DETAILS_TOOLTIP');?>" >
																		<?php echo JText::_('COM_JGIVE_GIVE_BACK_DETAILS');?>
																	</label>
																	<div class="controls">
																		<textarea rows="4" cols="50" id="give_back_details<?php echo $i;?>" name="give_back_details[]" placeholder="<?php echo JText::_('COM_JGIVE_GIVE_BACK_DETAILS');?>"><?php echo $giveback->description;?></textarea>
																	</div>
																</div>


															<!-- Added by Sneha -->
																<div class="control-group">
																	<label class="control-label" title="<?php echo JText::_('COM_JGIVE_GIVE_BACK_QUANTITY_TOOLTIP');?>" >
																		<?php echo JText::_('COM_JGIVE_GIVEBACK_QUANTITY');?>
																	</label>
																	<div class="controls">
																		<input type="text" id="give_back_quantity<?php echo $i;?>" name="give_back_quantity[]" placeholder="<?php echo JText::_('COM_JGIVE_GIVEBACK_QUANTITY');?>" value="<?php echo $giveback->total_quantity;?>" >
																	</div>
																</div>
																<div class="control-group">
																	<label class="control-label" ><?php echo JText::_('COM_JGIVE_IMAGE');?></label>
																		<div class="controls">

																			<?php //print_r($cdata['givebacks']);
																			if(isset($cdata['givebacks']) && count($cdata['givebacks']))
																			{
																				?>
																				<input type="file" id="coupon_image[<?php echo $i;?>]" name="coupon_image[]" placeholder="<?php echo JText::_('COM_JGIVE_IMAGE');?>" accept="image/*">

																				<div class="text-warning">
																					<?php echo JText::_('COM_JGIVE_EXISTING_IMAGE_MSG');?>
																				</div>
																				<div class="text-info">
																					<?php echo JText::_('COM_JGIVE_EXISTING_IMAGE');?>
																				</div>
																				<div>
																					<?php
																					//foreach($cdata['images'] as $img){		//print_r($cdata['images']);
																						echo "<img class='img-rounded com_jgive_img_128_128' src='".JUri::root().$giveback->image_path."' />";
																					//}
																					?>
																				</div>
																				<?php
																			}
																			else//while editing image field is not required
																			{
																				?>
																				<input type="file" id="coupon_image" name="coupon_image[]" placeholder="<?php echo JText::_('COM_JGIVE_IMAGE');?>" class="required" accept="image/*">
																				<?php
																			}
																			?>
																		</div>
																</div>
															<!--Added by Sneha ends-->


															</div>

															<div class='com_jgive_remove_button'>
																<button class='btn btn-mini btn-primary' type='button' id='remove<?php echo $i;?>'
																	onclick="removeClone('jgive_container<?php echo $i;?>','jgive_container');" title="<?php echo JText::_('COM_JGIVE_REMOVE_TOOLTIP');?>" >
																	<i class="icon-minus-sign icon-white"></i>
																</button>
															</div>
														</div>
													<?php
													$i++;
													}
												}

											?>

											<!--This is a repating block of html-->
											<div id="jgive_container" class="jgive_container">
												<div class="com_jgive_repeating_block">
													<div class="control-group">
														<label class="control-label" for="give_back_value" title="<?php echo JText::_('COM_JGIVE_GIVE_BACK_VALUE_TOOLTIP');?>" >
															<?php echo JText::_('COM_JGIVE_GIVEBACK_VALUE');?>
														</label>
														<div class="controls">
															 <div class="input-prepend input-append">
																<input type="hidden"  class="" name="ids[]" value="" >
																<input type="hidden"  class="" name="give_back_order[]" value="" >
																<input type="text" id="give_back_value" name="give_back_value[]"
																placeholder="<?php echo JText::_('COM_JGIVE_GIVEBACK_VALUE');?>" class="validate-numeric">
																<span class="add-on"><?php echo $this->currency_code;?></span>
															</div>
														</div>
													</div>
													<div class="control-group">
														<label class="control-label " for="give_back_details" title="<?php echo JText::_('COM_JGIVE_GIVE_BACK_DETAILS_TOOLTIP');?>" >
															<?php echo JText::_('COM_JGIVE_GIVE_BACK_DETAILS');?>
														</label>
														<div class="controls">
															<textarea rows="4" cols="50" id="give_back_details" name="give_back_details[]" placeholder="<?php echo JText::_('COM_JGIVE_GIVE_BACK_DETAILS');?>"></textarea>
														</div>
													</div>

													<!-- Added by Sneha -->
													<div class="control-group">
														<label class="control-label" for="give_back_quantity" title="<?php echo JText::_('COM_JGIVE_GIVE_BACK_QUANTITY_TOOLTIP');?>" >
															<?php echo JText::_('COM_JGIVE_GIVEBACK_QUANTITY');?>
														</label>
														<div class="controls">
															<input type="text" id="give_back_quantity" name="give_back_quantity[]" placeholder="<?php echo JText::_('COM_JGIVE_GIVEBACK_QUANTITY');?>" class="validate-numeric">
														</div>
													</div>

													<div class="control-group">
														<label class="control-label" for="coupon_image"><?php echo JText::_('COM_JGIVE_IMAGE');?></label>
														<div class="controls">
															<input type="file" id="coupon_image" name="coupon_image[]" placeholder="<?php echo JText::_('COM_JGIVE_IMAGE');?>" accept="image/*">
														</div>
													</div>
													<!--Added by Sneha ends-->


												</div>
												<div>&nbsp;</div>
											</div>
											<div class="com_jgive_add_button">
												<button class="btn btn-mini btn-primary " type="button" id='add'
												onclick="addClone('jgive_container','jgive_container');"
												title='<?php echo JText::_('COM_JGIVE_ADD_MORE_TOOLTIP');?>'>
													<i class="<?php echo $jgive_icon_plus; ?> icon-white"></i>
												</button>
											</div>

										</div><!--span12-->
									</div><!--row-fluid-->
								</fieldset>
							</div>
						</div>
					</div>


					<!--tab3fb-->
				<?php }?>


					<div id="tab4fb" class="tab-pane">
						<div class="row-fluid">
							<div class="span12">
								<fieldset>
									<legend><i class="fontello-icon-bag"></i> <span> <?php echo JText::_('COM_JGIVE_IMAGE_GALLERY');?></span></legend>
									<!--avatar -->
									<div class="control-group">
										<label class="control-label" for="camp_image"><?php echo JText::_('COM_JGIVE_IMAGE').' * ';?></label>
											<div class="controls">

												<?php
												//print_r($cdata['images']); gallery_image
												if(isset($cdata['images']) && count($cdata['images']))
												{
													?>
													<input type="file" id="camp_image" name="camp_image" placeholder="<?php echo JText::_('COM_JGIVE_IMAGE');?>" accept="image/*">

													<div class="text-warning">
														<?php echo JText::_('COM_JGIVE_EXISTING_IMAGE_MSG');?>
													</div>
													<div class="text-info">
														<?php echo JText::_('COM_JGIVE_EXISTING_IMAGE');?>
													</div>
													<div>
														<?php

														foreach($cdata['images'] as $img)
														{
															if($img->gallery_image==0)
															{
																if(JFile::exists(JPATH_SITE.DS.$img->path))
																{
																	echo "<input type='hidden' name='main_img_id' value=".$img->id.">";
																	echo "<img class='img-rounded com_jgive_img_128_128' src='".JUri::root().$img->path."' />";
																	break;//print only 1 image
																}
																else
																{
																	$path='images'.DS.'jGive'.DS;
																	//get original image name to find it resize images (S,M,L)
																	$org_file_after_removing_path=trim(str_replace($path,'S_',$img->path));
																	$img_link= JUri::root().$path.$org_file_after_removing_path;

																	echo "<input type='hidden' name='main_img_id' value=".$img->id.">";
																	echo "<img class='img-rounded com_jgive_img_128_128' src='".$img_link."' />";
																	break;//print only 1 image
																}
															}
														}
														?>
													</div>
													<?php
												}
												else//while editing image field is not required
												{
													?>
													<input type='hidden' name='main_img_id' value="">
													<input type="file" id="camp_image" name="camp_image" placeholder="<?php echo JText::_('COM_JGIVE_IMAGE');?>" class="required" accept="image/*">
													<?php
												}
												?>
											</div>
									</div>

									<div class="control-group">
										<label class="control-label" for="video_provider" title="<?php echo JText::_('COM_JGIVE_VIDEO_PROVIDER_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_VIDEO_PROVIDER');?>
										</label>
										<div class="controls">
											<?php
											/*$video_provider=array('youtube','liveleak','myspace','flickr','vimeo',
											'matacafe','blip.tv','dailymotion','break','viddler');*/

											$video_provider=array('youtube','vimeo');
											/*
											$video_provider_label=array('COM_JGIVE_YOUTUBE','COM_JGIVE_LIVELEAK','COM_JGIVE_MYSPACE',
											'COM_JGIVE_FLICKR','COM_JGIVE_VIMEO','COM_JGIVE_METACAFE','COM_JGIVE_BLIPTV',
											'COM_JGIVE_DAILYMOTION','COM_JGIVE_BREAK','COM_JGIVE_VIDDLER');*/

											$video_provider_label=array('COM_JGIVE_YOUTUBE','COM_JGIVE_VIMEO');
											 ?>
											<select name="video_provider" id="video_provider">
											<?php
												for($i=0;$i<count($video_provider);$i++)
												{
													if($video_provider[$i]==$cdata['images'][0]->video_provider)
														echo '<option value="'.$video_provider[$i].'" selected>'.JText::_($video_provider_label[$i]).'</option>';
													else
														echo '<option value="'.$video_provider[$i].'">'.JText::_($video_provider_label[$i]).'</option>';
												}
											?>
											</select>
										</div>
									</div>

									<div class="control-group">
										<label class="control-label" for="video_url_imput_id" title="<?php echo JText::_('COM_JGIVE_VIDEO_URL_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_VIDEO_URL');?>
										</label>
										<div class="controls">
											<input type="text" id="video_url_imput_id" name="video_url" class="" placeholder="<?php echo JText::_('COM_JGIVE_VIDEO_URL');?>"
											value="<?php if(isset($cdata['images'][0]->video_url)) echo $cdata['images'][0]->video_url;?>">
										</div>
									</div>

									<?php
										$video_img1=$video_img2='';
										//print_r($cdata['images']);die;
										if(isset($cdata['images'][0]->video_img))
										{
											if($cdata['images'][0]->video_img==0)
												$video_img1='checked';
											else
												$video_img2='checked';
										}else{
											$video_img1='checked';
										}
										?>
									<div class="control-group">
										<label class="control-label" title="<?php echo JText::_('COM_JGIVE_VIDEO_IMG_TOOLTIP');?>">
											<?php echo JText::_('COM_JGIVE_VIDEO_IMG');?>
										</label>
										<div class="controls">
											<label class="radio inline">
												<input type="radio" name="video_img" id="video_img1" value="0" <?php echo $video_img1;?> onchange="addRemoveRequired()">
													<?php echo JText::_('COM_JGIVE_IMG');?>
											</label>
											<label class="radio inline">
												<input type="radio" name="video_img" id="video_img2" value="1" <?php echo $video_img2;?> onchange="addRemoveRequired()">
													<?php echo JText::_('COM_JGIVE_VIDEO');?>
											</label>
										</div>
									</div>

									<?php if($params->get('img_gallery')){ ?>
									<div class="control-group imagediv" id="imagediv">
										<label class="control-label"><?php echo JHTML::tooltip(JText::_('COM_JGIVE_PROD_IMG_TOOLTIP'), JText::_('COM_JGIVE_PROD_IMG'), '',JText::_('COM_JGIVE_PROD_IMG'));?></label>
										<div class="controls">
											<?php
											if(!empty($cdata['images']))
											{ ?>
											<span class="alert alert-warning"><?php echo JText::_('COM_JGIVE_UNCHECK_TO_REMOVE_EXISTING_IMAGE');?></span>
											<div class="clearfix">
											<?php
												foreach($cdata['images'] as $img)
												{
													if($img->gallery_image)
													{
														?>
														<div class="pull-left jgive_images">
															<input type='checkbox' name='existing_imgs_ids[]' value='<?php echo $img->id;?>' checked>
															<?php
															echo "<img class='img-rounded com_jgive_img_128_128' src='".JUri::root().$img->path."' />";
															?>
														</div>
														<?php
													}
												}
												?>
											</div>
											<?php
											}
											?>
											<div class="row-fluid">
												<div class="span7">
													<?php
													/*@TODO JUGAD done for add more images display */
													if(version_compare(JVERSION, '3.0', 'lt')) { ?>
													<span class="filediv" id="filediv" >
														<input  type="file" name="jgive_img_gallery[]"  id="avatar" placeholder="" class=""  accept="image/*">
													</span>
													<?php }
													else{ ?>
														<span class="filediv" id="filediv" >
															<input  type="file" name="jgive_img_gallery[]"  id="avatar" placeholder="" class=""  accept="image/*">
														</span>
													<?php } ?>
												<!-- ADD MORE BTN-->
														<span class="addmore"  id="addmoreid"  id="addmoreid" >
															<button onclick="addmoreImg('filediv','filediv');" type="button" class="btn btn-mini btn-primary" title="<?php echo JText::_('COM_JGIVE_IMAGE_ADD_MORE');?>">
																<i class="<?php echo COM_JGIVE_ICON_PLUS;?> icon-white "></i>
															</button>
														</span>
												</div>
											</div> <!--END OF ROW FLUID -->
										</div> <!-- END OF CONTROL-->
									</div><!-- END OF control-group -->
									<?php } ?>
									<input type="hidden" name="option" value="com_jgive"/>
									<input type="hidden" name="controller" value="campaign"/>
									<input type="hidden" name="task" value="<?php echo $this->task;?>"/>

									<?php
									//print_r($cdata);
										if(isset($cdata['campaign']->id))
										{
											?>
											<input type="hidden" name="cid" value="<?php echo $cdata['campaign']->id;?>"/>
											<input type="hidden" name="img_id" value="<?php echo $cdata['images'][0]->id;?>"/>
											<?php
										}
									?>

									<?php
									if(!$this->send_payments_to_owner && $this->commission_fee>0)
									{
										?>
										<div class="alert alert-info">
											<em><i>
												<?php
													echo JText::sprintf('COM_JGIVE_COMMISSION_FEE_NOTICE',$this->commission_fee.'%');
												?>
											</i></em>
										</div>
										<?php
									}
									if($this->admin_approval)
									{
										?>
									<?php
									}
									?>

								</fieldset>
							</div>
						</div>
					</div>
					<!-- // tab4fb -->


				</div>
				<div class="section-content footer">
					<ul class="nav nav-pills pager">
						<li id="first-btn"><a href="javascript:void(0);" class="previous first btn" onclick="showTab('first')"><i class="icon-fast-backward"></i><?php echo JText::_('COM_JGIVE_FIRST_BTN'); ?></a></li>
						<li id="previous-btn"><a href="javascript:void(0);" class="previous btn" onclick="showTab('previous')"><i class="icon-step-backward"></i><?php echo JText::_('COM_JGIVE_PRE_BTN'); ?></a></li>

						<li id="last-btn" class="pull-right" ><a href="javascript:void(0);" class="last btn" onclick="showTab('last')"><?php echo JText::_('COM_JGIVE_LAST_BTN'); ?> <i class="icon-fast-forward"></i></a></li>
						<li id="next-btn" class="pull-right" ><a href="javascript:void(0);" class="next btn" onclick="showTab('next')"><?php echo JText::_('COM_JGIVE_NEXT_BTN'); ?> <i class="icon-step-forward"></i></a></li>

						<li id="resent-btn" class="pull-right" style="display: none;">
							<button class="btn btn-danger com_jgive_button " type="reset"><?php echo JText::_('COM_JGIVE_BUTTON_RESET_FORM'); ?></button>
						</li>
					</ul>
					<!-- // Action -->
				</div>
				<?php echo JHtml::_('form.token');?>
		   </form>
		</div>
	</div>
</div>
