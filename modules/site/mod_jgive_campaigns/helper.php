<?php
/**
* @package		JJ Module Generator
* @author		JoomJunk
* @copyright	Copyright (C) 2011 - 2012 JoomJunk. All Rights Reserved
* @license		http://www.gnu.org/licenses/gpl-3.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class modJGiveHelper
{
	function getData($featured_camp='',$group_page,$groupid)
	{
		//module js group 
		$for_group_camp=" ";
		if(($group_page==1) && (!empty($groupid)))
		{
			$for_group_camp=" AND c.js_groupid= ".$groupid." ";
		}
		$featured_camp ? $featured_camp=" WHERE c.featured = 1 AND c.published=1 AND i.gallery_image=0 ".$for_group_camp :$featured_camp=" WHERE c.published=1  AND i.gallery_image=0 ".$for_group_camp ;
		$db=JFactory::getDBO();
		$query="SELECT c.max_donors,COUNT(c.id) AS total,c.id,o.status, c.title,c.goal_amount,c.start_date,c.end_date,c.allow_exceed,c.type , SUM(o.amount) AS amount_received 
				FROM #__jg_campaigns as c 
				LEFT JOIN #__jg_orders as o ON o.campaign_id=c.id 
				LEFT JOIN #__jg_campaigns_images as i ON i.campaign_id=c.id 
				$featured_camp
				GROUP BY c.id	";		
		$db->setQuery($query);
		$goal_amount=$db->loadobjectlist();
		return $goal_amount;
	}
	
	function getCampaignAmounts($cid,$image)
	{
		$db=JFactory::getDBO();
		$amounts=array();
		if($image)
		{
			$query="SELECT i.path
			FROM #__jg_campaigns_images as i
			WHERE i.campaign_id=".$cid." AND i.gallery_image=0 ";			
			$db->setQuery($query);
			$amounts['path']=$db->loadResult();
			
		}
		else
		{
			$amounts['path']=0;
		}
		
		$query="SELECT c.max_donors,COUNT(c.id) AS total,c.id,c.goal_amount,c.type,c.allow_exceed
		FROM `#__jg_campaigns` AS c
		WHERE c.id=".$cid;				
		$db->setQuery($query);
		$data=$db->loadObjectList();	
			
		$query="SELECT SUM(o.amount) AS amount_received
		FROM `#__jg_orders` AS o
		WHERE o.campaign_id=".$cid."
		AND o.status='C'";
		$db->setQuery($query);
		
	
		//if no donations, set receved amount as zero
			
		//calculate remaining amount
		$amounts['goal_amount']=$data[0]->goal_amount;
		$amounts['id']=$data[0]->id;
		$amounts['type']=$data[0]->type;
		$amounts['allow_exceed']=$data[0]->allow_exceed;
		$amounts['total']=$data[0]->total;
		$amounts['max_donors']=$data[0]->max_donors;
		return $amounts;
	}
	
	////CORRECT QUERY.......................
	function getwhere($orderby='',$orderby_dir='',$count='',$image='',$featured_camp='',$group_page,$groupid)
	{
		//module js group 
		$for_group_camp=" ";
		if(($group_page==1) && (!empty($groupid)))
		{
			$for_group_camp=" AND c.js_groupid= ".$groupid." ";
		}
		$featured_camp ? $featured_camp=" WHERE c.featured = 1 AND c.published=1 AND i.gallery_image=0 ".$for_group_camp :$featured_camp=" WHERE c.published=1  AND i.gallery_image=0 ".$for_group_camp ;
		$select=',';
		if($image){
			$select=",i.path,";
		}
		$query="SELECT c.max_donors,COUNT(c.id) AS total,c.id,o.status, c.title,c.goal_amount,c.start_date,c.end_date,c.allow_exceed,c.type $select SUM(o.amount) AS amount_received 
				FROM #__jg_campaigns as c 
				LEFT JOIN #__jg_orders as o ON o.campaign_id=c.id 
				LEFT JOIN #__jg_campaigns_images as i ON i.campaign_id=c.id 
				$featured_camp
				GROUP BY c.id
				ORDER BY $orderby $orderby_dir LIMIT 0, $count";
		return $query;
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
