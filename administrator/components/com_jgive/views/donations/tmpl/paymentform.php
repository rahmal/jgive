<?php
// no direct access

defined( '_JEXEC' ) or die( ';)' );
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
JHTML::_('behavior.formvalidation');
JHtml::_('behavior.framework',true);
JHtml::_('behavior.modal', 'a.modal');
$input=JFactory::getApplication()->input;
$pagetitle=JText::_('COM_JGIVE_DONATE_NOW');

$document=JFactory::getDocument();
$document->setTitle($pagetitle);
$baseurl=JRoute::_ (JUri::root().'index.php');
$params=JComponentHelper::getParams('com_jgive');
$user = JFactory::getuser();
$isguest=$user->id;


//get the data to idetify which field to show on donation view
$show_selected_fields_on_donate=$params->get('show_selected_fields_on_donation');
$donationfield=array();

$show_field=0;
$first_name=0;
$last_name=0;
$email=0;
$address=0;
$address2=0;
$hide_country=0;
$state=0;
$city=0;
$zip=0;
$phone_no=0;
$donation_type=0;
$donation_anonym=0;

if ($show_selected_fields_on_donate)
{
	$donationfield=$params->get('donationfield');

	if(isset($donationfield))
	foreach($donationfield as $tmp)
	{
		switch($tmp)
		{
			case 'first_name':
				$first_name=1;
			break;

			case 'last_name':
				$last_name=1;
			break;

			case 'email':
				$email=1;
			break;

			case 'address':
				$address=1;
			break;

			case 'address2':
				$address2=1;
			break;

			case 'country':
				$hide_country=1;
			break;

			case 'state':
				$state=1;
			break;

			case 'city':
				$city=1;
			break;

			case 'zip':
				$zip=1;
			break;

			case 'phone_no':
				$phone_no=1;
			break;

			case 'donation_type':
				$donation_type=1;
			break;

			case 'donation_anonym':
				$donation_anonym=1;
			break;
		}
	}
}
else
{
	$show_field=1;
}
/*End Added by SNeha*/



if($user->id)
	$registr_field_display="display:none";
else
	$registr_field_display="display:display";


if(empty($isguest))
$isguest=1;
else
$isguest=0;


$js = "
var isgst=".$isguest.";
var jgive_baseurl='".$baseurl."';

";

$document->addScriptDeclaration($js);

?>
<script type="text/javascript">

	techjoomla.jQuery(document).ready(function()
	{
		//techjoomla.jQuery(".checkout-content").hide();
		var userid=techjoomla.jQuery('#userid').val();

		techjoomla.jQuery('#payment-info-tab').hide();
		var DBuserbill="<?php echo (isset($this->userbill->state_code))?$this->userbill->state_code:''; ?>";
		generateState("country",DBuserbill) ;
	});

	function fillprofiledata(data)
	{
		if(data.first_name!='')
			techjoomla.jQuery('#first_name').val(data.first_name);
		if(data.last_name!='')
			techjoomla.jQuery('#last_name').val(data.last_name);
			if(data.paypal_email!='')
			techjoomla.jQuery('#paypal_email').val(data.paypal_email);
		if(data.address!='')
			techjoomla.jQuery('#address').val(data.address);
		if(data.address2!='')
			techjoomla.jQuery('#address2').val(data.address2);

		if(data.paypal_email!='')
			techjoomla.jQuery('#address2').val(data.paypal_email);

		if(data.address2!='')
			techjoomla.jQuery('#address2').val(data.address2);
		if(data.zip!='')
			techjoomla.jQuery('#zip').val(data.zip);

		if(data.phone!='')
			techjoomla.jQuery('#phone').val(data.phone);


	}

	/*
	To generate State list according to selected Country
	@param id of select list
	*/
	function generateState(countryId)
	{
		generateCity(countryId);
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
					generateoption(data,countryId);
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
	function generateoption(data,countryId)
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
			var op="<option value="  +region['region_code']+  ">"  +region['region']+   '</option>'     ;
			if(countryId=='country'){
				techjoomla.jQuery('#state').append(op);
			}
		}
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
	function chkmail(email){
		techjoomla.jQuery.ajax({
			url: '?option=com_jgive&controller=donations&task=chkmail&email='+email+'&tmpl=component&format=raw',
			type: 'GET',
			dataType: 'json',
			success: function(data)
			{

				if(data[0] == 1){
					techjoomla.jQuery('#email_reg').html(data[1]);
					techjoomla.jQuery("#button-billing-info").attr("disabled", "disabled");
				}
				else{
					techjoomla.jQuery('#email_reg').html('');
					techjoomla.jQuery("#button-billing-info").removeAttr("disabled");
				}
			}
		});
	}




function jGive_RecurDonation(radio_option)
{
	if(radio_option==0)
	{
		techjoomla.jQuery('#recurring_freq_div').hide();
		techjoomla.jQuery('#recurring_count_div').hide();
		techjoomla.jQuery('#recurring_count').removeClass('required');
	}
	else if(radio_option==1)
	{
		techjoomla.jQuery('#recurring_count').addClass('required');
		techjoomla.jQuery('#recurring_freq_div').show();
		techjoomla.jQuery('#recurring_count_div').show();
	}
}

function jGive_toggle_checkout(radio){
	if(parseInt(radio)==0)
	{
		techjoomla.jQuery('.jgive_select_user').show();
		techjoomla.jQuery('#donor_name').addClass('required');
		techjoomla.jQuery('#donor_id').addClass('required');

	}
	else if(parseInt(radio)==1){

		techjoomla.jQuery('.jgive_select_user').hide();
		techjoomla.jQuery('#donor_name').removeClass('required');
		techjoomla.jQuery('#donor_id').removeClass('required');
		techjoomla.jQuery('#donor_name').removeClass('invalid');
		techjoomla.jQuery('#donor_id').removeClass('invalid');
		techjoomla.jQuery('#donor_id').val();
		techjoomla.jQuery('#donor_name').val();





	}


}

function jSelectUser_jform_created_by(id, title) {

		var old_id = document.getElementById("donor_id").value;
		if (old_id != id) {
			document.getElementById("donor_id").value = id;
			document.getElementById("donor_name").value = title;

		}
		SqueezeBox.close();

			var compaignuserid=document.getElementById("donor_id").value;

		techjoomla.jQuery.ajax(
		{
			url:'index.php?option=com_jgive&task=loadprofiledata&controller=donations&compaignuserid='+compaignuserid+'&tmpl=component&format=raw',
			type:'GET',
			dataType:'json',
			success:function(data)
			{
				if (data === undefined || data == null || data.length <= 0)
				{

				}
				else
				{
						fillprofiledata(data)

				}

			}
		});

	}

	function SelectCompaign(id, title){
		var old_id = document.getElementById("user_id").value;
		if (old_id != id) {
			document.getElementById("user_id").value = id;
			document.getElementById("cid").value = id;
			document.getElementById("campaign_name").value = title;

		}
		SqueezeBox.close();

		//getGiveBack Against Selected Campaign
		getGiveBackAgainstCampaign(id);
	}

	Joomla.submitbutton = function(action){
	var form = document.adminForm;
	if(action=='donations.placeOrder')
	{

		var validateflag = document.formvalidator.isValid(document.id('adminForm'));

		var donation_amount=document.getElementById('donation_amount').value;
		donation_amount=parseFloat(donation_amount);

		var errorRes = validateGiveBackAmount(donation_amount);

		if(!errorRes)
		{
			return false;
		}

		if(validateflag)
		{
			Joomla.submitform(action );
		}//if validate flag
		else
		{
			alert("<?php echo JText::_('COM_JGIVE_VALIDATATION_ERROR'); ?>");
			return false;
		}

	}
	else
		Joomla.submitform(action );
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

//Populate selectd giveback amount in donation amount field



function populateGiveback()
{
	var giveBackid= techjoomla.jQuery('#givebacks').val();

	if(giveBackid=='edit_amount')
	{
		techjoomla.jQuery('#giveback_des').text("<?php echo JText::_('COM_JGIVE_GIVE_NOT_AVIL_FOR_THIS_AMOUNT'); ?>");
		//techjoomla.jQuery('#donation_amount').removeAttr('readOnly');
	}

	for(index = 0; index < givebackDetails.length; index++)
	{
		if(givebackDetails[index]['id']==giveBackid)
		{
			//techjoomla.jQuery('#donation_amount').attr('readOnly','readOnly');
			//update amount in donation amount field
			techjoomla.jQuery('#donation_amount').attr('value',givebackDetails[index]['amount']);
			techjoomla.jQuery('#giveback_des').text(givebackDetails[index]['description']);
			//update giveback description
		}
	}
}

function noGiveBack()
{
	var no_giveback=techjoomla.jQuery( "input:checkbox[name=no_giveback]:checked" ).val();

	if(no_giveback==undefined)
	{
		techjoomla.jQuery("#hide_giveback").show("slow");
	}
	else
	{
		techjoomla.jQuery("#hide_giveback").hide("slow");
		//techjoomla.jQuery('#donation_amount').removeAttr('readOnly');
	}

}

function validateGiveBackAmount(donation_amount)
{

	var no_giveback=techjoomla.jQuery( "input:checkbox[name=no_giveback]:checked" ).val();

	// Get the value from a dropdown select
	var givebackId = techjoomla.jQuery( "#givebacks").val();

	if(no_giveback==undefined)
	{
		for(index = 0; index < givebackDetails.length; index++)
		{
			if(givebackDetails[index]['id']==givebackId)
			{
				var givebackAmount = givebackDetails[index]['amount'];

				if(donation_amount>=givebackAmount)
				{
					return true;
				}
				else
				{
					alert("<?php echo JText::_('COM_JGIVE_AMOUNT_SHOULD_BE'); ?>"+givebackAmount);
					return false;
				}
			}
		}
	}
	return true;
}

var givebackDetails=" ";

function getGiveBackAgainstCampaign(cid)
{

techjoomla.jQuery(document).ready(function(){


	select=techjoomla.jQuery('#givebacks');
	select.find('option').remove().end();

	techjoomla.jQuery.ajax({
		url:'index.php?option=com_jgive&task=donations.getGiveBackAgainstCampaign&tmpl=component',
		type:'POST',
		dataType:'json',
		data:{
			cid:cid
		},
		success:function(response)
		{
			//Store response in global variable & this is used while changing giveback for desc
			givebackDetails= response;

			var desc_flag = 0;
			var desc_index = 0;
			for(i=0;i<response.length;i++)
			{
				if(response[i]['sold']==0)
				{
					var op = "<option value="+response[i]['id']+" >"+response[i]['amount']+"+</option>";
					techjoomla.jQuery('#givebacks').append(op);
					if(desc_flag == 0)
					{
						desc_flag = 1;
						desc_index = i;
					}
				}
			}

			if(desc_flag ==1 )
			{
				techjoomla.jQuery("#donation_amount").val(response[desc_index]['amount']);
				techjoomla.jQuery("#giveback_des").append(response[desc_index]['description']);
			}

		},
		error:function()
		{
				console.log('error');
		}
	});


	});

}

</script>




<div class="techjoomla-bootstrap jgive" id="jgive-checkout">

			<form action="" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data"
			class="form-horizontal form-validate form-validate" >


		<!-- Start OF billing_info_tab-->
		<div id="billing-info" class="jgive-checkout-steps">
			<div><h3><?php echo JText::_('COM_JGIVE_DONOR_DONATION_DETAILS');?></h3>
			</div>
			<div class="checkout-content  checkout-first-step-billing-info" id="billing-info-tab">
				<div  class="row-fluid form-horizontal">
					<div class="control-group" id="jgive_billmail_msg_div">
						<span class="help-inline jgive_removeBottomMargin" id="billmail_msg"></span>
					</div>


					<div class="control-group">
						<label class="control-label" for="campaign_name" title="<?php echo JText::_('COM_JGIVE_SELECT_CAMPAIGN_LABEL');?>">
							<?php echo JText::_('COM_JGIVE_SELECT_CAMPAIGN_LABEL');?>
						</label>
						<div class="controls">
							<?php
							?>
							<input type="text" id="campaign_name" name="campaign_name" class="required" disabled="disabled"
							placeholder="<?php echo JText::_('COM_JGIVE_SELECT_CAMPAIGN_TITLE');?>" value="">

							<input type="hidden" id="user_id" name="user_id" class="required"
							 value="">

								<a class="modal  button btn btn-info btn-small" rel="{handler: 'iframe', size: {x: 800, y: 500}}" href="index.php?option=com_jgive&amp;view=campaigns&amp;layout=all_list_select&amp;tmpl=component&amp;field=jform_created_by" title="<?php echo JText::_('COM_JGIVE_SELECT_CAMPAIGN_LABEL');?>" class="modal_jform_">
									<?php echo JText::_('COM_JGIVE_SELECT_CAMPAIGN_LABEL');?></a>


						</div>
					</div>
					<!--Donation type -->
						<div class="control-group">
							<label class="control-label"  title=""><?php echo JText::_('COM_JGIVE_CHECKOUT_OPTION');?></label>
							<div class="controls">
								<label class="radio inline">
									<input type="radio" name="checkout_type" id="checkout_register" value="0" checked="checked" onclick="jGive_toggle_checkout(0)" >
										<?php echo JText::_('COM_JGIVE_CHECKOUT_REGISTERED');?>
								</label>
								<?php
								if($params->get('guest_donation')){
								?>
								<label class="radio inline">
									<input type="radio" name="checkout_type" id="checkout_guest" value="1"  onclick="jGive_toggle_checkout(1)">
										<?php echo JText::_('COM_JGIVE_CHECKOUT_GUEST');?>
								</label>
								<?php
								}
								?>
							</div>
						</div>



					<div class="control-group jgive_select_user">
						<label class="control-label" for="compaign" title="<?php echo JText::_('COM_JGIVE_USER_LABEL');?>">
							<?php echo JText::_('COM_JGIVE_USER_LABEL');?>
						</label>
						<div class="controls">
							<?php


							?>
							<input type="text" id="donor_name" name="donor_name" class="required" disabled="disabled"
							placeholder="<?php echo JText::_('COM_JGIVE_USER_NAME');?>" value="<?php echo JFactory::getUser()->name;?>">

							<input type="hidden" id="donor_id" name="donor_id" class="required"
							 value="<?php echo JFactory::getUser()->id;?>">

								<a class="modal  button btn btn-info btn-small" rel="{handler: 'iframe', size: {x: 800, y: 500}}" href="index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=jform_created_by" title="Select User" class="modal_jform_created_by">
									<?php echo JText::_('COM_JGIVE_USER_LABEL');?></a>

						</div>
					</div>


					<?php if($show_field==1 OR $first_name==0 ): ?>
					<div class="control-group">
						<label class="control-label" for="first_name" title="<?php echo JText::_('COM_JGIVE_FIRST_NAME_TOOLTIP');?>">
							<?php echo JText::_('COM_JGIVE_FIRST_NAME');?>
						</label>
						<div class="controls">
							<input type="text" id="first_name" name="first_name" class="required"
							placeholder="<?php echo JText::_('COM_JGIVE_FIRST_NAME');?>" value="">
						</div>
					</div>
					<?php endif; ?>

					<?php if($show_field==1 OR $last_name==0 ): ?>

					<div class="control-group">
						<label class="control-label" for="last_name" title="<?php echo JText::_('COM_JGIVE_LAST_NAME_TOOLTIP');?>">
							<?php echo JText::_('COM_JGIVE_LAST_NAME');?>
						</label>
						<div class="controls">
							<input type="text" id="last_name" name="last_name" class="required"
							placeholder="<?php echo JText::_('COM_JGIVE_LAST_NAME');?>" value="">
						</div>
					</div>
					<?php endif; ?>

					<div class="control-group">
						<label class="control-label" for="paypal_email" title="<?php echo JText::_('COM_JGIVE_PAYPAL_EMAIL_TOOLTIP');?>">
							<?php echo JText::_('COM_JGIVE_EMAIL');?>
						</label>
						<div class="controls">
							<input type="text" id="paypal_email" <?php echo ( (!$user->id) ?  'onchange="chkmail(this.value);"':''); ?> name="paypal_email" class="required validate-email"
							placeholder="<?php echo JText::_('COM_JGIVE_EMAIL');?>" value="">
							<span class="help-inline" id="email_reg"></span>
						</div>
					</div>

					<?php if($show_field==1 OR $address==0 ): ?>

					<div class="control-group">
						<label class="control-label" for="address" title="<?php echo JText::_('COM_JGIVE_ADDRESS_TOOLTIP');?>">
							<?php echo JText::_('COM_JGIVE_ADDRESS');?>
						</label>
						<div class="controls">
							<input type="text" id="address" name="address" class="required"
							placeholder="<?php echo JText::_('COM_JGIVE_ADDRESS');?>" value="">
						</div>
					</div>
					<?php endif; ?>

					<?php if($show_field==1 OR $address2==0 ): ?>

					<div class="control-group">
						<label class="control-label" for="address2"	title="<?php echo JText::_('COM_JGIVE_ADDRESS2_TOOLTIP');?>">
							<?php echo JText::_('COM_JGIVE_ADDRESS2');?>
						</label>
						<div class="controls">
							<input type="text" id="address2" name="address2"
							placeholder="<?php echo JText::_('COM_JGIVE_ADDRESS2');?>" value="">
						</div>
					</div>
					<?php endif; ?>

					<?php if($show_field==1 OR $hide_country==0 ): ?>

					<div class="control-group">
						<label class="control-label" for="country" title="<?php echo JText::_('COM_JGIVE_COUNTRY_TOOLTIP');?>">
							<?php echo JText::_('COM_JGIVE_COUNTRY');?>
						</label>
						<div class="controls">
							<?php

							$countries=$this->countries;
							$default=NULL;
							$default=$params->get('default_country');
							$options=array();
							$options[]=JHtml::_('select.option',"",JText::_('COM_JGIVE_COUNTRY'));
							foreach($countries as $key=>$value)
							{
								$country=$countries[$key];
								$id=$country['country_id'];
								$value=$country['country'];
								$options[]=JHtml::_('select.option', $id, $value);
							}
							echo $this->dropdown=JHtml::_('select.genericlist',$options,'country','required="required" aria-invalid="false" size="1" onchange="generateState(id)"','value','text',$default,'country');
							?>
						</div>
					</div>
					<?php endif; ?>

					<?php if($show_field==1 OR ($hide_country==0 AND $state==0 )): ?>

					<div class="control-group">
						<label class="control-label" for="state" title="<?php echo JText::_('COM_JGIVE_STATE_TOOLTIP');?>">
							<?php echo JText::_('COM_JGIVE_STATE');?>
						</label>
						<div class="controls">
							<select name="state" id="state"></select>
						</div>
					</div>
					<?php endif; ?>

					<?php if($show_field==1 OR ($hide_country==0 AND $city==0 )): ?>
						<div class="control-group" id="hide_city">
							<label class="control-label" for="city" title="<?php echo JText::_('COM_JGIVE_CITY_TOOLTIP');?>">
								<?php echo JText::_('COM_JGIVE_CITY');?>
							</label>
							<div class="controls">
								<select name="city" id="city"></select>
							</div>
						</div>

						<div class="control-group">
							<label class="control-label" for="other_city_check" title="<?php echo JText::_('COM_JGIVE_OTHER_CITY_TOOLTIP');?>">
								<?php echo JText::_('COM_JGIVE_OTHER_CITY');?>
							</label>
							<div class="controls">
								<input type="checkbox" name="other_city_check" id="other_city_check" onchange="otherCity()"/>
								<?php echo JText::_('COM_JGIVE_CHECK_OTHER_CITY_MSG'); ?> <br/><br/>
								<input type="text" name="other_city" id="other_city" placeholder="<?php echo JText::_('COM_JGIVE_ENTER_OTHER_CITY');?>" value="" style="display:none;" >
							</div>
						</div>
					<?php endif; ?>

					<?php if($show_field==1 OR $zip==0 ): ?>

					<div class="control-group">
						<label class="control-label" for="zip" title="<?php echo JText::_('COM_JGIVE_ZIP_TOOLTIP');?>">
							<?php echo JText::_('COM_JGIVE_ZIP');?>
						</label>
						<div class="controls">
							<input type="text" id="zip" name="zip" class="required"
							placeholder="<?php echo JText::_('COM_JGIVE_ZIP');?>" value="">
						</div>
					</div>
					<?php endif; ?>

					<?php if($show_field==1 OR $phone_no==0 ): ?>

					<div class="control-group">
						<label class="control-label" for="phone" title="<?php echo JText::_('COM_JGIVE_PHONE_TOOLTIP');?>">
							<?php echo JText::_('COM_JGIVE_PHONE');?>
						</label>
						<div class="controls">
							<input type="text" id="phone" name="phone" class="required"
							placeholder="<?php echo JText::_('COM_JGIVE_PHONE');?>" value="">
						</div>
					</div>

					<label>
						<h4><?php
							echo JText::_('COM_JGIVE_DONATION_DETAILS');
						?></h4>
					</label>
					<?php endif; ?>

					<div class="control-group">
						<label class="control-label" for="donation_amount" title="<?php
							echo JText::_('COM_JGIVE_DONATION_AMOUNT_TOOLTIP') ;
						?>">
							<?php
							echo  JText::_('COM_JGIVE_DONATION_AMOUNT');
							?>
						</label>
						<div class="controls">
							 <div class="input-prepend input-append">
								<input type="text" id="donation_amount" name="donation_amount" class="required validate-numeric"
								placeholder="<?php	echo JText::_('COM_JGIVE_DONATION_AMOUNT');?>" >
								<span class="add-on"><?php echo $this->currency_code;?></span>
							</div>
						</div>
					</div>



					<div class="control-group">
						<label class="control-label" for="no_giveback" title="<?php echo JText::_('COM_JGIVE_NO_GIVEBACK');?>">
							<?php echo JText::_('COM_JGIVE_NO_GIVEBACK');?>
						</label>
						<div class="controls">
							<input type="checkbox" id="no_giveback" name="no_giveback" class="checkbox" value="1" onchange="noGiveBack()" >
							<span><?php  echo JText::_('COM_JGIVE_THANKS_MSG'); ?></span>
						</div>
					</div>

					<div id="hide_giveback" class="control-group">
						<label class="control-label" for="givebacks" title="<?php echo JText::_('COM_JGIVE_GIVEBACKS_TOOLTIP'); ?>">
							<?php echo JText::_('COM_JGIVE_GIVEBACK'); ?>
						</label>
						<div class="controls">
							<select name="givebacks" id="givebacks" onchange="populateGiveback()" >
							</select>

							<br/><br/>
							<div id="giveback_des" class="well">
							</div>
						</div>
					</div>


					<?php
					$recurring_donation=$params->get('recurring_donation');
					$recurring_donation=0;
					if($recurring_donation)
					{ ?>
						<?php if($show_field==1 OR $donation_type==0 ): ?>

						<!--Donation type -->
						<div class="control-group">
							<label class="control-label"  title="<?php
								echo  JText::_('COM_JGIVE_DONATION_TYPE_TOOLTIP') ;
							?>">
								<?php
									echo  JText::_('COM_JGIVE_DONATATION_TYPE') ;
								?>
							</label>
							<div class="controls">
								<label class="radio inline">
									<input type="radio" name="donation_type" id="donation_one_time" value="0" checked onclick="jGive_RecurDonation(0)">
										<?php echo JText::_('COM_JGIVE_ONE_TIME');?>
								</label>
								<label class="radio inline">
									<input type="radio" name="donation_type" id="donation_recurring" value="1" onclick="jGive_RecurDonation(1)" >
										<?php echo JText::_('COM_JGIVE_RECURRING');?>
								</label>
							</div>
						</div>
						<?php endif; ?>

						<!--recurring type -->
						<div id="recurring_freq_div" class="control-group jgive_display_none">
							<label class="control-label" for="recurring_freq" title="<?php
								echo  JText::_('COM_JGIVE_DONATE_RECR_TYPE_TOOLTIP');
							?>">
							<?php
								echo JText::_('COM_JGIVE_RECURRING_TYPE');
							?>
							</label>
							<div class="controls">
								<select name="recurring_freq" id="recurring_freq">
									<option value="DAY"><?php echo JText::_('COM_JGIVE_RECUR_DAILY'); ?></option>
									<option value="WEEK"><?php echo JText::_('COM_JGIVE_RECUR_WEEKLY'); ?></option>
									<option value="MONTH"><?php echo JText::_('COM_JGIVE_RECUR_MONTHLY'); ?></option>
									<option value="QUARTERLY"><?php echo JText::_('COM_JGIVE_RECUR_QUARTERLY'); ?></option>
									<option value="YEAR"><?php echo JText::_('COM_JGIVE_RECUR_ANNUALLY'); ?></option>
								</select>
							</div>
						</div>

						<!--recurring times -->
						<div id="recurring_count_div" class="control-group jgive_display_none">
							<label class="control-label" for="recurring_count" title="<?php
								echo  JText::_('COM_JGIVE_DONATION_RECUR_TIMES_TOOLTIP') ;
							?>">
								<?php
								echo JText::_('COM_JGIVE_RECUR_TIMES');
								?>
							</label>
							<div class="controls">
								 <div class="input-prepend input-append">
									<input type="text" id="recurring_count" name="recurring_count" class="validate-numeric"
									placeholder="<?php
									echo JText::_('COM_JGIVE_RECUR_TIMES');
									?>" >
								</div>
							</div>
						</div>
					<?php } ?>
					<!-- vat number -->
						<?php
					$params=JComponentHelper::getParams('com_jgive');
					if($params->get('vat_for_donor'))
					{
					 ?>
					<div class="control-group">
						<label class="control-label" for="vat_number" title="<?php
							echo JText::_('COM_JGIVE_VAT_NUMBER_TOOLTIP') ?>" >
							<?php
							echo JText::_('COM_JGIVE_VAT_NUMBER');
							?>
						</label>
						<div class="controls">
							 <div class="input-prepend input-append">
								<input type="text" id="vat_number" name="vat_number"
								placeholder="<?php
								echo JText::_('COM_JGIVE_VAT_NUMBER'); ?>" >
							</div>
						</div>
					</div>
						<?php
					} ?>

					<?php if($show_field==1 OR $donation_anonym==0 ): ?>

					<div class="control-group">
						<label class="control-label"  title="<?php
							echo JText::_('COM_JGIVE_DONATE_ANONYMOUSLY');
						?>">
							<?php
								echo JText::_('COM_JGIVE_DONATE_ANONYMOUSLY');
							?>
						</label>
						<div class="controls">
							<label class="radio inline">
								<input type="radio" name="publish" id="publish1" value="1" >
									<?php echo JText::_('COM_JGIVE_YES');?>
							</label>
							<label class="radio inline">
								<input type="radio" name="publish" id="publish2" value="0" checked>
									<?php echo JText::_('COM_JGIVE_NO');?>
							</label>
						</div>
					</div>
					<?php endif; ?>
					<?php

					if($params->get('terms_condition'))
					{
						/*$link='';
						if($params->get('payment_terms_article'))
							$link = JRoute::_(JUri::root()."index.php?option=com_content&view=article&id=".$params->get('payment_terms_article')."&tmpl=component" );
						if($link)
						{
						?>
						<div class="control-group">
							<div class="control-label">
								<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" href="<?php echo $link; ?>" class="modal">
									<?php echo JText::_( 'COM_JGIVE_ACCEPT_TERMS' ); ?>
								</a>
							</div>
							<div class="controls">
									&nbsp;&nbsp;<input  type="checkbox"  id="terms_condition" size="30"/>&nbsp;&nbsp;<?php echo JText::_( 'COM_JGIVE_YES' ); ?>
							</div>
						</div>
						<?php
						}*/
					}
					?>
					<div class="control-group">
						<label for="state" class="control-label"><?php echo JText::_('COM_JGIVE_PAY_METHODS')?></label>
						<div class="controls">
							<?php
							$select=array();
							$gateways = array_merge($select, $this->gateways);
							$gateways=array_filter($gateways);
							if(count($gateways)>=1) //if only one geteway then keep it as selected
							{
								$default=$gateways[0]->id; // id and value is same
							}
							if(empty($this->gateways))
								echo JText::_( 'COM_JGIVE_NO_PAYMENT_GATEWAY' );
							else
							{
								$pg_list = JHtml::_('select.radiolist', $gateways, 'gateways', 'class="inputbox required "', 'id', 'name',$default,false);
								echo $pg_list;
							}
								?>
						</div>
					</div>
					<div class="jtspacer">
								<input type="hidden" name="cid" id="cid" value="" />
								<input type="hidden" name="option" value="com_jgive" />
								<input type="hidden" name="view" value="donations" />
								<input type="hidden" id="controller" name="controller" value="donations" />
								<input type="hidden" name="Itemid" value="<?php echo $input->get('Itemid','','INT');?>" />
								<input type="hidden" name="order_id" id="order_id" value="0" />
								<input type="hidden" name="task" id="task" value="placeOrder" />
								<input type="hidden" name="userid" id="userid" value="<?php if($user->id) echo $user->id;else echo '0';?>">
								<!--<input  type="submit" class="btn btn-primary"  name="save" id="save" value="<?php
								 echo JText::_('COM_JGIVE_CONTINUE_CONFIRM_FREE');?>" >-->
							</div>
				</div>
			</div>
		</div>
		<!-- END OF billing_info_tab-->





		</div>
		<!--EOF Select Payment method  -->
	</form>
</div>
<!-- EOF of techjoomla bootrap -->



