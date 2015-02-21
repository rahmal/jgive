<?php
/**
 * @package	Jticketing
 * @copyright Copyright (C) 2012 -2013 Techjoomla, Tekdi Web Solutions . All rights reserved.
 * @license GNU GPLv2 <http://www.gnu.org/licenses/old-licenses/gpl-2.0.html>
 * @link     http://www.techjoomla.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model' );
jimport( 'joomla.database.table.user' );

class jgiveModelReports extends JModelLegacy
{

	var $_data;
 	var $_total = null;
 	var $_pagination = null;

	function __construct()
	{
		parent::__construct();
		global $mainframe, $option;
		$mainframe = JFactory::getApplication();
		$option = JFactory::getApplication()->input->get('option');
		// Get pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = JFactory::getApplication()->input->get('limitstart', 0, '', 'int');
		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
  }

	////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////

	//B
	////campaignwise donations report - payouts layout
	function getPayouts()
	{
		if(empty($this->_data))
		{
			$query=$this->_buildQuery();
			$this->_data=$this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_data;

	}

	//B=>used in backend
	//B
	//campaignwise donations report - default layout
	function getCampaignWiseDonations()
	{
		if(empty($this->_data))
		{
			$query=$this->_buildQuery();
			$this->_data=$this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		foreach($this->_data as $d)
		{
			//get amount to be excluded from payout
			$reportsHelper=new reportsHelper();
			$d->exclude_amount=$reportsHelper->getTotalAmount2BExcluded($d->cid);
		}

		global $mainframe,$option;
		$mainframe=JFactory::getApplication();
		$filter_type=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','goal_amount','cmd');
		$filter_order_Dir=$mainframe->getUserStateFromRequest('com_jgive.filter_order_Dir','filter_order_Dir','desc','word');

		if($filter_type=='donations_count' || $filter_type=='total_amount' || $filter_type=='total_commission' || $filter_type=='exclude_amount'){
			$jgiveFrontendHelper=new jgiveFrontendHelper();
			$this->_data=$jgiveFrontendHelper->multi_d_sort($this->_data,$filter_type,$filter_order_Dir);
		}

		return $this->_data;
	}

	//B
	function _buildQuery()
	{
		$db=JFactory::getDBO();
		global $mainframe,$option;
		$mainframe=JFactory::getApplication();
		$option=JFactory::getApplication()->input->get('option');
		$layout=JFactory::getApplication()->input->get('layout','default');

		//Get the WHERE and ORDER BY clauses for the query
		$where='';
		$where=$this->_buildContentWhere();

		if($layout=='default')//campaignwise donations report
		{
			$query="SELECT c.title, c.id AS cid, c.creator_id, c.first_name, c.last_name, c.paypal_email, COUNT(o.id) AS donations_count,
			SUM(o.amount) AS total_amount, SUM(o.fee) AS total_commission, u.username
			FROM  `#__jg_orders` AS o
			LEFT JOIN `#__jg_campaigns` AS c ON c.id=o.campaign_id
			LEFT JOIN #__categories as cat ON c.category_id=cat.id
			LEFT JOIN `#__users` AS u ON u.id=c.creator_id ".
			$where . "
			GROUP BY o.campaign_id";
			$filter_order=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','goal_amount','cmd');
			$filter_order_Dir=$mainframe->getUserStateFromRequest($option.'filter_order_Dir','filter_order_Dir','desc','word');

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
		}
		if($layout=='payouts' || $layout=='mypayouts')//payouts report
		{
			$query="SELECT a.id, a.user_id, a.payee_name, a.transaction_id, a.date, a.email_id, a.amount, a.status, u.username
			FROM #__jg_payouts AS a
			LEFT JOIN `#__users` AS u ON u.id=a.user_id
			".$where;
			$filter_order=$mainframe->getUserStateFromRequest($option.'filter_order','filter_order','goal_amount','cmd');
			$filter_order_Dir=$mainframe->getUserStateFromRequest($option.'filter_order_Dir','filter_order_Dir','desc','word');

			if($filter_order)
			{
				$qry1="SHOW COLUMNS FROM #__jg_payouts";
				$db->setQuery($qry1);
				$exists1=$db->loadobjectlist();
				foreach($exists1 as $key1=>$value1)
				{
					$allowed_fields[]=$value1->Field;
				}
				if(in_array($filter_order,$allowed_fields)){
					$query.=" ORDER BY a.$filter_order $filter_order_Dir";
				}
			}
		}

		//echo $query."<br/>";

		return $query;
	}

	//B
	function _buildContentWhere()
	{
		global $mainframe,$option;
		$mainframe=JFactory::getApplication();
 		$option=JFactory::getApplication()->input->get('option');
		$layout=JFactory::getApplication()->input->get('layout','default');

		//$db=JFactory::getDBO();
		$where=array();
		if($layout=='default')//campaignwise donations report
		{
			$filter_campaign=$mainframe->getUserStateFromRequest($option.'filter_campaign','filter_campaign','','string');
			if($filter_campaign!=0){
				$where[]=" c.id=".$filter_campaign;
			}
			$where[]=" o.status='C'";

			$filter_campaign_cat=$mainframe->getUserStateFromRequest('com_jgive.filter_campaign_cat','filter_campaign_cat','','INT');
			if(!empty($filter_campaign_cat))
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
			$filter_campaign_type=$mainframe->getUserStateFromRequest($option.'filter_campaign_type','filter_campaign_type','','string');
			if(!empty($filter_campaign_type))
			{
				$where[]=" c.type='$filter_campaign_type'";
			}

			$filter_org_ind_type_report=$mainframe->getUserStateFromRequest('com_jgive'.'filter_org_ind_type_report','filter_org_ind_type_report');
			if(!empty($filter_org_ind_type_report))
			{
				$where[]=" c.org_ind_type='$filter_org_ind_type_report'";
			}

		}
		if($layout=='payouts'){//payouts report
		}
		if ($layout=='mypayouts'){
			$me=JFactory::getuser();
			$where[]=' a.user_id='.$me->id;
		}

		return $where=(count($where)?' WHERE '. implode(' AND ',$where ):'');
	}

	//B
	function getTotal()
	{
		//Lets load the content if it doesn’t already exist
		if(empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
	}

	//B
	function getPagination()
	{
		// Lets load the content if it doesn’t already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	function getPayoutFormData()
	{
		$query="SELECT c.creator_id, c.first_name, c.last_name, c.paypal_email,
		COUNT(o.id) AS donations_count, SUM(o.amount) AS total_amount, SUM(o.fee) AS total_commission
		FROM  `#__jg_campaigns` AS c
		LEFT JOIN  `#__jg_orders` AS o ON o.campaign_id = c.id
		AND o.status='C'
		AND o.fund_holder=0 ". /*ONLY consider payments which are directly transferred to admin's account*/
		" GROUP BY c.creator_id";

		$this->_db->setQuery($query);

		$payouts=$this->_db->loadObjectList();
		return $payouts;
	}

	function getSinglePayoutData()
	{
		$payout_id=JRequest::getInt('payout_id','');

		$db=JFactory::getDBO();

		$query="SELECT id,user_id,payee_name,transaction_id,date,email_id,amount,status
		FROM #__jg_payouts
		WHERE id=".$payout_id;

		$db->setQuery($query);

		$result = $db->loadObject();

		return $result;
	}

	//saves a new campaign details
	//used for create view
	function savePayout()
	{
		$post=JRequest::get('post');
		//echo "<pre>";	print_r($post);die('save');

		$obj = new stdClass();
		$obj->id='';
		$obj->user_id=$post['user_id'];
		$obj->payee_name=$post['payee_name'];
		$obj->email_id=$post['paypal_email'];
		$obj->transaction_id=$post['transaction_id'];
		$obj->amount=$post['payment_amount'];
		$obj->date=$post['payout_date'];
		$obj->status=$post['status'];

		//insert object
		if(!$this->_db->insertObject( '#__jg_payouts',$obj,'id'))
		{
			echo $this->_db->stderr();
			return false;
		}

		return true;
	}

	//saves a new campaign details
	//used for create view
	function editPayout()
	{
		$post=JRequest::get('post');
		//echo "<pre>";	print_r($post);die('save');

		$obj = new stdClass();
		$obj->id=$post['edit_id'];
		$obj->user_id=$post['user_id'];
		$obj->payee_name=$post['payee_name'];
		$obj->email_id=$post['paypal_email'];
		$obj->transaction_id=$post['transaction_id'];
		$obj->amount=$post['payment_amount'];
		$obj->date=$post['payout_date'];
		$obj->status=$post['status'];

		//insert object
		if(!$this->_db->updateObject('#__jg_payouts',$obj,'id'))
		{
			echo $this->_db->stderr();
			return false;
		}

		return true;
	}
	function deletePayouts($id)
	{
		$payee_id=implode(',',$id);
		$db=JFactory::getDBO();
		$query="delete FROM #__jg_payouts where id IN(".$payee_id.")";

		$db->setQuery($query);
		if(!$db->execute()){
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return true;
	}
	//Added by Sneha, to get result in csv
	function getCsvexportData()	{
		$query = $this->_buildQuery();
		$db=JFactory::getDBO();
		$query = $db->setQuery($query);
		return $data =$db->loadAssocList();
	}

}
