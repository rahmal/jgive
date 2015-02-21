<?php
/**
 * @version		1.5 jgive $
 * @package		jgive
 * @copyright	Copyright © 2013 - All rights reserved.
 * @license		GNU/GPL
 * @author		TechJoomla
 * @author mail	extensions@techjoomla.com
 * @website		http://techjoomla.com
 *
 */
//no direct access
defined('_JEXEC') or die('Restricted access');
//jimport('joomla.application.component.model');
jimport('joomla.application.component.modellist');
//class jgiveModelCampaigns extends JModelLegacy
class jgiveModelCampaigns extends JModelList
{
	/**
	* Items total
	* @var integer
	*/
	var $_total = null;
	/**
	* Pagination object
	* @var object
	*/
	var $_pagination = null;

	function __construct()
	{
		parent::__construct();
		global $mainframe;
		$mainframe=JFactory::getApplication();
        //Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = JFactory::getApplication()->input->get('limitstart', 0, '', 'int');
        //In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null) {
		// Initialise variables.
		$app = JFactory::getApplication();
		if($app->isAdmin())
			return;
		// Load the parameters. Merge Global and Menu Item params into new object
		$params = $app->getParams();
		$menuParams = new JRegistry;

		if ($menu = $app->getMenu()->getActive()) {
			$menuParams->loadString($menu->params);
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);

		$this->setState('params', $mergedParams);
	}

	function getData()
	{
		// if data hasn't already been obtained, load it
		$jgiveFrontendHelper=new jgiveFrontendHelper();
		if (empty($this->_data)) {
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		/////////////////////////////////////////////////////////////////////////
		//modifiy the data
		require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."campaign.php");
		$campaignHelper=new campaignHelper();

		$cdata=array();
		$i=0;
		foreach($this->_data as $d)
		{

			//get campaign amounts
			$amounts=$campaignHelper->getCampaignAmounts($d->id);
			$d->amount_received=$amounts['amount_received'];
			$d->remaining_amount=$amounts['remaining_amount'];
			//count donors(donations)
			$d->donor_count=$campaignHelper->getCampaignDonorsCount($d->id);
			//get campaign images

		}
		global $mainframe,$option;
		$mainframe=JFactory::getApplication();

		if($mainframe->isAdmin())
		{

			$filter_order=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','created','cmd');

			$filter_order_Dir=$mainframe->getUserStateFromRequest($option.'filter_order_Dir','filter_order_Dir','desc','cmd');

		}
		else
		{
			$filter_order=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order',$mainframe->getParams()->get('default_sort_by_option'),'cmd');

			$filter_order_Dir=$mainframe->getUserStateFromRequest($option.'filter_order_Dir','filter_order_Dir',$mainframe->getParams()->get('filter_order_Dir'),'cmd');

		}
		if($filter_order=='donor_count' || $filter_order=='amount_received' || $filter_order=='remaining_amount')
		{
			$this->_data=$jgiveFrontendHelper->multi_d_sort($this->_data,$filter_order,$filter_order_Dir);
		}


		foreach($this->_data as $d)
		{
			$cdata[$d->id]['campaign']=$d;

			//get campaign images
			$cdata[$d->id]['images']=$campaignHelper->getCampaignImages($d->id);
		}
		//print_r($data);

		//Add Mark to successful campaigns
		foreach($cdata as $key)
		{
			$key['campaign']->successful=0;
			//Changed condition by Sneha, when goal amount is absent
			 if(($key['campaign']->amount_received>=$key['campaign']->goal_amount) && ($key['campaign']->amount_received > 0)){
				$key['campaign']->successful=1;
			 }
		}
		$this->_data=$cdata;

		/////////////////////////////////////////////////////////////////////////

		return $this->_data;
	}

	function _buildQuery()
	{
		//build query as you want
		$db=JFactory::getDBO();
		global $mainframe,$option;
		$mainframe=JFactory::getApplication();
		$option=JFactory::getApplication()->input->get('option');

		//Get the WHERE and ORDER BY clauses for the query
		$where='';
		$where=$this->_buildContentWhere();
		//modified by SNeha, bug id:24903
		$query="SELECT c.*,cat.title as cat_name
		FROM #__jg_campaigns AS c
		LEFT JOIN #__categories as cat ON c.category_id=cat.id
		LEFT JOIN #__users AS u ON c.creator_id = u.id
		".$where;

		//$filter_order='goal_amount';

		if($mainframe->isAdmin())
		{
			$filter_order=$mainframe->getUserStateFromRequest($option.'campaigns.filter_order','filter_order','created','cmd');

			$filter_order_Dir=$mainframe->getUserStateFromRequest($option.'campaigns.filter_order_Dir','filter_order_Dir',
			'desc','cmd');
		}
		else
		{
			$filter_order=$mainframe->getUserStateFromRequest($option.'campaigns.filter_order','filter_order',$mainframe->getParams()->get('default_sort_by_option'),'cmd');

			$filter_order_Dir=$mainframe->getUserStateFromRequest($option.'campaigns.filter_order_Dir','filter_order_Dir',$mainframe->getParams()->get('filter_order_Dir'),'cmd');
		}

		if($filter_order)
		{
			$qry1="SHOW COLUMNS FROM #__jg_campaigns";
			$db->setQuery($qry1);
			$exists1=$db->loadobjectlist();
			foreach($exists1 as $key1=>$value1)
			{
				$allowed_fields[]=$value1->Field;
			}
			if(in_array($filter_order,$allowed_fields)){
				$query.=" ORDER BY c.$filter_order $filter_order_Dir";
			}
		}

		return $query;
	}

	//F
	function _buildContentWhere()
	{
		global $mainframe,$option;
		$mainframe=JFactory::getApplication();
 		$option=JFactory::getApplication()->input->get('option');
 		$layout=JFactory::getApplication()->input->get('layout','all');

		$db=JFactory::getDBO();
		$user=JFactory::getUser();

		if($mainframe->isAdmin())
		{
			$filter_campaign_cat=$mainframe->getUserStateFromRequest('com_jgive.filter_campaign_cat','filter_campaign_cat','','INT');
		}
		else
		{
			$filter_campaign_cat=$mainframe->getUserStateFromRequest('com_jgive.filter_campaign_cat','filter_campaign_cat',$mainframe->getParams()->get('defualtCatid'),'INT');
		}

		$where=array();
		//Added by SNeha
		$where[]=' c.creator_id = u.id ';
		if(!empty($filter_campaign_cat))
		{

			if(JVERSION>=3.0)
			{

				if (is_numeric($filter_campaign_cat))
				{
					$cat_tbl = JTable::getInstance('Category', 'JTable');
					$cat_tbl->load($filter_campaign_cat);
					$rgt = $cat_tbl->rgt;
					$lft = $cat_tbl->lft;
					$baselevel = (int) $cat_tbl->level;
					$where[]='cat.lft >= ' . (int) $lft;
					$where[]='cat.rgt <= ' . (int) $rgt;
				}

			}
			else
			{

				// Filter by a single or group of categories
				if (is_numeric($filter_campaign_cat))
				{
					$type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

					// Add subcategory check
					$categoryEquals = 'cat.catid '.$type.(int) $filter_campaign_cat;

					$levels = (int) $this->getState('filter.max_category_levels', '1');
					// Create a subquery for the subcategory list
					$subQuery = $db->getQuery(true);
					$subQuery->select('sub.id');
					$subQuery->from('#__categories as sub');
					$subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
					$subQuery->where('this.id = '.(int) $filter_campaign_cat);
					if ($levels >= 0)
					{
						$subQuery->where('sub.level <= this.level + '.$levels);
					}
					$db->setQuery($subQuery);
					$result=$db->loadColumn();
					if($result)
					{
						$result=implode(',',$result);
						$where[]= ' c.category_id IN ('.$result.','.$filter_campaign_cat.')';
					}
					else
					{
						$where[]= ' c.category_id = '.$filter_campaign_cat;
					}
					//$where[]= ' c.category_id IN ('.$result.'))';

						// Add the subquery to the main query

				}
			}

		}
		$filter_campaign_type=$mainframe->getUserStateFromRequest($option.'filter_campaign_type','filter_campaign_type','','string');
		if(!empty($filter_campaign_type))
		{
			$where[]=" c.type='$filter_campaign_type'";
		}

		//Added by Sneha
		// Add text filter on all campaigns
 		$option = JRequest::getCmd('option');
		$filter_state = $mainframe->getUserStateFromRequest( $option.'search_list', 'search_list', '', 'string' );
		if($filter_state) {
			$where[] = " ((c.title like '%".$filter_state."%' ) OR (c.short_description like '%".$filter_state."%' ) OR (c.group_name like '%".$filter_state."%' ) OR (c.first_name like '%".$filter_state."%' ) OR (c.last_name like '%".$filter_state."%' ) OR (c. 	address like '%".$filter_state."%' ) )";
		}
		//End added by sneha

		if($layout=='all')
		{
			//show only publisgehed
			$where[]=' c.published=1';

			//show campaigns created by selected user
			$filter_user=$mainframe->getUserStateFromRequest('com_jgive'.'filter_user','filter_user');
			if($filter_user>0)
			{
				$where[]=' c.creator_id='.$filter_user;

			}
			//show campaigns from selected type
			$filter_campaign_type=$mainframe->getUserStateFromRequest('com_jgive'.'filter_campaign_type','filter_campaign_type');
			if($filter_campaign_type)
			{
				$where[]=" c.type='".$filter_campaign_type."'";
			}
			//show campaign for selected country
			$countries_filter=$mainframe->getUserStateFromRequest('com_jgive'.'campaign_countries','campaign_countries');
			if($countries_filter)
			{
				$where[]=" c.country='".$countries_filter."'";
			}
			//show campaign for selected state
			$state_filter=$mainframe->getUserStateFromRequest('com_jgive'.'campaign_states','campaign_states');
			if($state_filter)
			{
				$where[]=" c.state='".$state_filter."'";
			}
			//show campaign for selected city
			$city_filter=$mainframe->getUserStateFromRequest('com_jgive'.'campaign_city','campaign_city');
			if($city_filter)
			{
				$where[]=" c.city='".$city_filter."'";
			}
			//organization_individual_type filter since version 1.5.1
			$filter_org_ind_type=$mainframe->getUserStateFromRequest('com_jgive'.'filter_org_ind_type','filter_org_ind_type');
			if($filter_org_ind_type)
			{
				$where[]=" c.org_ind_type='".$filter_org_ind_type."'";
			}
			//Campaigns to show filter since jGive version 1.6
			$filter_campaigns_to_show=$mainframe->getUserStateFromRequest('com_jgive'.'campaigns_to_show', 'campaigns_to_show', '0');

			if($filter_campaigns_to_show=='featured')
			{
				$where[]=" c.featured=1 ";
			}
			else if(isset($filter_campaigns_to_show) && $filter_campaigns_to_show!='')
			{
				$where[]=" c.success_status=".$filter_campaigns_to_show." ";
			}

		}
		else if($layout=='my')
		{
			$filter_org_ind_type_my=$mainframe->getUserStateFromRequest('com_jgive'.'filter_org_ind_type_my','filter_org_ind_type_my');
			if($filter_org_ind_type_my)
			{
				$where[]=" c.org_ind_type='".$filter_org_ind_type_my."'";
			}
			if($user->id>0){
				$where[]=' c.creator_id='.$user->id;
			}
		}
		else if($layout=='all_list')
		{
			if($user->id>0){
				$filter_campaign_approve=$mainframe->getUserStateFromRequest('com_jgive'.'filter_campaign_approve','filter_campaign_approve','','INT');
				//check the email link to apporove if it is then set pending value for filter
				$approve=JFactory::getApplication()->input->get('approve','','INT');
				if($filter_campaign_approve==1){
					$where[]='c.published=1';
				}
				else if($filter_campaign_approve==2 or $approve ){
					$where[]='c.published=0';
				}
				//organization_individual_type filter since version 1.5.1
				$filter_org_ind_type=$mainframe->getUserStateFromRequest('com_jgive'.'filter_org_ind_type','filter_org_ind_type');
				if($filter_org_ind_type)
				{
					$where[]=" c.org_ind_type='".$filter_org_ind_type."'";
				}
			}
		}

		return $where=(count($where)?' WHERE '. implode(' AND ',$where ):'');
	}

	function getTotal()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_total)) {
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	function getPagination()
	{
		// Load the content if it doesn't already exist
		if (empty($this->_pagination)) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			// Get the pagination request variables
			$limitstart	= JFactory::getApplication()->input->get('limitstart', 0, '', 'int');
			$limit 		= JFactory::getApplication()->input->get('limit', 20, '', 'int');
			$query = $this->_buildQuery();
			$Arows = $this->_getList($query, $limitstart, $limit);
			$this->_data = $Arows;
		}
		return true;
	}

	/////////////////////////////////////////////
	//functions for filters
	/////////////////////////////////////////////
	//loads all options for filter
	//used in all campaigns layout
	function getUserFilterOptions()
	{
		$mainframe=JFactory::getApplication();
		$query="SELECT DISTINCT (c.creator_id) AS id, u.username as name
			FROM `#__jg_campaigns` AS c
			LEFT JOIN `#__users` AS u ON u.id = c.creator_id
			ORDER BY u.username";
		$this->_db->setQuery($query);
		$users= $this->_db->loadObjectList();

		$filter_user=$mainframe->getUserStateFromRequest('com_jgive.filter_user','filter_user');
		$this->setState('filter_user', $filter_user);

		$options=array();
		$options[]=JHtml::_('select.option','',JText::_('COM_JGIVE_SELECT_USER_FILTER'));
		foreach($users AS $user)
		{
			$options[]=JHtml::_('select.option',$user->id,$user->name);
		}
     	return $options;
	}

	function getCampaignTypeFilterOptions()
	{
		$mainframe=JFactory::getApplication();
		$filter_campaign_type=$mainframe->getUserStateFromRequest('com_jgive.filter_campaign_type','filter_campaign_type');
		$this->setState('filter_campaign_type', $filter_campaign_type);
		$apps=JFactory::getApplication();
		$options=array();

		if($apps->issite() OR JVERSION<3.0)
		{
			$options[]=JHtml::_('select.option','',JText::_('COM_JGIVE_FILTER_SELECT_TYPE'));
		}
		$options[]=JHtml::_('select.option','donation',JText::_('COM_JGIVE_CAMPAIGN_TYPE_DONATION'));
		$options[]=JHtml::_('select.option','investment',JText::_('COM_JGIVE_CAMPAIGN_TYPE_INVESTMENT'));
     	return $options;
	}

	function getOrderingOptions()
	{
		$mainframe=JFactory::getApplication();		//$filter_campaign_type=$mainframe->getUserStateFromRequest('com_jgive.filter_campaign_type','filter_campaign_type');

		if($mainframe->isAdmin())
			$filter_order=$mainframe->getUserStateFromRequest('com_jgive.filter_order','filter_order','created','string');
		else
			$filter_order=$mainframe->getUserStateFromRequest('com_jgive.filter_order','filter_order',$mainframe->getParams()->get('default_sort_by_option'),'string');

		$this->setState('filter_order', $filter_order);

		/*Added by Sneha*/
		//get the data to idetify which field to show on donation view
		$params=JComponentHelper::getParams('com_jgive');
		$show_selected_fields=$params->get('show_selected_fields');
		$creatorfield=array();
		$show_field=0;
		$goal_amount=0;

		if ($show_selected_fields)
		{
			$creatorfield=$params->get('creatorfield');

			if(isset($creatorfield))
			foreach($creatorfield as $tmp)
			{
				switch($tmp)
				{
					case 'goal_amount':
						$goal_amount=1;
					break;
				}
			}
		}
		else
		{
			$show_field=1;
		}
		/*End Added by SNeha*/
		$options=array();
		$options[]=JHtml::_('select.option','',JText::_('COM_JGIVE_FILTER_SELECT_OREDERING'));
		//$options[]=JHtml::_('select.option','id',JText::_('id'));
		$options[]=JHtml::_('select.option','title',JText::_('COM_JGIVE_TITLE'));
		$options[]=JHtml::_('select.option','created',JText::_('COM_JGIVE_CREATED'));
		$options[]=JHtml::_('select.option','modified',JText::_('COM_JGIVE_MODIFIED'));
		$options[]=JHtml::_('select.option','start_date',JText::_('COM_JGIVE_START_DATE'));
		$options[]=JHtml::_('select.option','end_date',JText::_('COM_JGIVE_END_DATE'));
/*Condition Added by Sneha, if hide goal amount conditionally*/
		if($show_field==1 OR $goal_amount==0 )
			$options[]=JHtml::_('select.option','goal_amount',JText::_('COM_JGIVE_GOAL_AMOUNT'));
		$options[]=JHtml::_('select.option','amount_received',JText::_('COM_JGIVE_AMOUNT_RECEIVED'));
		$options[]=JHtml::_('select.option','remaining_amount',JText::_('COM_JGIVE_REMAINING_AMOUNT'));
		$options[]=JHtml::_('select.option','donor_count',JText::_('COM_JGIVE_TOTAL_DONORS_INVESTORS'));

     	return $options;
	}

	function getOrderingDirectionOptions()
	{
		$mainframe=JFactory::getApplication();
		if($mainframe->isAdmin())
			$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jgive.filter_order_Dir','filter_order_Dir','desc','string');
		else
			$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jgive.filter_order_Dir','filter_order_Dir',$mainframe->getParams()->get('filter_order_Dir'),'string');

		$this->setState('filter_order_Dir', $filter_order_Dir);
		$options=array();
		$options[]=JHtml::_('select.option','',JText::_('COM_JGIVE_FILTER_SELECT_OREDERING_DIRECTION'));
		$options[]=JHtml::_('select.option','asc',JText::_('COM_JGIVE_ASCENDING'));
		$options[]=JHtml::_('select.option','desc',JText::_('COM_JGIVE_DESCENDING'));
     	return $options;
	}

	function setItemState($items,$state)
	{
		$campaignHelper=new campaignHelper();
		$db=JFactory::getDBO();
		if(is_array($items))
		{
			foreach($items as $id)
			{
				$db=JFactory::getDBO();
				$query="UPDATE #__jg_campaigns SET published=".$state." WHERE id=".$id;
				$db->setQuery( $query );
				if (!$db->execute()) {
				  $this->setError( $this->_db->getErrorMsg() );
				  return false;
				}
			}
			// get data to for email
			$ids=implode(',',$items);

			//get creator ids
			$query="SELECT camp.creator_id
					FROM #__jg_campaigns as camp
					WHERE camp.id IN(".$ids.") GROUP BY camp.creator_id";

			$db->setQuery($query);
			$creator_ids=$db->loadColumn();
			//echo "<pre>";print_r($creator_ids);echo"</pre>";

			// get campaign infor for each creator
			$camp_info =array();
			$i=0;
			foreach($creator_ids as $creator)
			{
					$query="SELECT camp.id,camp.title,u.email
							FROM #__jg_campaigns as camp
							LEFT JOIN #__users as u ON camp.creator_id=u.id
							WHERE camp.id IN(".$ids.") AND camp.creator_id=".$creator;

					$db->setQuery($query);
					$camp_info[$i++]=$db->LoadObjectList();
			}
		}
		//call function to send email admins/promoters to inform campaign is approved / reject
		$campaignHelper->sendemailCampaignApprovedReject($camp_info,$state);
		return true;
	}
	function delete_campaigns($camp_id)
	{
		$campaignHelper=new campaignHelper();
		$db=JFactory::getDBO();

		// camp creator info to send notification email
		if(is_array($camp_id))
		{
			// get data to for email
			$ids=implode(',',$camp_id);

			//get creator ids
			$query="SELECT camp.creator_id
					FROM #__jg_campaigns as camp
					WHERE camp.id IN(".$ids.") GROUP BY camp.creator_id";

			$db->setQuery($query);
			$creator_ids=$db->loadColumn();
			//echo "<pre>";print_r($creator_ids);echo"</pre>";

			// get campaign infor for each creator
			$camp_info =array();
			$i=0;
			foreach($creator_ids as $creator)
			{
					$query="SELECT camp.id,camp.title,u.email
							FROM #__jg_campaigns as camp
							LEFT JOIN #__users as u ON camp.creator_id=u.id
							WHERE camp.id IN(".$ids.") AND camp.creator_id=".$creator;

					$db->setQuery($query);
					$camp_info[$i++]=$db->LoadObjectList();
			}

		// end of camp creator function

			// campaign deletion function start

			//get Image Directory
			$dir = '../images/jGive/';
			foreach($camp_id as $id)
			{
				// get the Image Path
				$query="Select path FROM #__jg_campaigns_images where campaign_id=$id";
				$db->setQuery($query);
				$result=$db->loadResult();

				// get the image name & delete image
				$file=str_replace('images/jGive/','',$result);
				if(file_exists($dir.$file)){
					if(!@unlink($dir.$file)){
					}
				}

				$query="Delete from #__jg_campaigns_images where campaign_id=$id";
				$db->setQuery($query);
				if(!$db->execute()){
					$this->setError($this->_db->getErrorMsg());
				}

				// Delete give details
				$query="Delete from #__jg_campaigns_givebacks where campaign_id=$id";
				$db->setQuery($query);
				if(!$db->execute()){
					$this->setError($this->_db->getErrorMsg());
				}

				// delete the donors details
				$query="Delete from #__jg_donors where campaign_id=$id";
				$db->setQuery($query);
				if(!$db->execute()){
					$this->setError($this->_db->getErrorMsg());
				}

				// delete the order details
				$query="Delete from #__jg_orders where campaign_id=$id";
				$db->setQuery($query);
				if(!$db->execute()){
					$this->setError($this->_db->getErrorMsg());
				}

				//delete campaigns
				$query="Delete from #__jg_campaigns where id=$id";
				$db->setQuery($query);
				if(!$db->execute()){
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}
		//call function to send email admins/promoters to inform campaign is rejected
		$campaignHelper->sendemailCampaignApprovedReject($camp_info,2);

		//Trigger after delete campaign
		$dispatcher	= JDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onAfterCampaignDelete', array ($camp_id));
		JPluginHelper::importPlugin('system');
		$dispatcher->trigger('OnAfterJGiveCampaignDelete', array ($camp_id));
		//END plugin triggers

		return true;
	}
	function setFeatureUnfreature($items,$state)
	{
		$db=JFactory::getDBO();
		if(is_array($items))
		{
	   		foreach($items as $id)
			{
				$db=JFactory::getDBO();
				$query="UPDATE #__jg_campaigns SET featured=".$state." WHERE id=".$id;
				$db->setQuery( $query );
				if (!$db->execute()) {
				  $this->setError( $this->_db->getErrorMsg() );
				  return false;
				}
			}
		}
		return true;
	}
	// function to get campaigns approved & to approve
	function getCampaignApproveFilterOptions()
	{
		$mainframe=JFactory::getApplication();
		$filter_campaign_approve=$mainframe->getUserStateFromRequest('com_jgive.filter_campaign_approve','filter_campaign_approve');
		$options=array();
		if(JVERSION<3.0)
			$options[]=JHtml::_('select.option','-1',JText::_('COM_JGIVE_FILTER_APPROVED'));

		$options[]=JHtml::_('select.option','1',JText::_('COM_JGIVE_CAMPAIGN_APPROVED'));
		$options[]=JHtml::_('select.option','2',JText::_('COM_JGIVE_CAMPAIGN_PENDING'));
     return $options;
	}
	//function to get campaign countries
	function getcountries()
	{
		$db=JFactory::getDBO();
		$query="SELECT country FROM #__jg_campaigns
		GROUP BY country";
		$db->setQuery($query);
		return $db->loadColumn();
	}
	// function to get campaign states
	function getCampaignStates()
	{
		$db=JFactory::getDBO();
		$mainframe=JFactory::getApplication();
		// get country to generate state
		$campaign_countries=$mainframe->getUserStateFromRequest('com_jgive'.'campaign_countries','campaign_countries');
		if($campaign_countries)
		{
			$query="SELECT state FROM #__jg_campaigns
			WHERE country='".$campaign_countries."'
			GROUP BY state";
			$db->setquery($query);
			return $db->loadColumn();
		}
	}
	//function get city
	function getCampaignCity()
	{
		$db=JFactory::getDBO();
		$mainframe=JFactory::getApplication();
		// get country to generate state
		$campaign_states_filter=$mainframe->getUserStateFromRequest('com_jgive'.'campaign_states','campaign_states');
		if($campaign_states_filter)
		{
			$query="SELECT city FROM #__jg_campaigns
			WHERE state='".$campaign_states_filter."'
			GROUP BY city";
			$db->setquery($query);
			return $db->loadColumn();
		}
	}

	// manoj - added for bill
	function changeSuccessState($cid, $successStatus)
	{
		$campaignHelper = new campaignHelper();
		$result = $campaignHelper->updateCampaignSuccessStatus($cid, $successStatus, $orderId=0);
		if($result)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>
