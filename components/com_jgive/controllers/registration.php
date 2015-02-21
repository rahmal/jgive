<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once( JPATH_COMPONENT.DS.'controller.php' );

jimport('joomla.application.component.controller');

class jgiveControllerregistration extends JControllerLegacy
{

	function __construct()
	{
		parent::__construct();
		
	}

	function save()
	{
		$jinput=JFactory::getApplication()->input;
		$model = $this->getModel('registration');
        $session = JFactory::getSession();
        //get data from request
		$post	= JRequest::get('post');
		// let the model save it
 		if(array_key_exists('guest_regis',$post))
 		{
			$result = $model->store($post);
			
			if ($result) 
			{
				$message = JText::_( 'COM_JGIVE_REGIS_USER_CREATE_MSG' );
				$itemid=$jinput->get('Itemid');
				$user = JFactory::getuser();            
				$this->setRedirect('index.php?option=com_jgive&view=donations&layout=paymentform&Itemid='.$itemid, $message);
			} 
			else
			{
				$message = JText::_('COM_JGIVE_REGISTRATION_FAILED');
				$itemid=$jinput->get('Itemid');    
				$this->setRedirect('index.php?option=com_jgive&view=registration&Itemid='.$itemid, $message);
			}
		}
		else
		{
			$session->set('quick_reg_no_login','1');
			$this->setRedirect('index.php?option=com_jgive&view=donations&layout=paymentform&Itemid='.$itemid, $message);
		}
    }
	
	function cancel()
	{
		$msg = JText::_( 'Operation Cancelled' );
		$jinput=JFactory::getApplication()->input;
		$itemid=$jinput->get('Itemid');
		$session=JFactory::getSession();
		$session->set('quick_reg_no_login','1');
		$this->setRedirect('index.php?option=com_jgive&view=donations&layout=paymentform&Itemid='.$itemid, $message);

	}

}

