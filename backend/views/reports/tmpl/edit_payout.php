<?php
/**
 * @package InviteX
 * @copyright Copyright (C) 2009 -2010 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');
$document=JFactory::getDocument();

//load validation scripts
JHtml::_('behavior.formvalidation');

//echo "<pre>";print_r($this->getPayoutFormData);echo "</pre>";

$js="
function fill_me(el)
{
	//console.log(el);
	techjoomla.jQuery('#payee_name').val(techjoomla.jQuery('#payee_options option:selected').text());
	techjoomla.jQuery('#user_id').val(techjoomla.jQuery('#payee_options option:selected').val());
	techjoomla.jQuery('#payment_amount').val(user_amount_map[techjoomla.jQuery('#payee_options option:selected').val()]);
	techjoomla.jQuery('#paypal_email').val(user_email_map[techjoomla.jQuery('#payee_options option:selected').val()]);
}
var user_amount_map=new Array();";

foreach($this->getPayoutFormData as $payout)
{
	//@TODO remove this function call somewhere else
	$reportsHelper=new reportsHelper();
	(float)$totalpaidamount=$reportsHelper->getTotalPaidOutAmount($payout->creator_id);
	$amt=(float)$payout->total_amount - (float) $payout->total_commission -(float)$totalpaidamount;
	$js.="user_amount_map[".$payout->creator_id."]=".$amt.";";
}

$js.="var user_email_map=new Array();";
foreach($this->getPayoutFormData as $payout){
	$js.="user_email_map[".$payout->creator_id."]='".$payout->paypal_email."';";
}

//echo $js;

$document->addScriptDeclaration($js);

$js_joomla15="function submitbutton(task)
{
        if (task == '')
        {
                return false;
        }
        else
        {
                var isValid=true;
                var action = task.split('.');
                if (action[1] != 'cancel' && action[1] != 'close')
                {
                        var forms = $$('form.form-validate');
                        for (var i=0;i<forms.length;i++)
                        {
                                if (!document.formvalidator.isValid(forms[i]))
                                {
                                        isValid = false;
                                        break;
                                }
                        }
                }

                if (isValid)
                {
                        /*Joomla.submitform(task);*/
                        document.adminForm.submit();
                        return true;
                }
                else
                {
                        alert(Joomla.JText._('COM_JGIVE_ERROR_UNACCEPTABLE','Some values are unacceptable'));
                        return false;
                }
        }
}
";

$js_joomla16="
Joomla.submitbutton = function(task)
{
        if (task == '')
        {
                return false;
        }
        else
        {
                var isValid=true;
                var action = task.split('.');
                if (action[1] != 'cancel' && action[1] != 'close')
                {
                        var forms = $$('form.form-validate');
                        for (var i=0;i<forms.length;i++)
                        {
                                if (!document.formvalidator.isValid(forms[i]))
                                {
                                        isValid = false;
                                        break;
                                }
                        }
                }

                if (isValid)
                {
                        /*Joomla.submitform(task);*/
                        document.adminForm.submit();
                        return true;
                }
                else
                {
                        alert(Joomla.JText._('COM_JGIVE_ERROR_UNACCEPTABLE','Some values are unacceptable'));
                        return false;
                }
        }
}
";

if(JVERSION >= '1.6.0')
	$document->addScriptDeclaration($js_joomla16);
else
	$document->addScriptDeclaration($js_joomla15);

//override active menu class to remove active class from other submenu
$menuCssOverrideJs="techjoomla.jQuery(document).ready(function(){
	techjoomla.jQuery('ul>li> a[href$=\"index.php?option=com_jgive&view=reports\"]:last').removeClass('active');
});";
$document->addScriptDeclaration($menuCssOverrideJs);
//$type=$this->type_data;
?>

<div class="techjoomla-bootstrap">

	<form action="" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data"
	class="form-horizontal form-validate" >
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
		<div class="control-group">
			<label class="control-label" for="payee_name" title="<?php echo JText::_('COM_JGIVE_PAYEE_NAME');?>">
				<?php echo JText::_('COM_JGIVE_PAYEE_NAME');?>
			</label>
			<div class="controls">
				<input type="text" id="payee_name" name="payee_name" class="required" maxlength="250"
				placeholder="<?php echo JText::_('COM_JGIVE_PAYEE_NAME');?>"
				value="<?php if(isset($this->payout_data->payee_name)) echo $this->payout_data->payee_name;?>">

				<?php
					echo JHtml::_('select.genericlist', $this->payee_options, "payee_options", 'class="" size="1"
					onchange="fill_me(this)" name="payee_options"',"value", "text", '');
				?>
				<i>Select payee name to auto fill data</i>

			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="user_id" title="<?php echo JText::_('COM_JGIVE_USER_ID');?>">
				<?php echo JText::_('COM_JGIVE_USER_ID');?>
			</label>
			<div class="controls">
				<input type="text" id="user_id" name="user_id" class="required validate-numeric" maxlength="250" placeholder="<?php echo JText::_('COM_JGIVE_USER_ID');?>"
				value="<?php if(isset($this->payout_data->user_id)) echo $this->payout_data->user_id;?>">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="paypal_email" title="<?php echo JText::_('COM_JGIVE_PAYPAL_EMAIL');?>">
				<?php echo JText::_('COM_JGIVE_PAYPAL_EMAIL');?>
			</label>
			<div class="controls">
				<input type="text" id="paypal_email" name="paypal_email" class="required validate-email" maxlength="250" placeholder="<?php echo JText::_('COM_JGIVE_PAYPAL_EMAIL');?>"
				value="<?php if(isset($this->payout_data->email_id)) echo $this->payout_data->email_id;?>">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="transaction_id" title="<?php echo JText::_('COM_JGIVE_TRANSACTION_ID');?>">
				<?php echo JText::_('COM_JGIVE_TRANSACTION_ID');?>
			</label>
			<div class="controls">
				<input type="text" id="transaction_id" name="transaction_id" class="required" maxlength="250" placeholder="<?php echo JText::_('COM_JGIVE_TRANSACTION_ID');?>"
				value="<?php if(isset($this->payout_data->transaction_id)) echo $this->payout_data->transaction_id;?>">
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="payout_date" title="<?php echo JText::_('COM_JGIVE_DATE');?>">
				<?php echo JText::_('COM_JGIVE_DATE');?>
			</label>
			<div class="controls">
				<?php
					$date=date('');//set date to blank
					if(isset($this->payout_data->date))
						$date=$this->payout_data->date;
					echo JHtml::_('calendar',$date,'payout_date','payout_date',JText::_('%Y-%m-%d '),"class='required'");//@TODO use jtext for date format
				?>
			</div>
		</div>


		<div class="control-group">
			<label class="control-label" for="payment_amount" title="<?php echo JText::_('COM_JGIVE_PAYOUT_AMOUNT');?>">
				<?php echo JText::_('COM_JGIVE_PAYOUT_AMOUNT');?>
			</label>
			<div class="controls">
			    <div class="input-append">
					<input type="text" id="payment_amount" name="payment_amount" class="required validate-numeric" maxlength="11"
					placeholder="<?php echo JText::_('COM_JGIVE_PAYOUT_AMOUNT');?>"
					value="<?php if(isset($this->payout_data->amount)) echo $this->payout_data->amount;?>">
					<span class="add-on"><?php echo $this->currency_code;?></span>
				</div>
			</div>
		</div>

		<?php
			$status1=$status2='';
			if(isset($this->payout_data->status))
			{
				if($this->payout_data->status)
					$status1='checked';
				else
					$status2='checked';
			}else{
				$status2='checked';
			}
		?>
		<div class="control-group">
			<label class="control-label" title="<?php echo JText::_('COM_JGIVE_STATUS');?>">
				<?php echo JText::_('COM_JGIVE_STATUS');?>
			</label>
			<div class="controls">
				<label class="radio inline">
					<input type="radio" name="status" id="status1" value="1" <?php echo $status1;?>>
						<?php echo JText::_('COM_JGIVE_PAID');?>
				</label>
				<label class="radio inline">
					<input type="radio" name="status" id="status2" value="0" <?php echo $status2;?>>
						<?php echo JText::_('COM_JGIVE_NOT_PAID');?>
				</label>
			</div>
		</div>

		<input type="hidden" name="option" value="com_jgive" />
		<input type="hidden" name="controller" value="reports" />
		<input type="hidden" name="task" value="<?php 

		if(!empty($this->payout_data->id))
		{
			echo 'editPayout';
		}
		else
		{
			echo 'SaveNewPayout';
		}
		
		?>" />

		<input type="hidden" name="edit_id" value="<?php echo $this->payout_data->id;?>" />
		<?php echo JHtml::_( 'form.token' ); ?>

	</form>

</div>
