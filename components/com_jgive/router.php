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
// defibe DIRECTORY_SEPARATOR if not mostly for joomla 3.0
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}
$helperPath=JPATH_SITE.DS.'components'.DS.'com_jgive'.DS.'helpers'.DS.'campaign.php';
if(!class_exists('campaignHelper'))
{
	JLoader::register('campaignHelper', $helperPath );
	JLoader::load('campaignHelper');
}

function jgiveBuildRoute(&$query)
{

	$params = JComponentHelper::getParams('com_jgive');
	$new_sef = $params->get('new_sef');

	$segments = array();
	/*echo "<pre>";
	print_r($query);
	echo "</pre>";*/

	/*if($new_sef)
	{
		if(isset($query['view']))
		{
				if($query['view'] == 'campaign')/*for CAMPAIGN*/
				/*{
					if(isset($query['cid']))
					{

						$campaignHelper = new campaignHelper();

						$camp_details	=	$campaignHelper->getCampaignDetails($query['cid']);
						$segments[]	=	$campaignHelper->getCatalias($camp_details->category_id);

						$ctitle	=	$campaignHelper->getCampaignTitleFromCid($query['cid']);
						$string = preg_replace('/\s+/', '', $ctitle);
						$segments[] = $query['cid'].':'.strtolower($string);

						if($query['layout'] =='single')
							$segments[] = 'details';

						if($query['layout'] =='create')
							$segments[] = 'edit';

						unset( $query['cid'] );
					}
				}
				elseif($query['view'] == 'campaigns')/*for CAMPAIGNS*/
				/*{
					if(isset($query['filter_campaign_cat']))
					{

						if(!empty($query['filter_campaign_cat']))
						{
							$campaignHelper = new campaignHelper();
							$alias      = $campaignHelper->getCatalias($query['filter_campaign_cat']) ;
							$segments[] = $alias;
							unset( $query['filter_campaign_cat'] );
						}

					}
					if(isset($query['campaigns_to_show']))
					{
						$segments[] = 'search';
						$camps='';
						switch($query['campaigns_to_show'])
						{
							case '0' :
								$camps="ongoing";
							break;
							case '-1' :
								$camps = "failed";
							break;
							case '1' :
								$camps="Successful";
							break;
						}

						$segments[] = $query['campaigns_to_show'] .":" .$camps.'';
						unset( $query['campaigns_to_show'] );
					}
				}
				else
				{
					$segments[] = $query['view'];
					$segments[] = $query['layout'];
				}
				unset( $query['view'] );
				unset( $query['layout'] );
		}


		if(isset($query['donationid']))
		{
			$segments[] = $query['donationid'];
			unset( $query['donationid'] );

		}

	}
	else*/
	{
		if(isset($query['view']))
		{
				$segments[] = $query['view'];
				unset( $query['view'] );
		}
		if(isset($query['cid']))
		{
				$segments[] = $query['cid'];
				unset( $query['cid'] );
		};
		if(isset($query['donationid']))
		{
			$segments[] = $query['donationid'];
			unset( $query['donationid'] );

		}
	}

	return $segments;
}

function jgiveParseRoute($segments)
{
	/*echo "<pre>";
	print_r($segments);
	echo "</pre>";die;
*/
	$vars = array();

	$params = JComponentHelper::getParams('com_jgive');
	$new_sef = $params->get('new_sef');

	/*if($new_sef)
	{
		$count	=	count($segments);
		if ( ! empty($count))
		{
			/*if($segments[0] == 'donations')/*campaign*/
			/*{
				$vars['view'] = 'donations';
				if(isset($segments[1]))
				{
					//$id = explode( ':', $segments[1] );
					if(isset($segments[2]))
					{
						$vars['donationid'] = (int) $segments[2];
					}
					$vars['layout'] = $segments[1];

				}
			}
			else
			{
				if(isset($segments[2]))
				{
					if($segments[2] == 'details' || $segments[2] == 'edit')/*campaign*/
					/*{
						$vars['view'] =	'campaign';
						if($segments[2] == 'details')
							$vars['layout']	=	'single';
						else
						{
							$vars['layout']	=	'create';
						}
						$camp_det_arr	=	explode(':',$segments[1]);

						$vars['cid']	=	$camp_det_arr[0];

					}
				}
				else
				{

					$vars['view'] =	'campaigns';
					$vars['layout'] = 'all' ;

					if(isset($segments[0])){
						if($segments[0]== 'search')
						{
							$quickSearachArray=explode(":",$segments[1]);
							if($quickSearachArray[1]=='1-failed')
							{
								$vars['campaigns_to_show'] ='-1';
							}
							else
							{
								$vars['campaigns_to_show'] =$quickSearachArray[0];
							}
						}
						else {

							/*filter bt categories*/
							/*$campaignHelper = new campaignHelper();
							$cat_alias	=	str_replace( array( ':', '_' ), array( '-', '.' ), $segments[0] );

							$cat_id      = $campaignHelper->getCatidbyalias($cat_alias) ;
							$vars['filter_campaign_cat'] =	 $cat_id;
						}
					}
				}
			}
		}
	}
	else*/
	{
		switch($segments[0])
		{
			   case 'campaign':
					$vars['view'] = 'campaign';
					if(isset($segments[1]))
					{
						$id = explode( ':', $segments[1] );
						$vars['cid'] = (int) $id[0];
					}
				break;

				case 'donations':
					$vars['view'] = 'donations';
					if(isset($segments[1]))
					{
						$id = explode( ':', $segments[1] );
						$vars['donationid'] = (int) $id[0];
					}
				break;

				case 'campaigns':
					$vars['view'] = 'campaigns';
				break;
		}
	}

	return $vars;
}
?>
