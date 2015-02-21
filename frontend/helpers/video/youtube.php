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
class helperVideoYoutube
{
	var $videoId = null;
	var $url = '';
	function getlink($url)
	{
		$this->url=$url;
		$result=$this->getId();
		if(!empty($result))
			return 'https://www.youtube.com/embed/'.$result;
		else
			return ;
	}

	public function getId()
	{
		if($this->videoId){
			return $this->videoId;
		}

		preg_match_all('~
			# Match non-linked youtube URL in the wild. (Rev:20111012)
			https?://         # Required scheme. Either http or https.
			(?:[0-9A-Z-]+\.)? # Optional subdomain.
			(?:               # Group host alternatives.
			  youtu\.be/      # Either youtu.be,
			| youtube\.com    # or youtube.com followed by
			  \S*             # Allow anything up to VIDEO_ID,
			  [^\w\-\s;]       # but char before ID is non-ID char.
			)                 # End host alternatives.
			([\w\-]{11})      # $1: VIDEO_ID is exactly 11 chars.
			(?=[^\w\-]|$)     # Assert next char is non-ID or EOS.
			(?!               # Assert URL is not pre-linked.
			  [?=&+%\w]*      # Allow URL (query) remainder.
			  (?:             # Group pre-linked alternatives.
				[\'"][^<>]*>  # Either inside a start tag,
			  | </a>          # or inside <a> element text contents.
			  )               # End recognized pre-linked alts.
			)                 # End negative lookahead assertion.
			[?=&+%\w]*        # Consume any URL (query) remainder.
			~ix',
			$this->url, $matches);

		if( isset($matches) && !empty($matches[1]) ){
			return $matches[1][0];
		}
		return false;
	}
}
