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
jimport( 'joomla.database.database' );
jimport('joomla.application.component.helper');
class jgiveFrontendHelper
{
	function getItemId($link,$skipIfNoMenu=0)
    {
        $itemid=0;
        $mainframe=JFactory::getApplication();
        if($mainframe->issite())
        {
			$JSite=new JSite();
			$menu=$JSite->getMenu();
			$items=$menu->getItems('link',$link);
			if(isset($items[0])){
				$itemid=$items[0]->id;
			}
		}
		if(!$itemid)
		{
			$db=JFactory::getDBO();
			if(JVERSION>=3.0)
			{
				$query="SELECT id FROM #__menu
				WHERE link LIKE '%".$link."%'
				AND published =1
				LIMIT 1";
			}
			else
			{
				$query="SELECT id FROM ".$db->nameQuote('#__menu')."
				WHERE link LIKE '%".$link."%'
				AND published =1
				ORDER BY ordering
				LIMIT 1";
			}
			$db->setQuery($query);
			$itemid=$db->loadResult();
		}
		if(!$itemid)
		{
			if($skipIfNoMenu)
				$itemid=0;
			else
				$itemid=JRequest::getInt('Itemid',0);
		}
		return $itemid;
    }

    //loads country list
	function getCountries()
	{
		$db=JFactory::getDBO();
		$query="SELECT country_id, country
		FROM #__tj_country
		ORDER BY country";
		$db->setQuery($query);
		$rows=$db->loadAssocList();
		return $rows;
	}

	//loads states for given country
	function getState($country)
	{
		if(!$country)
			return;

		$db=JFactory::getDBO();
		$query="SELECT r.region_code, r.region
		FROM #__tj_region AS r
		LEFT JOIN #__tj_country AS c
		ON r.country_code=c.country_code
		WHERE c.country_id=".$country."
		ORDER BY r.region";
		$db->setQuery($query);
		$rows=$db->loadAssocList();
		return $rows;
	}
	//loads cities for given country
	function getCity($country)
	{
		if(!$country)
			return;

		$db=JFactory::getDBO();
		$query="SELECT c.city_id, c.city
		FROM #__tj_city AS c
		LEFT JOIN #__tj_country AS con
		ON c.country_code=con.country_code
		WHERE con.country_id=".$country."
		ORDER BY c.city";
		$db->setQuery($query);
		$rows=$db->loadAssocList();
		return $rows;
	}
	//loads country name from country id
	//used when saving campaign
	function getCountryNameFromId($country_id)
	{
		if(!$country_id)
			return;

		$db=JFactory::getDBO();
		$query='SELECT country
		FROM #__tj_country
		WHERE country_id='.$country_id;
		$db->setQuery($query);
		return $db->loadResult();
	}

	//loads country id from country name
	//used when showing campaign details
	function getCountryIdFromName($country_name)
	{

		if(!$country_name)
			return;

		$db=JFactory::getDBO();
		$query="SELECT country_id
		FROM `#__tj_country`
		WHERE country='".$country_name."'";
		$db->setQuery($query);
		return $db->loadResult();
	}

	//loads region name from region id, country id
	//used when saving campaign
	function getRegionNameFromId($region_id,$country_id)
	{

		if(!$region_id)
			return;

		if(!$country_id)
			return;

		$db=JFactory::getDBO();
		$query="SELECT r.region
		FROM #__tj_region AS r
		LEFT JOIN #__tj_country AS c ON r.country_code=c.country_code
		WHERE c.country_id=".$country_id."
		AND r.region_code='".$region_id."'";
		$db->setQuery($query);
		return $db->loadResult();
	}

	//loads region name from region id, country id
	//used when saving campaign
	function getCityNameFromId($city_id,$country_id)
	{
		if(!$city_id)
			return;

		if(!$country_id)
			return;


		$db=JFactory::getDBO();
		$query="SELECT c.city
		FROM #__tj_city AS c
		LEFT JOIN #__tj_country AS con ON c.country_code=con.country_code
		WHERE con.country_id=".$country_id."
		AND c.city_id=".$city_id;
		$db->setQuery($query);
		return $db->loadResult();
	}

	//to sort the column which are not in table

	function multi_d_sort($array,$column,$order)
    {
		if(isset($array) && count($array))
		{
			foreach($array as $key=>$row)
			{
				//$orderby[$key]=$row['campaign']->$column;
				$orderby[$key]=$row->$column;
			}
			if($order=='asc')
			{
				array_multisort($orderby,SORT_ASC,$array);
			}
			else
			{
				array_multisort($orderby,SORT_DESC,$array);
			}
		}
        return $array;
    }

    function DisplayjlikeButton($ad_url,$id,$title)
    {
		$jlikeparams=array();
		$jlikeparams['url']=$ad_url;
		$jlikeparams['campaignid']=$id;
		$jlikeparams['title']=$title;
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('content','jlike_jgive');
		$grt_response=$dispatcher->trigger('onBeforeDisplaylike',array('com_jgive.compaign',$jlikeparams));
		if(!empty($grt_response['0']))
		return $grt_response['0'];
		else
		return '';
	}
	//push to activity stream
	function pushtoactivitystream($contentdata)
	{

		$actor_id=$contentdata['user_id'];
		$integration_option=$contentdata['integration_option'];
		$act_access=0;
		$act_description=$contentdata['act_description'];
		$act_type='';
		$act_subtype='';
		$act_link='';
		$act_title='';
		$act_access=0;
		//$act_subtype="Invited ".$invitee_count." people to the site using XXX tool.";

				$activityintegrationstream=new activityintegrationstream();
				$result=$activityintegrationstream->pushActivity($actor_id,$act_type,$act_subtype,$act_description,$act_link,$act_title,$act_access,$integration_option);
				if(!$result){
					return false;
				}
				return true;
	}
	/**
	 * @param price int
	 * @return formatted price-currency string
	 * */
	function getFromattedPrice($price,$curr=NULL)
	{
		$price=number_format($price,2);
		$curr_sym=$this->getCurrencySymbol();
		$params = JComponentHelper::getParams( 'com_jgive' );
		$currency =$params->get('currency' );
		$currency_display_format = $params->get('currency_display_format' );
		$currency_display_formatstr='';
		$currency_display_formatstr=str_replace('{AMOUNT}',"&nbsp;".$price,$currency_display_format);
		$currency_display_formatstr=str_replace('{CURRENCY_SYMBOL}',"&nbsp;".$curr_sym,$currency_display_formatstr);
		$currency_display_formatstr=str_replace('{CURRENCY}',"&nbsp;".$currency,$currency_display_formatstr);
		$html='';
		$html="<span>".$currency_display_formatstr." </span>";
		return $html;
	}
	function getCurrencySymbol($currency='')
	{
		$params = JComponentHelper::getParams( 'com_jgive' );
		$curr_sym = $params->get('currency_symbol' );

		if(empty($curr_sym))
		{
			$curr_sym =$params->get('currency' );
		}
		return $curr_sym;
	}

	/**
	 * Get jomsocial toobar html
	 */
	function jomsocailToolbarHtml()
	{
		$params=JComponentHelper::getParams('com_jgive');
		$html='';
		if(($params->get('integration')=='jomsocial' ) && $params->get('jomsocial_toolbar'))
		{
			// Added for JS toolbar inclusion.
			if (JFolder::exists(JPATH_SITE . DS . 'components' . DS . 'com_community') )
			{
				require_once(JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'toolbar.php');
				$toolbar = CFactory::getToolbar();
				$tool = CToolbarLibrary::getInstance();

				$html.='<div id="community-wrap">';
					$html.=$tool->getHTML();
				$html.='</div>';

			}
			// EOC - Added for JS toolbar inclusion.
		}
		return $html;
	}

	/** vm:checks for view override
	@parms $viewname :: (string) name of view
	$searchTmpPath ::(string) it may be admin or site. it is side(admin/site) where to search override view
	$useViewpath ::(string) it may be admin or site. it is side(admin/site) which VIEW shuld be use IF OVERRIDE IS NOT FOUND
	$layout:: (string) layout name eg order
	@return :: if exit override view then return path
	*/
	public function getViewpath($viewname,$layout="",$searchTmpPath='SITE',$useViewpath='SITE')
	{
		  $searchTmpPath = ($searchTmpPath=='SITE')?JPATH_SITE:JPATH_ADMINISTRATOR;
		  $useViewpath = ($useViewpath=='SITE')?JPATH_SITE:JPATH_ADMINISTRATOR;
		  $app = JFactory::getApplication();

		  if(!empty($layout))
		  {
				  $layoutname=$layout.'.php';
		  }
		  else
		  {
				  $layoutname="default.php";
		  }
		  $override = $searchTmpPath.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_jgive'.DS.$viewname.DS.$layoutname;

		  if(JFile::exists($override) )
		  {
				  return $view = $override;
		  }
		  else
		  {
				  return $view=$useViewpath.DS.'components'.DS.'com_jgive'.DS.'views'.DS.$viewname.DS.'tmpl'.DS.$layoutname;
		  }
	}
      // end of getViewpath()


}
?>
