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
		$mainframe=JFactory::getApplication();
		//get logged in user id
		$user=JFactory::getUser();
		$this->logged_userid=$user->id;

		$this->issite=1;//this is frontend

		$jgiveFrontendHelper=new jgiveFrontendHelper();
		$this->jomsocailToolbarHtml = $jgiveFrontendHelper->jomsocailToolbarHtml();

		//get itemid
		$this->singleCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
		$this->myCampaignsItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=my');
		$this->allCampaignsItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaigns&layout=all');
		$this->createCampaignItemid=$jgiveFrontendHelper->getItemId('index.php?option=com_jgive&view=campaign&layout=create');

		//get params
		//$params=$mainframe->getParams();//JComponentHelper::getParams('com_jgive');
		// Get some data from the models
		$state=$this->get('State');
		$params=$state->params;

		$this->params=$params;
		$this->currency_code=$params->get('currency');

		//default layout is all
		$layout=JFactory::getApplication()->input->get('layout','all');
		$this->setLayout($layout);

		// Category fillter
		$campaignHelper=new campaignHelper();
		$this->cat_options=$campaignHelper->getCampaignsCategories();

		//get filter value and set list
		$filter_campaign_cat = $mainframe->getUserStateFromRequest('com_jgive.filter_campaign_cat','filter_campaign_cat',$this->params->get('defualtCatid'),'INT');

		$lists['filter_campaign_cat']=$filter_campaign_cat;
		$this->lists=$lists;

		//Campaigns Type fillter

		if($layout=='my')
		{
			if(!$this->logged_userid)
			{
				$msg=JText::_('COM_JGIVE_LOGIN_MSG');
				$uri=JFactory::getApplication()->input->get('REQUEST_URI','','server','string');
				$url=base64_encode($uri);
				$mainframe->redirect(JRoute::_('index.php?option=com_users&view=login&return='.$url),$msg);
			}

		}

		jimport('joomla.html.pagination');
		//Get data from the model
		$data=$this->get('Data');
		$pagination=$this->get('Pagination');

		//push data into the template
		$this->data=$data;
		$this->pagination=$pagination;

		//ordering
		$filter_order=$mainframe->getUserStateFromRequest('com_jgive.filter_order','filter_order',$this->params->get('default_sort_by_option'),'string');

		$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jgive.filter_order_Dir','filter_order_Dir',$this->params->get('filter_order_Dir'),'string');

		//load all filter values
		$this->user_filter_options=$this->get('UserFilterOptions');
		$this->campaign_type_filter_options=$this->get('CampaignTypeFilterOptions');
		$this->ordering_options=$this->get('OrderingOptions');
		$this->ordering_direction_options=$this->get('OrderingDirectionOptions');
		//get Countries for filter
		$countries=$jgiveFrontendHelper->getCountries();
		$this->countries_filter=$countries;

		//organization_individual_type filter since version 1.5.1
		$campaignHelper=new campaignHelper();
		$org_ind_type=$campaignHelper->organization_individual_type();
		$this->filter_org_ind_type= $org_ind_type;

		$filter_user=$mainframe->getUserStateFromRequest('com_jgive'.'filter_user','filter_user');
		$filter_campaign_type=$mainframe->getUserStateFromRequest('com_jgive'.'filter_campaign_type','filter_campaign_type');

		$filter_org_ind_type=$mainframe->getUserStateFromRequest('com_jgive'.'filter_org_ind_type','filter_org_ind_type');
		$filter_org_ind_type_my=$mainframe->getUserStateFromRequest('com_jgive'.'filter_org_ind_type_my','filter_org_ind_type_my');

		$campaign_countries_filter=$mainframe->getUserStateFromRequest('com_jgive'.'campaign_countries','campaign_countries');
		$campaign_states_filter=$mainframe->getUserStateFromRequest('com_jgive'.'campaign_states','campaign_states');
		$campaign_city_filter=$mainframe->getUserStateFromRequest('com_jgive'.'campaign_city','campaign_city');

		// Default value O - Will show 'ongoing' campaigns by default.
		$filter_campaigns_to_show=$mainframe->getUserStateFromRequest('com_jgive'.'campaigns_to_show', 'campaigns_to_show', '0');
		//for countries


		$countryarray= array();
		$countryarray[] = JHtml::_('select.option','', JText::_('COM_JGIVE_SELONE_COUNTRY'));
		//get countries
		$campaign_countries=$this->get('countries');
		foreach($campaign_countries  as $tmp)
		{
			$value=$tmp;
			$option=$tmp;
			$countryarray[] = JHtml::_('select.option',$value, $option);
		}
		$this->countryoption=$countryarray;

		//for state
		$statearray=array();
		$statearray[] = JHtml::_('select.option','', JText::_('COM_JGIVE_SELECT_STATE'));
		//get states
		$campaign_states=$this->get('CampaignStates');
		if(isset($campaign_states))
			foreach($campaign_states  as $tmp)
			{
				$value=$tmp;
				$option=$tmp;
				$statearray[] = JHtml::_('select.option',$value, $option);
			}
		$this->campaign_states=$statearray;
		//for city
		$cityarray=array();
		$cityarray[] = JHtml::_('select.option','', JText::_('COM_JGIVE_SELECT_CITY'));
		//get states
		$campaign_city=$this->get('CampaignCity');
		if(isset($campaign_city))
			foreach($campaign_city  as $tmp)
			{
				$value=$tmp;
				$option=$tmp;
				$cityarray[] = JHtml::_('select.option',$value, $option);
			}
		$this->campaign_city=$cityarray;

		/** get Campaigns to show filter options
		 */
		$campaigns_to_show=$campaignHelper->campaignsToShowOptions();
		$this->campaigns_to_show=$campaigns_to_show;
		//print_r($this->campaigns_to_show);die;

		//set all filters in list
		$lists['filter_order']=$filter_order;
		$lists['filter_order_Dir']=$filter_order_Dir;
		$lists['filter_user']=$filter_user;
		$lists['filter_campaign_type']=$filter_campaign_type;
		$lists['campaign_countries']=$campaign_countries_filter;
		$lists['campaign_states']=$campaign_states_filter;
		$lists['campaign_city']=$campaign_city_filter;
		$lists['filter_org_ind_type']=$filter_org_ind_type;
		$lists['filter_org_ind_type_my']=$filter_org_ind_type_my;
		$lists['campaigns_to_show']=$filter_campaigns_to_show;


		$this->lists=$lists;

		//Added by Sneha for search and filter
		$filter_state = $mainframe->getUserStateFromRequest( $option.'search_list', 'search_list','', 'string' );
		$start_date = $mainframe->getUserStateFromRequest( $option.'start_date', 'start_date','', 'string' );
		$end_date = $mainframe->getUserStateFromRequest( $option.'end_date', 'end_date','', 'string' );
		$lists['search_list']= $filter_state;
		//$date['start_date']=$start_date;
		$lists['start_date']= $start_date;
		$lists['end_date']= $end_date;

		$this->assignRef('lists' , $lists);
		$this->assignRef('date', $date);

		//End added by Sneha
		parent::display($tpl);
	}
}
?>
