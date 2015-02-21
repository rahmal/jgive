<?php
/**
 * @version		1.6 jgive $
 * @package		jgive
 * @copyright	Copyright © 2013 - All rights reserved.
 * @license		GNU/GPL
 * @author		TechJoomla
 * @author mail	extensions@techjoomla.com
 * @website		http://techjoomla.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
// Component Helper
jimport('joomla.application.component.helper');
class reportsHelper
{
	function getTotalAmount2BPaidOut($userid=0)
	{
		$db=JFactory::getDBO();
		$where='';

		$query="SELECT SUM(o.amount) AS total_amount, SUM(o.fee) AS total_commission
		FROM `#__jg_orders` AS o ";
		if($userid){
			$query .=" LEFT JOIN `#__jg_campaigns` AS c ON c.id=o.campaign_id ";
			$where=" AND c.creator_id=".$userid;
		}
		$query .=" WHERE o.status='C'
		AND o.fund_holder=0 ".$where; /*ONLY consider payments which are directly transferred to admin's account*/

		$db->setQuery($query);

		$result=$db->loadObject();
		$TotalAmount2BPaidOut=0;
		if($result){
			$TotalAmount2BPaidOut=$result->total_amount-$result->total_commission;
		}
		return $TotalAmount2BPaidOut;
	}

	function getTotalPaidOutAmount($userid=0)
	{
		$db=JFactory::getDBO();
		$where='';
		if($userid)
			$where=" AND user_id=".$userid;

		$query="SELECT user_id,payee_name,transaction_id,date,email_id,amount
		FROM #__jg_payouts
		WHERE status=1 ".$where;

		$db->setQuery($query);
		$totalearn=0;
		$result = $db->loadObjectlist();
		$totalpaid=0;
		if(!empty($result))
		{
			foreach($result as $data) {
				$totalpaid=$totalpaid+$data->amount;
			}
		}
		return $totalpaid;
	}

	function getTotalAmount2BExcluded($cid)
	{
		$db=JFactory::getDBO();
		if(!empty($cid))
		{
			$query="SELECT SUM(o.amount) AS exclude_amount
			FROM `#__jg_orders` AS o
			WHERE o.status='C'
			AND o.campaign_id=".$cid."
			AND o.fund_holder=1 "; /*ONLY consider payments which are directly transferred to campaign creator's account*/
		
		$db->setQuery($query);

		$exclude_amount=$db->loadResult();
		if($exclude_amount=='')
			$exclude_amount=0;
		return $exclude_amount;
		}
		$exclude_amount=0;
		return $exclude_amount;
	
	}
}
?>
