<?php
// no direct access
defined( '_JEXEC' ) or die( ';)' );
jimport( 'joomla.application.component.view');
class jgiveViewDonations extends JViewLegacy
{
	function display($tpl = null)
	{
		global $mainframe,$option;
		$mainframe=JFactory::getApplication();

		//load submenu
		$JgiveHelper=new JgiveHelper();
		$JgiveHelper->addSubmenu('donations');

		$option=JFactory::getApplication()->input->get('option');
		$campaignHelper=new campaignHelper();
		//get params
		$params=JComponentHelper::getParams('com_jgive');
		$this->currency_code=$params->get('currency');

		//get logged in user id
		$user=JFactory::getUser();
		$this->logged_userid=$user->id;

		//default layout is default
		$layout=JFactory::getApplication()->input->get('layout','all');
		$this->setLayout($layout);


		//set common view variables
		//use frontend helper
		$frontpath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helper.php';
		if(!class_exists('jgiveFrontendHelper'))
		{
			JLoader::register('jgiveFrontendHelper', $frontpath );
			JLoader::load('jgiveFrontendHelper');
		}
		$path=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php';
		if(!class_exists('donationsHelper'))
		{
			JLoader::register('donationsHelper', $path );
			JLoader::load('donationsHelper');
		}
		$donationsHelper=new donationsHelper();
		$this->pstatus=$donationsHelper->getPStatusArray();
		$this->sstatus=$donationsHelper->getSStatusArray();//used on donattions view for payment status filter

		//imp
		$this->donations_site=0;//this is backend

		if($layout=='details')
		{
			/*load language file for component backend*/
			$lang = JFactory::getLanguage();
			$lang->load('com_jgive', JPATH_SITE);

			$donation_details=$this->get('SingleDonationInfo');
			$this->assignRef('donation_details',$donation_details);
		}
		if($layout=='all')
		{
			$donations=$this->get('Donations');
			$this->donations=$donations;

			//get ordering filter
			$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jgive.filter_order_Dir','filter_order_Dir','desc','word');
			$filter_type=$mainframe->getUserStateFromRequest('com_jgive.filter_order','filter_order','id','string');
			//Campaigns Type Fillter
			$this->campaign_type_filter_options=$campaignHelper->getCampaignTypeFilterOptions();
			//get filter value and set list
			$filter_campaign_type=$mainframe->getUserStateFromRequest('com_jgive.filter_campaign_type','filter_campaign_type','','string');
			$lists['filter_campaign_type']=$filter_campaign_type;
			//campaign list filter
			$filter_campaign_options=array();

			if(JVERSION<3.0)
				$filter_campaign_options[]=JHtml::_('select.option','0',JText::_('COM_JGIVE_SELECT_CAMPAIGN'));

			$campaign_list=$campaignHelper->getAllCampaignOptions();
			if(!empty($campaign_list))
			{
				foreach($campaign_list as $key=>$campaign){
					$filter_campaign_options[]=JHtml::_('select.option',$campaign->id,$campaign->title);
				}
			}
			$this->filter_campaign_options=$filter_campaign_options;
			//get filter value and set list
			$filter_campaign=$mainframe->getUserStateFromRequest($option.'filter_campaign','filter_campaign','','string');

			$cid=JFactory::getApplication()->input->get('cid',0);
			if($cid!=0){
				//important
				//this is used when redirected from other view to this view
				//so change filter value to be set as passed in url
				$filter_campaign=$cid;
			}
			//payment status filter
			$payment_status=$mainframe->getUserStateFromRequest('com_jgive'.'payment_status', 'payment_status','', 'string' );
			if($payment_status==null){
				$payment_status='-1';
			}

			//set filters
			$lists['filter_campaign']=$filter_campaign;
			$lists['payment_status']=$payment_status;
			$lists['order_Dir']=$filter_order_Dir;
			$lists['order']=$filter_type;
			$this->lists=$lists;

			$total=$this->get('Total');
			$this->total=$total;

			$pagination=$this->get('Pagination');
			$this->pagination=$pagination;
		}

		if($layout=='paymentform')
		{
			$path=JPATH_ROOT.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'donations.php';
			if(!class_exists('donationsHelper'))
			{
				JLoader::register('donationsHelper', $path );
				JLoader::load('donationsHelper');
			}

			$path=JPATH_ROOT.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'integrations.php';
			if(!class_exists('integrationsHelper'))
			{
				JLoader::register('integrationsHelper', $path );
				JLoader::load('integrationsHelper');
			}

			$jgiveFrontendHelper=new jgiveFrontendHelper();
			$donationsHelper=new donationsHelper();
			$this->pstatus=$donationsHelper->getPStatusArray();
			$this->sstatus=$donationsHelper->getSStatusArray();//used on donations view for payment status filter
			//get country list options
			//use helper function
			$countries=$jgiveFrontendHelper->getCountries();
			$this->countries=$countries;
			//get campaign id
			$cid=$this->get('CampaignId');
			$this->cid=$cid;
			$cdata=$this->get('AllCampaigns');
			$cusers=$this->get('Allusers');
			$this->assignRef('cdata',$cdata);
			$this->assignRef('cusers',$cusers);


			$params=JComponentHelper::getparams('com_jgive');
			//joomla profile import
			$session=JFactory::getSession();
			$nofirst='';
			$nofirst=$session->get('No_first_donation'); //if no user data set in session then only import the user profile or only first time after login
			if(empty($nofirst))
			{
				$profile_import=$params->get('profile_import');
				//if profie import is on the call profile import function
				if($profile_import)
				{
					$integrationsHelper=new integrationsHelper();

				}
			}
			$this->guest_donation=$params->get('guest_donation');
			$session=JFactory::getSession();
			$this->session=$session;
			//geteways
			$dispatcher=JDispatcher::getInstance();
			JPluginHelper::importPlugin('payment');

			$params=JComponentHelper::getParams('com_jgive');
			$this->gateways=$params->get('gateways');

			$gateways=array();
			if(!empty($this->gateways)){
				$gateways = $dispatcher->trigger('onTP_GetInfo',array((array)$this->gateways)); //array typecasting is imp
			}
			$this->assignRef('gateways',$gateways);

		}
		//set toolbar
		$this->_setToolBar($layout);
		if(JVERSION>=3.0)
			$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);

	}//function display ends here

	function _setToolBar($layout)
	{
		//Get the toolbar object instance
		$document=JFactory::getDocument();
		$document->addStyleSheet(JUri::base().'components/com_jgive/assets/css/jgive.css');
		$bar=JToolBar::getInstance('toolbar');
		$layout=JFactory::getApplication()->input->get('layout','');
		if($layout=='all' or $layout=='')
		{
			JToolBarHelper::addNew('donations.'.$task = 'addNewDonation', $alt = JText::_('COM_JGIVE_NEW_DONATION'));
		}

		if($layout=='paymentform')
		{
			JToolBarHelper::cancel('donations.'.$task = 'cancelorder', $alt =JText::_('COM_JGIVE_CANCEL')  );

			JToolBarHelper::save('donations.'.$task = 'placeOrder', $alt =JText::_('COM_JGIVE_CONTINUE_CONFIRM_FREE')  );
		}

		if($layout!='details')
		{
			if($layout!='paymentform')
			{
				JToolBarHelper::deleteList('','deleteDonations');
				JToolBarHelper::back('COM_JGIVE_BACK','index.php?option=com_jgive&layout=all');
			}
		}else{
			JToolBarHelper::back('COM_JGIVE_BACK','index.php?option=com_jgive&view=donations');
		}

		JToolBarHelper::title(JText::_('COM_JGIVE_DONATIONS' ),'icon-48-jgive.png' );
		JToolBarHelper::preferences( 'com_jgive' );

		if(JVERSION>=3.0 AND $layout=='all')
		{

			$campaignHelper=new campaignHelper();
			$campaign_type=$campaignHelper->filedToShowOrHide('campaign_type');

			if($campaign_type)
			{
				JHtmlSidebar::addFilter(
					JText::_('COM_JGIVE_FILTER_SELECT_TYPE'),
					'filter_campaign_type',
					JHtml::_('select.options', $this->campaign_type_filter_options, 'value', 'text', $this->lists['filter_campaign_type'], true)
				);
			}
			//@Campaign Filter
			JHtmlSidebar::addFilter(
				JText::_('COM_JGIVE_SELECT_CAMPAIGN'),
				'filter_campaign',
				JHtml::_('select.options', $this->filter_campaign_options, 'value', 'text', $this->lists['filter_campaign'], true)
			);
			//@Donation Status Filter
			JHtmlSidebar::addFilter(
				JText::_('COM_JGIVE_APPROVAL_STATUS'),
				'payment_status',
				JHtml::_('select.options', $this->sstatus, 'value', 'text', $this->lists['payment_status'], true)
			);

		}
	}
}// class
