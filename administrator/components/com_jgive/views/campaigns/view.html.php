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
jimport( 'joomla.application.component.view' );
class jgiveViewCampaigns extends JViewLegacy
{
	function display($tpl = null)
	{
		if(JVERSION>=3.0)
			JHtmlBehavior::framework();
		else
			JHtml::_('behavior.mootools');

		$mainframe=JFactory::getApplication();
		//load submenu
		$JgiveHelper=new JgiveHelper();
		$JgiveHelper->addSubmenu('campaigns');

		//get logged in user id
		$user=JFactory::getUser();
		$this->logged_userid=$user->id;

		$this->issite=0;//this is backend

		//get itemid
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$this->singleCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=single');
		$this->myCampaignsItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my');
		$this->allCampaignsItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
		$this->createCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=create');

		//get params
		$params=JComponentHelper::getParams('com_jgive');
		$this->currency_code=$params->get('currency');

		$lang=JFactory::getLanguage();
		$lang->load('com_jgive',JPATH_SITE);

		//default layout is all
		$layout=JFactory::getApplication()->input->get('layout','all_list');
		$this->setLayout($layout);

		jimport('joomla.html.pagination');
		//Get data from the model
		$data=$this->get('Data');
		$pagination=$this->get('Pagination');

		//get campaign status
		$campaignHelper=new campaignHelper();
		$data=$campaignHelper->getCampaignStatus($data);

		//push data into the template
		$this->data=$data;
		$this->pagination=$pagination;

		//ordering
		$filter_order=$mainframe->getUserStateFromRequest('com_jgive.filter_order','filter_order','created','string');
		$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jgive.filter_order_Dir','filter_order_Dir','desc','word');

		// Category fillter

		$this->cat_options=$campaignHelper->getCampaignsCategories();
		//get filter value and set list
		$filter_campaign_cat=$mainframe->getUserStateFromRequest('com_jgive.filter_campaign_cat','filter_campaign_cat','','INT');
		$lists['filter_campaign_cat']=$filter_campaign_cat;
		$this->lists=$lists;
		
		//organization_individual_type filter since version 1.5.1

		$org_ind_type=$campaignHelper->organization_individual_type();
		$this->filter_org_ind_type= $org_ind_type;		

		//load all filter values
		$this->user_filter_options=$this->get('UserFilterOptions');
		$this->campaign_type_filter_options=$this->get('CampaignTypeFilterOptions');
		$this->campaign_approve_filter_options=$this->get('CampaignApproveFilterOptions');
		$this->ordering_options=$this->get('OrderingOptions');
		$this->ordering_direction_options=$this->get('OrderingDirectionOptions');

		//load current value for filter
		$filter_user=$mainframe->getUserStateFromRequest('com_jgive'.'filter_user','filter_user');
		$filter_campaign_type=$mainframe->getUserStateFromRequest('com_jgive'.'filter_campaign_type','filter_campaign_type');

		$filter_org_ind_type=$mainframe->getUserStateFromRequest('com_jgive'.'filter_org_ind_type','filter_org_ind_type');
		//filter to view unpulish & publish campaign
		$filter_campaign_approve=$mainframe->getUserStateFromRequest('com_jgive'.'filter_campaign_approve','filter_campaign_approve','','INT');
		//check the email link to apporove if it is then set pending value for filter
		$approve=JFactory::getApplication()->input->get('approve','','INT');
		if($approve){
			$filter_campaign_approve=0;
		}

		//set all filters in list
		$lists['filter_order']=$filter_order;
		$lists['filter_order_Dir']=$filter_order_Dir;

		$lists['filter_user']=$filter_user;
		$lists['filter_campaign_type']=$filter_campaign_type;
		$lists['filter_campaign_approve']=$filter_campaign_approve;
		$lists['filter_org_ind_type']=$filter_org_ind_type;
		$this->lists=$lists;

		// manoj - added for bill start
		$this->campaignSuccessStatus = $campaignHelper->getCampaignSuccessStatusArray();
		// manoj - added for bill end

		//set toolbar
		$this->_setToolBar();
		if(JVERSION>=3.0)
			$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	function _setToolBar()
	{	$document=JFactory::getDocument();
		$document->addStyleSheet(JUri::base().'components/com_jgive/assets/css/jgive.css');
		$bar=JToolBar::getInstance('toolbar');
		JToolBarHelper::title(JText::_('COM_JGIVE_CAMPAIGNS' ),'icon-48-jgive.png' );
		$layout=JFactory::getApplication()->input->get('layout','');
		
		if($layout=='all_list')
		JToolBarHelper::addNew($task = 'campaigns.addNew', $alt = JText::_('COM_JGIVE_NEW_DONATION'));

		if(JVERSION>=3.0){
			JToolBarHelper::custom( 'feature' , 'featured' , '' , JText::_( 'COM_JGIVE_FEATURE_TOOLBAR' ) );
			JToolBarHelper::custom( 'unfeature' ,'star-empty','' , JText::_( 'COM_JGIVE_UNFEATURE_TOOLBAR' ) );
		}
		else{
			JToolBarHelper::custom( 'feature' , 'jgive-feature.png' , '' , JText::_( 'COM_JGIVE_FEATURE_TOOLBAR' ) );
			JToolBarHelper::custom( 'unfeature' ,'jgive-unfeature','' , JText::_( 'COM_JGIVE_UNFEATURE_TOOLBAR' ) );
		}



		if(JVERSION>=3.0)
		{
			JHtmlSidebar::setAction('index.php?option=com_jgive');
			//@Type filter
			$campaignHelper=new campaignHelper();
			$campaign_type=$campaignHelper->filedToShowOrHide('campaign_type');
			if($campaign_type)
			{
				JHtmlSidebar::addFilter(
					JText::_('COM_JGIVE_FILTER_SELECT_TYPE'),
					'filter_campaign_type',
					JHtml::_('select.options',$this->campaign_type_filter_options, 'value', 'text', $this->lists['filter_campaign_type'], true)
				);
			}
			//@ Categories filter
			JHtmlSidebar::addFilter(
				JText::_('COM_JGIVE_CAMPAIGN_CATEGORIES'),
				'filter_campaign_cat',
				JHtml::_('select.options', $this->cat_options, 'value', 'text', $this->lists['filter_campaign_cat'], true)
			);
			//@ organization in filter
			JHtmlSidebar::addFilter(
				JText::_('COM_JGIVE_SELECT_TYPE_ORG_INDIVIDUALS'),
				'filter_org_ind_type',
				JHtml::_('select.options', $this->filter_org_ind_type, 'value', 'text', $this->lists['filter_org_ind_type'], true)
			);
			//@ Campaign status filter
			JHtmlSidebar::addFilter(
				JText::_('COM_JGIVE_FILTER_APPROVED'),
				'filter_campaign_approve',
				JHtml::_('select.options', $this->campaign_approve_filter_options, 'value', 'text', $this->lists['filter_campaign_approve'], true)
			);

		}

	}
}
?>
