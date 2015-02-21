<?php
/**
 * @package		com_jgive
 * @version		$versionID$
 * @author		TechJoomla
 * @author mail	extensions@techjoomla.com
 * @website		http://techjoomla.com
 * @copyright	Copyright © 2009-2013 TechJoomla. All rights reserved.
 * @license		GNU General Public License version 2, or later
*/
defined('_JEXEC') or die('Restricted access');


/**
 * Class to manipulate data from YouTube
 *
 * @access	public
 */
jimport('joomla.application.component.helper');
class helperVideoVimeo
{
	var $videoId = null;
	var $url = '';
	function getlink($url)
	{
		$this->url=$url;
		$result=$this->getId();
		if(!empty($result)) //http://player.vimeo.com/video/' . $videoId 
		{
			return 'https://player.vimeo.com/video/'.$result;
		}
		return ;
	}

	/**
	 * Extract Vimeo video id from the video url submitted by the user
	 *
	 * @access	public
	 * @param	video url
	 * @returns videoid
	 */
	public function getId()
	{
	    $pattern = '/vimeo.com\/(hd#)?(channels\/[a-zA-Z0-9]*#)?(\d*)/';
	    preg_match($pattern, $this->url, $match);

            if(!empty($match[3]))
            {
                return $match[3];
            }
            else
            {
               return !empty( $match[2] ) ? $match[2] : null;
            }

	}
}
