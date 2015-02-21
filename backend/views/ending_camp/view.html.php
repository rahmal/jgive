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
class jgiveViewEnding_camp extends JViewLegacy
{
	function display($tpl = null)
	{
		global $mainframe, $option;
		if(JVERSION>=3.0)
			JHtmlBehavior::framework();
		else
			JHtml::_('behavior.mootools');

		$mainframe=JFactory::getApplication();

		//get logged in user id
		$user=JFactory::getUser();
		$this->logged_userid=$user->id;

		$this->issite=0;//this is backend

		//get params
		$params=JComponentHelper::getParams('com_jgive');
		$this->currency_code=$params->get('currency');

		$lang=JFactory::getLanguage();
		$lang->load('com_jgive',JPATH_SITE);

//Added By Sneha
		$layout=JFactory::getApplication()->input->get('layout','default');
		$this->setLayout($layout);
				//load submenu
		$JgiveHelper=new JgiveHelper();
		if($layout == 'default')
			$JgiveHelper->addSubmenu('ending_camp');
		else
			$JgiveHelper->addSubmenu('campaigns');

		//print_r($layout);  die('view.html');
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
		$filter_order=$mainframe->getUserStateFromRequest('com_jgive.filter_order','filter_order','','string');
		$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jgive.filter_order_Dir','filter_order_Dir','','word');

		// Category fillter

		$this->cat_options=$campaignHelper->getCampaignsCategories();
		//get filter value and set list
		$filter_campaign_cat=$mainframe->getUserStateFromRequest('com_jgive.filter_campaign_cat','filter_campaign_cat','','INT');
		$lists['filter_campaign_cat']=$filter_campaign_cat;
		$this->lists=$lists;

		//organization_individual_type filter since version 1.5.1

		//$org_ind_type=$campaignHelper->organization_individual_type();
		//$this->filter_org_ind_type= $org_ind_type;

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

		//Added by Sneha
		$filter_state = $mainframe->getUserStateFromRequest( $option.'search_list', 'search_list','', 'string' );
		$lists['search_list']= $filter_state;
		$this->assignRef('lists' , $lists);


		$start_date = $mainframe->getUserStateFromRequest( $option.'start_date', 'start_date','', 'string' );
		$date['start_date']= $start_date;
		$this->assignRef('date' , $date);

		$end_date = $mainframe->getUserStateFromRequest( $option.'end_date', 'end_date','', 'string' );
		$date['end_date']= $end_date;
		$this->assignRef('date' , $date);

		//End added by Sneha

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

		//Added By Sneha
		$layout=JFactory::getApplication()->input->get('layout','default');
		$this->setLayout($layout);
		if($layout == 'default')
		{
			JToolBarHelper::title(JText::_('COM_JGIVE_END_CAMPAIGNS' ),'icon-48-jgive.png' );
			JToolBarHelper::back( JText::_('JGIVE_HOME') , 'index.php?option=com_jgive');

			if(JVERSION>=3.0)
			{
				//JToolBarHelper::custom('ending_camp.csvexport', 'icon-32-save.png', 'icon-32-save.png',JText::_("CSV_EXPORT"), false);
				$button = "<a class='btn'
				class='button'
				type='submit'
				onclick=\"javascript:document.getElementById('task').value = 'ending_camp.csvexport';
				document.adminForm.submit();
				document.getElementById('task').value = '';\" href='#'>
				<span title='Export' class='icon-32-save'>
				</span>".JText::_('CSV_EXPORT')."</a>";
				$bar->appendButton( 'Custom', $button);
			}
			else
			{
				$button = "<a class='btn'
				class='button'
				type='submit'
				onclick=\"javascript:document.getElementById('task').value = 'ending_camp.csvexport';
				document.adminForm.submit();
				document.getElementById('task').value = '';\" href='#'>
				<span title='Export' class='icon-32-save'>
				</span>".JText::_('CSV_EXPORT')."</a>";
				$bar->appendButton( 'Custom', $button);
			}

		}
	}
}
?>
