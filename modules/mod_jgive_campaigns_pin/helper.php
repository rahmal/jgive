<?php
/**
* @package		JJ Module Generator
* @author		JoomJunk
* @copyright	Copyright (C) 2011 - 2012 JoomJunk. All Rights Reserved
* @license		http://www.gnu.org/licenses/gpl-3.0.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//load campaign helper
$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'campaign.php';
if(!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath );
	JLoader::load('campaignHelper');
}
$campaignHelper=new campaignHelper();

class modJGiveLayoutHelper
{
	function  getFeaturedCampaigns($no_of_camp_show,$orderby,$orderby_dir,$campaigns_to_show)
	{
		if($no_of_camp_show)
			$LIMIT="LIMIT 0,".$no_of_camp_show ;

		if ($campaigns_to_show == 'featured')
		{
			$where= "c.featured = 1";
		}
		else
		{
			$where= "c.success_status = '".$campaigns_to_show."'";
		}

		$db=JFactory::getDBO();
		$query="SELECT c.id, c.title, c.goal_amount,c.short_description, c.allow_exceed, c.start_date, c.end_date, c.max_donors,c.country,c.state,c.city
		FROM #__jg_campaigns AS c
		WHERE c.published=1 AND $where  ORDER BY c.$orderby $orderby_dir $LIMIT";
		$db->setQuery($query);
		$data=$db->loadObjectList();

		$cdata=$this->_getCampaignsOtherDetails($data);
		return $cdata=$this->_isActive($cdata);
	}


	//get campaigns amount_received  remaining_amount images

	function _getCampaignsOtherDetails($data)
	{
		$cdata=array();
		foreach($data as $d)//modifiy the data
		{
			$campaignHelper=new campaignHelper();
			//get campaign amounts
			$amounts=$campaignHelper->getCampaignAmounts($d->id);
			$d->amount_received=$amounts['amount_received'];
			//$d->amount_received=5000;
			$d->remaining_amount=$amounts['remaining_amount'];
			$d->donor_count=$campaignHelper->getCampaignDonorsCount($d->id);
			$cdata[$d->id]['campaign']=$d;//push modified data in cdata
			//get campaign images
			$cdata[$d->id]['images']=$campaignHelper->getCampaignImages($d->id);
		}
		return $cdata;
	}

	function _isActive($cdata)
	{
		//check if exeeding goal amount is allowed
		//if not check for received amount to decide about hiding donate button
		foreach($cdata as $key)
		{
			$key['campaign']->active=1;
			if($key['campaign']->allow_exceed==0) //Allow exceed No then
			{
				 if($key['campaign']->amount_received>=$key['campaign']->goal_amount){
					$key['campaign']->active=0;
				 }
			}
			if($key['campaign']->max_donors>0)
			{
				 if($key['campaign']->donor_count >= $key['campaign']->max_donors){
					 $key['campaign']->active=0;
				 }
			}
			//if both start date, and end date are present
			$curr_date='';
			if((int)$key['campaign']->start_date && (int)$key['campaign']->end_date) //(int) typecasting is important
			{
				$start_date=JFactory::getDate($key['campaign']->start_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
				$end_date=JFactory::getDate($key['campaign']->end_date)->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
				$curr_date=JFactory::getDate()->Format(JText::_('COM_JGIVE_DATE_FORMAT_JOOMLA3'));
				//if current date is less than start date, don't show donate button
				if($curr_date<$start_date){
					$key['campaign']->active=0;
				}
				//if current date is more than end date, don't show donate button
				if($curr_date>$end_date){
					$key['campaign']->active=0;
					$date_expire=1;
				}
			}
		}
		return $cdata;
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
