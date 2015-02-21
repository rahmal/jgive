<?php
// no direct access
defined( '_JEXEC' ) or die( ';)' );

global $mainframe;
$mainframe = JFactory::getApplication();

JHtml::_('behavior.formvalidation');
$skip_reg=JText::_('COM_SKIP_REGISTRATION_PROCEED_PAYMENT');
$sign_up=JText::_('COM_JGIVE_BUTTON_SAVE_TEXT_REG');
?>

<script type="text/javascript">

function reg_hideshow(skip_reg,sign_up)
{
	var divstyle=document.getElementById('registration_form').style.display;
	if(divstyle=="none")
	{
		document.getElementById('registration_form').style.display="block";
		document.getElementById('nextbtn').value=sign_up;
	}
	else
	{	
		document.getElementById('registration_form').style.display="none";
		document.getElementById('nextbtn').value=skip_reg;
	}	
	
}
</script>

<?php 
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::base().'components/com_jgive/assets/css/jgive.css' );

if(JVERSION >= '1.6.0'){
$js = "
	Joomla.submitbutton = function(pressbutton){";
	} else {
	$js = "function submitbutton( pressbutton ) {";
 }

	
	$js .="var form = document.adminForm;
		if (pressbutton == 'cancel') {
			submitform(pressbutton);
			return;
		}
		{
			submitform(pressbutton);
			return;
		}
	}

	function submitform(pressbutton){
		 if (pressbutton) {
			document.adminForm.task.value = pressbutton;
		 }
		 if (typeof document.adminForm.onsubmit == 'function') {
		 	alert('yyy');
		 	document.adminForm.onsubmit();
		 }
		 	document.adminForm.submit();
	} 
	";
$document->addScriptDeclaration($js);	
$users = JFactory::getuser();?>
<?php 
?>
<div id="qtc_maindiv">
<div id="qtc_admin">
<div class="techjoomla-bootstrap" >
<form action="" method="post" name="adminForm" class=" form-horizontal form-validate" id="adminForm">

<div id="editcell"  class="span6">
	<!-- Header toolbar -->
	<legend class="componentheading"><?php echo JText::_('COM_JGIVE_SA_REGISTER');	?> </legend>
<div id="qtc_chkmthd1"  class="broadcast-expands" >
	<div id="login_button" style="margin-left:32%">
		<div>
			<fieldset>	
  			
					<a href='<?php 
						$msg=JText::_('COM_JGIVE_LOGIN_MSG');
						$uri='index.php?option=com_jgive&view=donations&layout=paymentform';
						$url=base64_encode($uri);
						echo 'index.php?option=com_users&view=login&return='.$url; ?>'>
						<div>
							<input id="LOGIN" class="btn btn-success btn-small validate" type="button" value="<?php echo JText::_('COM_JGIVE_BUTTON_LOGIN_TEXT_REG'); ?>">
						</div>
					</a>
	</div>
	</fieldset>
</div>	
</div>
	
		<legend class="componentheading"><?php echo JText::_('COM_JGIVE_OR_REGISTER');	?> </legend>
  <fieldset>
	<?php 
	$user=JFactory::getUser();
	
	if( !$user->id){  ?>
				<div id="qtc_chkmthd1"  class="broadcast-expands" >
					<div class="paddleft"><?php //echo JText::_('net value view')?> 					<!--given id-->
					  <div class="control-group">
						<label for="email1" class="control-label"><?php echo JText::_('COM_JGIVE_SEL_CHK_MTHD')?></label>
						<div class="controls">
							<label class="checkbox"><input type="checkbox" id="guest_regis" name="guest_regis" value="1" checked="checked" onchange="reg_hideshow('<?php echo $skip_reg;?>','<?php echo $sign_up;?>')">
			<?php echo JText::_('COM_JGIVE_CHK_REGIS'); ?></label>
						</div>
					  </div>
					</div>	
				</div>
	<?php 
	}
	?>  
	<div id="registration_form">
	
    <div class="control-group">
			<label class="control-label"  for="user_name">
					<?php echo JText::_( 'COM_JGIVE_USER_NAME' ); ?>
			</label>
			<div class="controls"><input class="inputbox  validate-name" type="text" name="user_name" id="user_name" size="10" maxlength="50" value="" /></div>
    </div>
    <div class="control-group">
			<label class="control-label"  for="user_email">
				<?php echo JText::_( 'COM_JGIVE_USER_EMAIL' ); ?>
			</label>
			<div class="controls"><input class="inputbox  validate-email" type="text" name="user_email" id="user_email" size="20" maxlength="100" value="" /></div>
    </div>
		
    <div class="control-group">
			<label class="control-label" for="confirm_user_email">
				<?php echo JText::_( 'COM_JGIVE_CONFIRM_USER_EMAIL' ); ?>
			</label>
			<div class="controls"><input class="inputbox  validate-email" type="text" name="confirm_user_email" id="confirm_user_email" size="20" maxlength="100" value="" /></div>
    </div>
   </fieldset>
	<div id="nextbtndiv" style="margin-left:32%">
		<input id="nextbtn" class="btn btn-success btn-small validate" type="submit" onclick="submitbutton('save');"  value="<?php echo JText::_('COM_JGIVE_BUTTON_SAVE_TEXT_REG'); ?>">
	</div>
	<div class="clr" ></div>	
	
	</div>
	<input type="hidden" name="option" value="com_jgive" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="controller" value="registration" />
	<input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid');?>" />
</div>
<?php echo JHtml::_( 'form.token' ); ?>
</form>
</div><!-- eoc techjoomla-bootstrap -->
</div>
	<?php 
	$document = JFactory::getDocument();
	$renderer	= $document->loadRenderer('modules');
	$position	= 'tj_login';
	$options	= array('style' => 'raw');
	 echo $renderer->render($position, $options, null);?>

<div class="clr"></div>
</div>

