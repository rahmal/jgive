<?php
// no direct access

defined( '_JEXEC' ) or die( ';)' );
jimport('joomla.filesystem.file');

jimport('joomla.filesystem.folder');
JHTML::_('behavior.formvalidation');
//JHtml::_('behavior.framework',true);
JHtml::_('behavior.modal', 'a.modal');
$input=JFactory::getApplication()->input;

$cdata=$this->cdata;
$campaign_title=$this->cdata['campaign']->title;
$pagetitle=JText::sprintf('COM_JGIVE_CHECKOUT_TITLE',$campaign_title);
$document=JFactory::getDocument();
$document->addScript(JUri::root().DS.'components'.DS.'com_jgive'.DS.'assets'.DS.'javascript'.DS.'jgive.js');
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

	function jGive_submitbutton(pressbutton) {
		var form = document.jGivePaymentForm;
		//console.log('i n before if *');
			if(pressbutton)
			{
					//console.log('i n pressbtn');
					//if(pressbutton == 'save')
					{
						techjoomla.jQuery('#payment-info-tab').hide();
						techjoomla.jQuery('#system-message').remove();
						if (document.formvalidator.isValid(document.id('jGivePaymentForm'))) {
							techjoomla.jQuery('#system-message').remove();
						}
						else {
							techjoomla.jQuery('#payment-info-tab').hide();
							var msg = 'Some values are not acceptable.  Please retry.';
							alert(msg);
							return false;
						}

						values=techjoomla.jQuery('#jGivePaymentForm').serialize();
						var order_id=techjoomla.jQuery('#order_id').val();
						techjoomla.jQuery.ajax({
							url: jgive_baseurl+'?option=com_jgive&controller=donations&task=placeOrder&tmpl=component&format=raw',
							type: 'POST',
							data:values,
							dataType: 'json',
							beforeSend: function() {
								//console.log('___ befor send *');
								techjoomla.jQuery('#confirm-order').after('<div class=\"com_jgive_ajax_loading\"><div class=\"com_jgive_ajax_loading_text\">".JText::_('COM_JGIVE_LOADING_PAYMET_FORM_MSG')."</div><img class=\"com_jgive_ajax_loading_img\" src=\"".JUri::base()."components/com_jgive/assets/images/ajax.gif\"></div>');
								// CODE TO HIDE EDIT LINK
								jgive_hideAllEditLinks();
							},
							complete: function() {

								techjoomla.jQuery('#jgive_order_details_tab').show()
								techjoomla.jQuery('.com_jgive_ajax_loading').remove();
								jgive_showAllEditLinks();

							},
							success: function(data)
							{

								if(data['success'] == 1){
									//console.log('___  success *');
									if(isgst==1)
									{
										techjoomla.jQuery('#user-info').html('');
									}
									techjoomla.jQuery('#payment-info-tab').hide();
									techjoomla.jQuery('#order_id').val(data['order_id']);
									techjoomla.jQuery('#payment-info .checkout-heading a').remove();
									addEditLink(techjoomla.jQuery('#payment-info'+' .checkout-heading'),'payment-info');

									techjoomla.jQuery('#payment_tab_table_html').html(data['payhtml']);
									techjoomla.jQuery('#order_summary_tab_table_html').html(data['orderHTML']);
									techjoomla.jQuery('#payment_tab').show();
									techjoomla.jQuery('#payment_tab_table').show();
									techjoomla.jQuery('#order_summary_tab').show();
									techjoomla.jQuery('#order_summary_tab_table').show();
									techjoomla.jQuery('#payment-info-tab').hide();
									techjoomla.jQuery('#payment_tab_table_html').html(data['gatewayhtml']);
									techjoomla.jQuery('#payment_tab').show();
									techjoomla.jQuery('#payment_tab_table').show();
									techjoomla.jQuery('#payment_tab_table_html').show();
								}
								else{
									//console.log('___  fail else  *');
									techjoomla.jQuery('#payment-info').hide();
									techjoomla.jQuery('#payment-info-tab').hide();
									techjoomla.jQuery('#payment_tab').hide();
									techjoomla.jQuery('#payment_tab_table').html();
									techjoomla.jQuery('#order_summary_tab_table_html').html();
									techjoomla.jQuery('#payment_tab_table').hide();
									techjoomla.jQuery('#order_summary_tab_table').hide();
								}
							}
						});
						return;
					}//pressbutton==save

			}//pressbutton
	return;
}

";

$document->addScriptDeclaration($js);

?>
<script type="text/javascript">

	//get Commission for identifying minimum donation amount
	var minimum_amount="<?php echo $cdata['campaign']->minimum_amount;?>";
	minimum_amount=parseInt(minimum_amount);
	var send_payments_to_owner="<?php echo$params->get('send_payments_to_owner');?>";
	var commission_fee="<?php echo$params->get('commission_fee');?>";
	var fixed_commissionfee="<?php echo$params->get('fixed_commissionfee');?>";

	function validatedatainsteps(thistepobj,nextstepid,stepno,currentstepname){
		if(parseInt(stepno)==2){
				GotonextStep(thistepobj,nextstepid,currentstepname);
		}
		if(parseInt(stepno)==3) //donation form
		{
			//check donation amount is valid
			var donation_amount=document.getElementById('donation_amount').value;
			donation_amount=parseFloat(donation_amount);

			if(donation_amount)
			{
				if(!send_payments_to_owner)
				{
					if(commission_fee)
					{
						total_commission_amount=((donation_amount*commission_fee)/100)+fixed_commissionfee;
					}
					else
					{
						total_commission_amount=fixed_commissionfee;
					}
				}
				else
				{
					total_commission_amount=0;
				}

				if(total_commission_amount<minimum_amount)
				{
					total_commission_amount=minimum_amount; //trikey don't bother it
				}

				if(total_commission_amount>donation_amount)
				{
					alert("<?php echo JText::_('COM_JGIVE_MINIMUM_DONATION_AMOUNT');?>"+total_commission_amount);
					return false;
				}

				var response = validateGiveBackAmount(donation_amount);

				if(!response)
				{
					return false;
				}
				//alert(minimum_amount)
			}
			else
			{
				alert("<?php echo JText::_('COM_JGIVE_ENTER_DONATION_AMOUNT');?>");
				return false;
			}

			//alert(donation_amount);
			//return false;
			//if()
			if(document.formvalidator.isValid(document.id('jGivePaymentForm')))
			{
				techjoomla.jQuery('#payment-info-tab').hide();
				techjoomla.jQuery('#system-message').remove();
			}
			else
			{
				techjoomla.jQuery('#payment-info-tab').hide();
				var msg = "<?php echo JText::_('COM_JGIVE_FORM_INVALID')?>";
				alert(msg);
				return false;
			}
			<?php if($params->get('terms_condition') && ($params->get('payment_terms_article')!=0)){ ?>
				if(document.jGivePaymentForm.terms_condition.checked==false){
					var check='<?php echo JText::_('COM_JGIVE_CHECK_TERMS');?>';
					alert(check);
					return false;
				}
			<?php } ?>
			GotonextStep(thistepobj,nextstepid,currentstepname);
		}
	}

	function GotonextStep(thistepobj,nextstepid,currentstepname){
		techjoomla.jQuery('.checkout-content').hide();
		techjoomla.jQuery('#'+currentstepname.toString()+' .checkout-heading a').remove();

		var order_id=techjoomla.jQuery('#order_id').val();
		var finalamt=techjoomla.jQuery('#net_amt_pay_inputbox').val();
		if(parseFloat(finalamt)<=0)
			techjoomla.jQuery('#payment_info-tab-method').hide();
		else
			techjoomla.jQuery('#payment_info-tab-method').show();

		if(parseInt(order_id)>=1)
		{
			jGive_submitbutton(thistepobj.id)
			addEditLink(techjoomla.jQuery('#'+currentstepname.toString()+' .checkout-heading'),currentstepname.toString());
			return;
		}

		techjoomla.jQuery('#'+nextstepid).slideDown('slow');
		//MOVE CURSOR TO CURRENT STEP
		var parentid=techjoomla.jQuery('#'+nextstepid).parent().attr('id');
		goToByScroll(parentid);
		addEditLink(techjoomla.jQuery('#'+currentstepname.toString()+' .checkout-heading'),currentstepname.toString());
		jgive_showAllEditLinks()
	}

	function jgive_hideAllEditLinks()
	{
		techjoomla.jQuery(".jgive_editTab").hide();
	}
	function jgive_showAllEditLinks()
	{
		techjoomla.jQuery(".jgive_editTab").show();
	}
	// This is a functions that scrolls to #{blah}link
	function goToByScroll(id){
		 techjoomla.jQuery('html,body').animate({
		 scrollTop: techjoomla.jQuery("#"+id).offset().top},'slow');
	}

	function addEditLink(selectorObj,currentstepname)
	{
		techjoomla.jQuery('#'+currentstepname.toString()+' .checkout-heading .badge').remove();
		techjoomla.jQuery(selectorObj).append('<span class="badge badge-success pull-right" id="jgive_success_icon"><i class="icon-publish"></i></span><a class="jgive_editTab" onclick="jgive_hideshowTabs(this)"><?php echo JText::_('COM_JGIVE_EDIT'); ?></a>');
	}

	techjoomla.jQuery(document).ready(function()
	{
		//techjoomla.jQuery(".checkout-content").hide();
		var userid=techjoomla.jQuery('#userid').val();
		if(parseInt(userid)==0)
		{
			techjoomla.jQuery(".checkout-first-step-billing-info").hide();
			techjoomla.jQuery(".checkout-first-step-user-info").show();
		}
		else
		{
			techjoomla.jQuery(".checkout-first-step-billing-info").show();
		}
		techjoomla.jQuery('#payment-info-tab').hide();
		var DBuserbill="<?php echo (isset($this->userbill->state_code))?$this->userbill->state_code:''; ?>";
		generateState("country",DBuserbill) ;
		populateGiveback();

		//payment gateways html
		jGive_RecurDonation(0);

	});
	/*
	To generate State list according to selected Country
	@param id of select list
	*/
	function generateState(countryId)
	{
		generateCity(countryId);
		var country=jQuery('#'+countryId).val();
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


function jgive_hideshowTabs(obj)
{
	jgive_hideAllEditLinks()
	techjoomla.jQuery('.checkout-content').slideUp('slow');
	techjoomla.jQuery(obj).hide();
	techjoomla.jQuery('#payment_tab_table_html').html();
	techjoomla.jQuery(obj).parent().parent().find('.checkout-content').slideDown('slow');
}

function jgive_toggleOrder(selecteddiv){
	techjoomla.jQuery('#'+selecteddiv).toggle();
}

function jgive_login()
{
	techjoomla.jQuery.ajax({
		   url: jgive_baseurl+'?option=com_jgive&controller=donations&task=login_validate',
		   type: 'post',
		   data: techjoomla.jQuery('#user-info-tab #login :input'),
		   dataType: 'json',
		   beforeSend: function() {
				   techjoomla.jQuery('#button-login').attr('disabled', true);
				   techjoomla.jQuery('#button-login').after('<span class="wait">&nbsp;Loading..</span>');
		   },
		   complete: function() {
				   techjoomla.jQuery('#button-login').attr('disabled', false);
				   techjoomla.jQuery('.wait').remove();
		   },
		   success: function(json) {
				   techjoomla.jQuery('.warning, .j2error').remove();

				   if (json['error']) {
						   techjoomla.jQuery('#login').prepend('<div class="warning alert alert-danger" >' + json['error']['warning'] + '<button data-dismiss="alert" class="close" type="button">×</button></div>');
						   techjoomla.jQuery('.warning').fadeIn('slow');
					}
				   else if (json['redirect']) {
						   location = json['redirect'];
					}
		   },
		   error: function(xhr, ajaxOptions, thrownError) {
		   }
	});
}

function jGive_RecurDonation(radio_option)
{
	if(radio_option==0)
	{
		var pg_list = <?php
			$select=array();
			$gateways = array_merge($select, $this->gateways);
			$gateways=array_filter($gateways);
			if(count($gateways)>=1) //if only one geteway then keep it as selected
			{
			$default=$gateways[0]->id; // id and value is same
			}
			if(empty($this->gateways))
				echo 0;
			else
			{
				$pg_list = JHtml::_('select.radiolist', $gateways, 'gateways', 'class="inputbox required "', 'id', 'name',$default,false);
				//$pg_list=str_replace("</div>","",$pg_list);
				//$pg_list = trim(preg_replace('/\s\s+/', ' ', $pg_list));
				$html = array();
				$html[0] = $pg_list;
				echo json_encode($html);
			}

		?>;

		if(pg_list[0]==0)
		{
			pg_list[0]="<?php echo JText::_( 'COM_JGIVE_NO_PAYMENT_GATEWAY' ); ?>"

		}

		techjoomla.jQuery( "div#gatewaysContent" ).html(pg_list[0]);
		techjoomla.jQuery('#recurring_freq_div').hide();
		techjoomla.jQuery('#recurring_count_div').hide();
		techjoomla.jQuery('#recurring_count').removeClass('required');
	}
	else if(radio_option==1)
	{
		var pg_list = <?php
			$select=array();
			$gateways = array_merge($select, $this->recurringGateways);
			$gateways=array_filter($gateways);
			if(count($gateways)>=1) //if only one geteway then keep it as selected
			{
			$default=$gateways[0]->id; // id and value is same
			}
			if(empty($this->recurringGateways))
				echo 0;
			else
			{
				$pg_list = JHtml::_('select.radiolist', $gateways, 'gateways', 'class="inputbox required "', 'id', 'name',$default,false);
				$pg_list=str_replace("</div>","",$pg_list);
				$pg_list = trim(preg_replace('/\s\s+/', ' ', $pg_list));
				$html = array();
				$html[0] = $pg_list;
				echo json_encode($html);
			}
		?>;

		if(pg_list[0]==0)
		{
			pg_list[0]="<?php echo JText::_( 'COM_JGIVE_NO_PAYMENT_GATEWAY' ); ?>"

		}
		techjoomla.jQuery( "div#gatewaysContent" ).html(pg_list[0]);
		techjoomla.jQuery('#recurring_count').addClass('required');
		techjoomla.jQuery('#recurring_freq_div').show();
		techjoomla.jQuery('#recurring_count_div').show();
	}
}
function otherCity()
{
	if(document.jGivePaymentForm.other_city_check.checked===true)
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

var givebackDetails=<?php
				if(!empty($this->campaignGivebacks))
				{
					echo json_encode($this->campaignGivebacks,1);
				}
				else
				{
					echo 0;
				}?>;


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
</script>


<?php
if(isset($this->jsheader))
{
	echo $this->jsheader;
}
?>
<?php
if(isset($this->jsfooter))
{
	echo $this->jsfooter;
}
//jomsocial toolbar
echo $this->jomsocailToolbarHtml;
?>

<div class="techjoomla-bootstrap jgive" id="jgive-checkout">
	<span><h3><?php echo JText::sprintf('COM_JGIVE_CHECKOUT_TITLE',$campaign_title);?></h3></span>
		<form action="" method="post" name="jGivePaymentForm" id="jGivePaymentForm"	class="form-validate">
		<!--Start User Details Tab-->
		<?php
			$user=JFactory::getUser();
			if(!$user->id){ ?>
			<div id="user-info" class="jgive-checkout-steps ">
				<div class="checkout-heading"><span><?php echo JText::_('COM_JGIVE_USER_INFO');?></span>
				</div>
				<div class="checkout-content row-fluid checkout-first-step-user-info" id="user-info-tab">
					<div class="left span5">
						<h2><?php echo JText::_('COM_JGIVE_CHECKOUT_NEW_DONOR'); ?></h2>
						<p><?php echo JText::_('COM_JGIVE_CHECKOUT_OPTIONS'); ?></p>
						<!-- registration -->
						<?php if($this->guest_donation == '1'): ?>
							<label for="register">
								<input type="radio" name="account" value="register" id="register" checked="checked" />
								<b><?php echo JText::_('COM_JGIVE_CHECKOUT_REGISTER'); ?></b>
							</label>
							<br />
						<?php endif; ?>

						<!-- guest -->
						<?php if($this->guest_donation == '1'): ?>
							<label for="guest">
								<input type="radio" name="account" value="guest" id="guest"  />
								<b><?php echo JText::_('COM_JGIVE_CHECKOUT_GUEST'); ?></b>
							</label>
							<br />
						<?php endif; ?>
						<br />

						<?php if($this->guest_donation == '1'): ?>
							<p><?php echo JText::_('COM_JGIVE_CHECKOUT_REGISTER_ACCOUNT_HELP_TEXT'); ?></p>
						<?php endif; ?>
						<input type="button" class="button btn btn-primary" id="button-user-info" value="<?php echo JText::_('COM_JGIVE_CONTINUE');?>" onclick="validatedatainsteps(this,'<?php echo "billing-info-tab" ?>',2,'user-info')">
						<br/>

					</div>
					<div id="login" class="right span5">
						<h2><?php echo JText::_('COM_JGIVE_CHECKOUT_RETURNING_DONOR'); ?></h2>
						<p><?php echo JText::_('COM_JGIVE_CHECKOUT_RETURNING_DONOR_WELCOME'); ?></p>
						<b><?php echo JText::_('COM_JGIVE_CHECKOUT_USERNAME'); ?></b><br />
						<input type="text" name="email" value="" />
						<br />
						<br />
						<b><?php echo JText::_('COM_JGIVE_CHECKOUT_PASSWORD'); ?></b><br />
						<input type="password" name="password" value="" />
						<br />
						<input type="button" value="<?php echo JText::_('COM_JGIVE_CHECKOUT_LOGIN'); ?>" id="button-login" onclick="jgive_login()" class="button btn btn-primary" /><br />
						<br />
					</div>
					<div class="span5 pull-left">
					</div>
				</div>
			</div>
			<?php
			}?>
		<!--End User Details Tab-->


		<!-- Start OF billing_info_tab-->
		<div id="billing-info" class="jgive-checkout-steps">
			<div class="checkout-heading"><?php echo JText::_('COM_JGIVE_DONOR_DONATION_DETAILS');?>
			</div>
			<div class="checkout-content  checkout-first-step-billing-info" id="billing-info-tab">
				<div  class="row-fluid form-horizontal">
					<div class="control-group" id="jgive_billmail_msg_div">
						<span class="help-inline jgive_removeBottomMargin" id="billmail_msg"></span>
					</div>
					<label><h4><?php
						echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONOR_DETAILS') : JText::_('COM_JGIVE_INVESTOR_DETAILS'));
					?></h4></label>
					<!--Added by Sneha-->
					<?php if($show_field==1 OR $first_name==0 ): ?>
					<div class="control-group">
							<label class="control-label" for="first_name" title="<?php echo JText::_('COM_JGIVE_FIRST_NAME_TOOLTIP');?>">
								<?php echo JText::_('COM_JGIVE_FIRST_NAME');?>
							</label>
							<div class="controls">
								<input type="text" id="first_name" name="first_name" class="validate-name required"
								placeholder="<?php echo JText::_('COM_JGIVE_FIRST_NAME_PH');?>" value="<?php echo $this->session->get('JGIVE_first_name');?>">
							</div>
					</div>
					<?php endif; ?>
					<!--Added by Sneha-->
					<?php if($show_field==1 OR $last_name==0 ): ?>
						<div class="control-group">
							<label class="control-label" for="last_name" title="<?php echo JText::_('COM_JGIVE_LAST_NAME_TOOLTIP');?>">
								<?php echo JText::_('COM_JGIVE_LAST_NAME');?>
							</label>
							<div class="controls">
								<input type="text" id="last_name" name="last_name" class="required"
								placeholder="<?php echo JText::_('COM_JGIVE_LAST_NAME_PH');?>" value="<?php echo $this->session->get('JGIVE_last_name');?>">
							</div>
						</div>
					<?php endif; ?>

					<div class="control-group">
						<label class="control-label" for="paypal_email" title="<?php echo JText::_('COM_JGIVE_PAYPAL_EMAIL_TOOLTIP');?>">
							<?php echo JText::_('COM_JGIVE_EMAIL');?>
						</label>
						<div class="controls">
							<input type="text" id="paypal_email" <?php echo ( (!$user->id) ?  'onchange="chkmail(this.value);"':''); ?> name="paypal_email" class="required validate-email"
							placeholder="<?php echo JText::_('COM_JGIVE_EMAIL_PH');?>" value="<?php echo $this->session->get('JGIVE_paypal_email');?>">
							<span class="help-inline" id="email_reg"></span>
						</div>
					</div>

					<!--Added by Sneha-->
					<?php if($show_field==1 OR $address==0 ): ?>
						<div class="control-group">
							<label class="control-label" for="address" title="<?php echo JText::_('COM_JGIVE_ADDRESS_TOOLTIP');?>">
								<?php echo JText::_('COM_JGIVE_ADDRESS');?>
							</label>
							<div class="controls">
								<input type="text" id="address" name="address" class="required"
								placeholder="<?php echo JText::_('COM_JGIVE_ADDRESS_PH');?>" value="<?php echo $this->session->get('JGIVE_address');?>">
							</div>
						</div>
					<?php endif; ?>

					<!--Added by Sneha-->
					<?php if($show_field==1 OR $address2==0 ): ?>
						<div class="control-group">
							<label class="control-label" for="address2"	title="<?php echo JText::_('COM_JGIVE_ADDRESS2_TOOLTIP');?>">
								<?php echo JText::_('COM_JGIVE_ADDRESS2');?>
							</label>
							<div class="controls">
								<input type="text" id="address2" name="address2"
								placeholder="<?php echo JText::_('COM_JGIVE_ADDRESS2');?>" value="<?php echo $this->session->get('JGIVE_address2');?>">
							</div>
						</div>
					<?php endif; ?>

					<!--Added by Sneha-->
					<?php if($show_field==1 OR $hide_country==0 ): ?>
						<div class="control-group">
							<label class="control-label" for="country" title="<?php echo JText::_('COM_JGIVE_COUNTRY_TOOLTIP');?>">
								<?php echo JText::_('COM_JGIVE_COUNTRY');?>
							</label>
							<div class="controls">
								<?php
								$sc=$this->session->get('JGIVE_country');//@TODO use directly below
								$countries=$this->countries;
								$default=NULL;
								if(isset($sc)){
									$default=$this->session->get('JGIVE_country');
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
								echo $this->dropdown=JHtml::_('select.genericlist',$options,'country','required="required" aria-invalid="false" size="1" onchange="generateState(id)"','value','text',$default,'country');
								?>
							</div>
						</div>
					<?php endif; ?>

					<!--Added by Sneha-->
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

					<!--Added by Sneha-->
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

					<!--Added by Sneha-->
					<?php if($show_field==1 OR $zip==0 ): ?>
						<div class="control-group">
							<label class="control-label" for="zip" title="<?php echo JText::_('COM_JGIVE_ZIP_TOOLTIP');?>">
								<?php echo JText::_('COM_JGIVE_ZIP');?>
							</label>
							<div class="controls">
								<input type="text" id="zip" name="zip" class="required"
								placeholder="<?php echo JText::_('COM_JGIVE_ZIP_PH');?>" value="<?php echo $this->session->get('JGIVE_zip');?>">
							</div>
						</div>
					<?php endif; ?>

					<!--Added by Sneha-->
					<?php if($show_field==1 OR $phone_no==0 ): ?>
						<div class="control-group">
							<label class="control-label" for="phone" title="<?php echo JText::_('COM_JGIVE_PHONE_TOOLTIP');?>">
								<?php echo JText::_('COM_JGIVE_PHONE');?>
							</label>
							<div class="controls">
								<input type="text" id="phone" name="phone" class="required"
								placeholder="<?php echo JText::_('COM_JGIVE_PHONE_PH');?>" value="<?php echo $this->session->get('JGIVE_phone');?>">
							</div>
						</div>
					<?php endif; ?>

					<label>
						<h4><?php
							echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATION_DETAILS') : JText::_('COM_JGIVE_INVESTMENT_DETAILS'));
						?></h4>
					</label>

					<div class="control-group">
						<label class="control-label" for="donation_amount" title="<?php
							echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATION_AMOUNT_TOOLTIP') : JText::_('COM_JGIVE_INVESTMENT_AMOUNT_TOOLTIP'));
						?>">
							<?php
							echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATION_AMOUNT') : JText::_('COM_JGIVE_INVESTMENT_AMOUNT'));
							?>
						</label>
						<div class="controls">
							 <div class="input-prepend input-append">

								<?php
								if (!empty($this->giveback_id))
								{
									$predefineAmount='';
									foreach($this->campaignGivebacks as $giveback)
									{
										if($this->giveback_id == $giveback->id)
										{
											$predefineAmount=$giveback->amount;
										}
									}
									//print_r( (array)$this->campaignGivebacks);die;
									 ?>
									<input type="text" id="donation_amount" name="donation_amount" class="required validate-numeric"
									onblur='validateAmount(id,"<?php echo JText::_('COM_JGIVE_INVALID_DONATION_AMOUNT'); ?>")'
									placeholder="<?php
									echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATION_AMOUNT_PH') : JText::_('COM_JGIVE_INVESTMENT_AMOUNT_PH'));
									?>"  value="<?php echo $predefineAmount; ?>" >
								<?php
								}
								else
								{ ?>
									<input type="text" id="donation_amount" name="donation_amount" class="required validate-numeric"
									onblur='validateAmount(id,"<?php echo JText::_('COM_JGIVE_INVALID_DONATION_AMOUNT'); ?>")'
									placeholder="<?php
									echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATION_AMOUNT_PH') : JText::_('COM_JGIVE_INVESTMENT_AMOUNT_PH'));
									?>" >

								<?php
								} ?>
								<span class="add-on"><?php echo $this->currency_code;?></span>
							</div>
						</div>
					</div>


					<?php

					if (count($this->campaignGivebacks))
					{

						$givebackDescription='';

						?>
						<!--<option value="edit_amount"><?php echo JText::_('COM_JGIVE_SELECT_GIVBACK_AMOUNT'); ?></option> -->
						<?php
						$firstItem=1;
						$end_select=0;
						foreach($this->campaignGivebacks as $giveback)
						{
							//Only unsold giveback available to buy
							if($giveback->sold == 0 )
							{

								if($firstItem==1)
								{
									$firstItem++;
									 ?>
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
								<?php
									$end_select=1;
								}
								if($this->giveback_id == $giveback->id)
								{
									$givebackDescription=$giveback->description;
								 ?>
									<option value="<?php echo $giveback->id; ?>"  selected="selected" ><?php echo $giveback->amount; ?>+ </option>
								<?php
								}
								else
								{ ?>
									<option value="<?php echo $giveback->id; ?>"><?php echo $giveback->amount; ?>+</option>
								<?php
								}

							}
						}

						if($end_select==1)
						{ ?>
								</select>
								<br/><br/>
								<div id="giveback_des" class="well">
									<?php echo $givebackDescription; ?>
								</div>
							</div>
						</div><?php
						}
					} ?>



					<?php if($params->get('recurring_donation'))
					{ ?>
						<!--Added by Sneha-->
						<?php if($show_field==1 OR $donation_type==0 ): ?>
						<!--Donation type -->
						<div class="control-group">
							<label class="control-label"  title="<?php
								echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATION_TYPE_TOOLTIP') : JText::_('COM_JGIVE_INVESTMENT_TYPE_TOOLTIP'));
							?>">
								<?php
									echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATATION_TYPE') : JText::_('COM_JGIVE_INVEST_ANONYMOUSLY'));
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
								echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATE_RECR_TYPE_TOOLTIP') : JText::_('COM_JGIVE_INVEST_RECR_TYPE_TOOLTIP'));
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
								echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATION_RECUR_TIMES_TOOLTIP') : JText::_('COM_JGIVE_INVESTMENT_RECUR_TIMES_TOOLTIP'));
							?>">
								<?php
								echo JText::_('COM_JGIVE_RECUR_TIMES');
								?>
							</label>
							<div class="controls">
								 <div class="input-prepend input-append">
									<input type="text" id="recurring_count" name="recurring_count"
									onblur='validateAmount(id,"<?php echo JText::_('COM_JGIVE_INVALID_RECURRING_TIMES'); ?>")'
									class="validate-numeric"
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
					<!--Added by Sneha-->
					<?php if($show_field==1 OR $donation_anonym==0 ): ?>
					<div class="control-group">
						<label class="control-label"  title="<?php
							echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATE_ANONYMOUSLY_TOOLTIP') : JText::_('COM_JGIVE_INVEST_ANONYMOUSLY_TOOLTIP'));
						?>">
							<?php
								echo (($cdata['campaign']->type=='donation') ? JText::_('COM_JGIVE_DONATE_ANONYMOUSLY') : JText::_('COM_JGIVE_INVEST_ANONYMOUSLY'));
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
						$link='';
						if($params->get('payment_terms_article'))
							$link = JRoute::_(JUri::root()."index.php?option=com_content&view=article&id=".$params->get('payment_terms_article')."&tmpl=component" );
						if($link)
						{
						?>
						<div class="control-group">
							<div class="control-label jgive_terms_cond">
								<a rel="{handler: 'iframe', size: {x: 600, y: 600}}" href="<?php echo $link; ?>" class="modal jgive-override-modal">
									<?php echo JText::_( 'COM_JGIVE_ACCEPT_TERMS' ); ?>
								</a>
							</div>
							<div class="controls">
									&nbsp;&nbsp;<input  type="checkbox"  id="terms_condition" size="30"/>&nbsp;&nbsp;<?php echo JText::_( 'COM_JGIVE_YES' ); ?>
							</div>
						</div>
						<?php
						}
					}
					?>
					<div class="control-group">
						<div class="controls">
							<input type="button" class="button btn btn-primary" id="button-billing-info" value="<?php echo JText::_('COM_JGIVE_CONTINUE');?>" onclick="validatedatainsteps(this,'<?php echo "payment-info-tab" ?>',3,'billing-info')">
						</div>
					</div>	<!-- END OF row-fluid-->
				</div>
			</div>
		</div>
		<!-- END OF billing_info_tab-->


		<!--Start of Select Payment method  -->
		<div id="payment-info" class="jgive-checkout-steps form-horizontal">

			<div class="checkout-heading">
				<?php
					echo JText::_('COM_JGIVE_PAY_METHODS_TAB');
				?>
			</div>

			<div class="checkout-content row-fluid " id="payment-info-tab">
				<div class="well" id="payment_info-tab-method">
					<div class="control-group">
						<label for="state" class="control-label"><?php echo JText::_('COM_JGIVE_PAY_METHODS')?></label>
						<div id="gatewaysContent" class="controls span1">
							<?php
							/*$select=array();
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
							}*/
								?>
						</div>
					</div>
				</div>
				<!-- EOF payment_info-tab-method-->
				<!-- start control-group-->
				<div class="control-group" align="center">
					<div class="controls">
						<div class="btn-group pull-left">
							<div class="jtspacer">
								<input type="hidden" name="cid" value="<?php echo $this->cid;?>" />
								<input type="hidden" name="option" value="com_jgive" />
								<input type="hidden" name="view" value="donations" />
								<input type="hidden" name="controller" value="donations" />
								<input type="hidden" name="Itemid" value="<?php echo $input->get('Itemid','','INT');?>" />
								<input type="hidden" name="order_id" id="order_id" value="0" />
								<input type="hidden" name="task" value="placeOrder" />
								<input type="hidden" name="userid" id="userid" value="<?php if($user->id) echo $user->id;else echo '0';?>">
								<input  type="button" class="btn btn-primary"  name="save" id="save" value="<?php
								 echo JText::_('COM_JGIVE_CONTINUE_CONFIRM_FREE');?>" onclick="jGive_submitbutton('save');">
							</div>
						</div>
					</div>
				</div>
				<!-- EOF control-group-->
			</div>
		</div>
		<!--EOF Select Payment method  -->
	</form>



	<!-- start confirm order -->
	<div id="confirm-order" class="jgive-checkout-steps">
	  <div class="checkout-heading"><?php echo JText::_('COM_JGIVE_PAYMENT_INFO');?>
		<span class="pull-right" id="jgive_order_details_tab"><a href="javascript:void('0');" onclick="jgive_toggleOrder('order_summary_tab_table')"><?php //echo JText::_('COM_JGIVE_ORDER_INFO_HIDE');?></a></span></div>
		<div class="checkout-content row-fluid"  id="payment_tab">
			<div   id="order_summary_tab_table"  class="table table-striped- table-hover">
				<div  id="order_summary_tab_table_html" >
				</div>
			</div>

			<!--start of payment tab-->
			<div   id="payment_tab_table">
				<div class="checkout-heading">	<?php echo JText::_('COM_JGIVE_PAY_FORM'); ?>
				</div>
				<div  id="payment_tab_table_html" class="well">
				</div>
			</div>
			<!--end of payment tab-->
		</div>
	</div>
	<!-- EOF of confirm order -->
</div>
<!-- EOF of techjoomla bootrap -->


