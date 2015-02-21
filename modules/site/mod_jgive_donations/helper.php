<?php
/**
* @package		JJ Module Generator
* @author		JoomJunk
* @copyright	Copyright (C) 2011 - 2012 JoomJunk. All Rights Reserved
* @license		http://www.gnu.org/licenses/gpl-3.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class modJGiveDonationHelper
{

	function lastDonations($no_of_record_show)
	{
		$where= " ORDER BY i.mdate DESC ";
		return $result=$this->getData($where,$no_of_record_show);
	}

	function topDonations($no_of_record_show)
	{
		$where= " ORDER BY i.original_amount DESC ";
		return $result=$this->getData($where,$no_of_record_show);
	}

	function myDonations($no_of_record_show)
	{
		$userid=JFactory::getUser()->id;
		$where= "AND d.user_id <> 0  AND d.user_id=".$userid;
		return $result=$this->getData($where,$no_of_record_show);
	}

	function getData($where,$no_of_record_show)
	{
		$db=JFactory::getDBO();
		$query="SELECT i.id, i.order_id,i.amount,i.cdate, d.user_id AS donor_id, c.id AS cid, c.title,u.name
				FROM #__jg_orders AS i
				LEFT JOIN #__jg_campaigns AS c ON c.id=i.campaign_id
				LEFT JOIN #__jg_donors AS d on d.id=i.donor_id 
				LEFT JOIN #__users AS u  on d.user_id=u.id
				WHERE i.status='C'  ".$where." LIMIT 0, ".$no_of_record_show.""; //die;
		$db->setquery($query);
		$result=$db->loadobjectlist();
		return $result;
	}

	function multi_d_sort($array,$column,$order,$count)
    {
        foreach ($array as $key=>$row){
			$orderby[$key]=$row->$column; 
        }
    
        if($order=='ASC'){
			array_multisort($orderby,SORT_ASC,$array);
		}
		 else
		{
			if(!empty($array))
            array_multisort($orderby,SORT_DESC,$array);
	    }
       return $array;
    }
	 
}

?>
