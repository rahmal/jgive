function validateForm()
{
	/*var daterangefrom=techjoomla.jQuery('#start_date').val();
	var daterangeto=techjoomla.jQuery('#end_date').val();
	if(daterangefrom=='' && daterangeto==''){
		return true;
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

	today = year+'-'+newmonth+'-'+newdate;
	/*
	if(((daterangefrom) < (today)) || ((daterangeto) < (today)))
	{
		alert('start date / end date can not be in past');
		return false;
	}
	**/

	/*if((daterangefrom) > (daterangeto))
	{
			alert('Start Date should not be greater than End Date');
			return false;
	}*/
}


function validateAmount(fieldId, fieldMsg)
{

	switch(fieldId)
	{
		case 'max_donors':
		case 'minimum_amount':
			if( (techjoomla.jQuery('#'+fieldId).val() !=='') && (! parseInt(techjoomla.jQuery('#'+fieldId).val(),10) > 1 ) )
			{
				alert(fieldMsg);
				techjoomla.jQuery('#'+fieldId).val('');
				techjoomla.jQuery('#'+fieldId).focus();
				return false;
			}

			if( (techjoomla.jQuery('#'+fieldId).val() !=='') && (techjoomla.jQuery('#'+fieldId).val() < 0) )
			{
				alert(fieldMsg);
				techjoomla.jQuery('#'+fieldId).val('');
				techjoomla.jQuery('#'+fieldId).focus();
				return false;
			}
		break;

		default:
			if( (techjoomla.jQuery('#'+fieldId).val() !=='') && (! parseInt(techjoomla.jQuery('#'+fieldId).val(),10) > 0 ) )
			{
				alert(fieldMsg);
				techjoomla.jQuery('#'+fieldId).val('');
				techjoomla.jQuery('#'+fieldId).focus();
				return false;
			}

			if( (techjoomla.jQuery('#'+fieldId).val() !=='') && (techjoomla.jQuery('#'+fieldId).val() < 0) )
			{
				alert(fieldMsg);
				techjoomla.jQuery('#'+fieldId).val('');
				techjoomla.jQuery('#'+fieldId).focus();
				return false;
			}
	}

	/* validate duration */
}
