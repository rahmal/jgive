<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.html.parameter' );
jimport('joomla.filesystem.file');
jimport( 'joomla.filesystem.folder');


class jgivemediaHelper
{
	function __construct()
	{
		$params = JComponentHelper::getParams( 'com_jgive' );
		$this->sa_config['image_size']=$params->get( 'max_size' );
		if(!$this->sa_config['image_size'])
		{
			$this->sa_config['image_size']=9024;
		}
	}

	function geturl($video_provider,$url)
	{
		switch($video_provider)
		{
			case 'youtube':
				require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."video".DS."youtube.php");
				$helperVideoYoutube=new helperVideoYoutube();
				return $result=$helperVideoYoutube->getlink($url);
			break;

			case 'vimeo':
				require_once(JPATH_SITE.DS."components".DS."com_jgive".DS."helpers".DS."video".DS."vimeo.php");
				$helperVideoVimeo=new helperVideoVimeo();
				return $result=$helperVideoVimeo->getlink($url);
			break;
		}
	}

	//check for max media size allowed for upload
	//image upload
	function imageupload($file_field,$img_dimensions_config, $upload_orig='1' )
	{
		$app=JFactory::getApplication();
		//get uploaded media details
		$params=JComponentHelper::getParams('com_jgive');
		$file_name  = $_FILES[$file_field]['name'];//orginal file name
		$file_name  = strtolower($_FILES[$file_field]['name']);//convert name to lowercase
		$file_name  = preg_replace('/\s/', '_', $file_name);//replace "spaces" with "_" in filename
		$file_type  = $_FILES[$file_field]['type'];
		$file_tmp_name=$_FILES[$file_field]['tmp_name'];
		$file_size    = $_FILES[$file_field]['size'];
		$file_error   = $_FILES[$file_field]['error'];

		//set error flag, if any error occurs set this to 1
		$error_flag=0;
		//$socialads_config['image_size'] = 1204;
		//check for max media size allowed for upload
		$max_size_exceed=$this->check_max_size($file_size);

		if ($max_size_exceed)
		{ 
			$max_size = $params->get( 'max_size' );

			if (!$max_size)
			{
				$max_size = 1024;//KB
			}

			$errorList[] = JText::_('FILE_BIG') . " " . $max_size . "KB<br>";
			$app->enqueueMessage(JText::_('COM_JGIVE_MAX_FILE_SIZE').' '.$max_size.'KB<br>','error');
			$error_flag=1;
		}

		if(!$error_flag)
		{
			//detect file type 
			//& detect media group type image/video/flash
			$media_type_group=$this->check_media_type_group($file_type);
			
			if(!$media_type_group['allowed']){
				$errorList[]= JText::_('FILE_ALLOW'); 
				$error_flag=1;
			}

			if(!$error_flag)
			{
				$media_extension=$this->get_media_extension($file_name);

				/// upload original img

				//$file_name_without_extension=$this->get_media_file_name_without_extension($file_name);

				$timestamp=time();

				$original_file_name=$original_file_name_with_extension = $timestamp.'_'.$file_name;;

				//$original_file_name=$original_file_name_without_extension.'.'.$media_extension;

				//always use constants when making file paths, to avoid the possibilty of remote file inclusion
				
				$fullPath = JPATH_SITE.DS.'images'.DS.'jGive'.DS;
				$relPath = 'images'.DS.'jGive'.DS;
				//if folder is not present create it
				if(!JFolder::exists(JPATH_SITE.DS.'images'.DS.'jGive')){
					@mkdir(JPATH_SITE.DS.'images'.DS.'jGive');
				}

				//determine if resizing is needed for images


				foreach ($img_dimensions_config as $config)
				{
					$media_dimnesions = new stdClass;

					//If component optins saved the get the image dimentions 
					if ($params->get( $config.'_width' ))
					{
						$media_dimnesions->img_width=$params->get( $config.'_width' );
					}
					else // If there is no value exist then get default value
					{

						switch ($config . '_width')
						{

							case 'small_width':
							$media_dimnesions->img_width=64;
							break;

							case 'medium_width':
							$media_dimnesions->img_width=120;
							break;

							case 'large_width':
							$media_dimnesions->img_width=400;
							break;

							default:
								$media_dimnesions->img_width=400;
							break;

						}
					}

					if ($params->get( $config.'_height' ))
					{
						$media_dimnesions->img_height=$params->get( $config.'_height' );
					}
					else
					{

						switch($config . '_height')
						{
							case 'small_height':
								$media_dimnesions->img_height=64;
							break;

							case 'medium_height':
								$media_dimnesions->img_height=120;
							break;

							case 'large_height':
								$media_dimnesions->img_height=400;
							break;

							default:
								$media_dimnesions->img_height=400;
							break;

						}
					}


					//$media_dimnesions->img_height=$params->get( $config.'_height' );
					$max_zone_width  = $media_dimnesions->img_width;
					$max_zone_height = $media_dimnesions->img_height;

					switch ($config)
					{
						case 'small':
						$file_name_with_extension_size = "S_".$original_file_name_with_extension;
						break;
						case 'medium':
						$file_name_with_extension_size = "M_".$original_file_name_with_extension;
						break;
						case 'large':
						$file_name_with_extension_size = "L_".$original_file_name_with_extension;
						break;
						default:
						$file_name_with_extension_size = $original_file_name_with_extension;
						break;
					}
				
					//if($media_type_group['media_type_group']!="video" )// skip resizing for video
					if($media_type_group['media_type_group']=="image" )
					{
						
						//get uploaded image dimensions
						$media_size_info=$this->check_media_resizing_needed($media_dimnesions,$file_tmp_name);
				
						$resizing=0;
						if($media_size_info['resize']){
							$resizing=1;
						}
			
						switch ($resizing)
						{
							case 0:
									$new_media_width=$media_size_info['width_img'];
									$new_media_height=$media_size_info['height_img'];
									$top_offset=0;//@TODO not sure abt this
									$blank_height=$new_media_height;//@TODO not sure abt this
								break;
							case 1:
									$new_dimensions=$this->get_new_dimensions($max_zone_width, $max_zone_height,'auto');
									$new_media_width=$new_dimensions['new_calculated_width'];
									$new_media_height=$new_dimensions['new_calculated_height'];
									$top_offset=$new_dimensions['top_offset'];
									$blank_height=$new_dimensions['blank_height'];
								break;
						}
					}
					else //as we skipped resizing for video , we will use zone dimensions
					{
						$new_media_width=$media_dimnesions->img_width;
						$new_media_height=$media_dimnesions->img_height;
						$top_offset=0;//@TODO not sure abt this
						$blank_height=$new_media_height;
					}

					$colorR = 255;
					$colorG = 255;
					$colorB = 255;

					$upload_image = $this->uploadImage($file_field,$max_zone_width, $max_zone_height, $fullPath, $relPath, $colorR, $colorG, $colorB,$new_media_width,$new_media_height,$blank_height,$top_offset,$media_extension,$file_name_with_extension_size);

				}

				//print_r($original_file_name);

				if($upload_orig=='1'){
					$upload_path = $fullPath.$original_file_name;

					if(!JFile::upload($file_tmp_name,$upload_path))
					{
						$app->enqueueMessage(JText::_('COM_JGIVE_ERROR_MOVING_FILE'),'error');
						echo JText::_('COM_JGIVE_ERROR_MOVING_FILE');
						return false;
					}
				}
				return $original_file_name;
			}
		}
		return false;
	}
	//check for max media size allowed for upload
	function check_max_size($file_size)
	{
		$this->media_size=$file_size;//@TODO needed?
		$max_media_size=$this->sa_config['image_size']*1024;
		if($file_size>$max_media_size){ 
			return 1;
		}
		return 0;
	}
	//detect file type 
	//detect media group type image/video/flash
	function check_media_type_group($file_type)
	{
		$allowed_media_types=array( 
			'image'=>array
				( 
				//images
				'image/png',
				'image/jpeg',
				'image/pjpeg',
				'image/jpeg',
				'image/pjpeg',
				'image/jpeg',
				'image/pjpeg'
				)
		);
		$media_type_group='';
		$flag=0;
		foreach($allowed_media_types as $key=>$value)
		{
			if(in_array($file_type,$value)){
				$media_type_group=$key;
				$flag=1;
				break;
			}
		}
		
		$this->media_type=$file_type;
		$this->media_type_group=$media_type_group;
		
		$return['media_type']=$file_type;
		$return['media_type_group']=$media_type_group;
		if(!$flag){
			$return['allowed']=0;
			return $return;//file type not allowed 
		}
		$return['allowed']=1;
		return $return;//allowed file type
	}
	function get_media_extension($file_name)
	{
		$media_extension=pathinfo($file_name);
		$this->media_extension=$media_extension['extension'];
		return $media_extension['extension'];
	}
	function get_media_file_name_without_extension($file_name)
	{
		$media_extension=pathinfo($file_name);
		return $media_extension['filename'];
	}
	function check_media_resizing_needed($adzone_media_dimnesions,$file_tmp_name)
	{
		//get uploaded image height and width
		//this will work for all images + swf files
		list($width_img,$height_img) = getimagesize($file_tmp_name);
		$return['width_img']=$width_img;
		$return['height_img']=$height_img;
		$this->width =$width_img;
		$this->height=$height_img;
		if($width_img==$adzone_media_dimnesions->img_width && $height_img==$adzone_media_dimnesions->img_height){
			$return['resize']=0;
			return $return;//no resizing needed
		}
		$return['resize']=1;
		return $return;//resizing needed
	}
	function get_new_dimensions($max_zone_width, $max_zone_height, $option)
	{
	   switch ($option)
		{
			case 'exact':
				$new_calculated_width = $max_zone_width;
				$new_calculated_height= $max_zone_height;
				break;
			case 'auto':
				$new_dimensions = $this->get_optimal_dimensions($max_zone_width, $max_zone_height);
				$new_calculated_width = $new_dimensions['new_calculated_width'];
				$new_calculated_height = $new_dimensions['new_calculated_height'];
				break;
		}
		$new_dimensions['new_calculated_width']=$new_calculated_width;
		$new_dimensions['new_calculated_height']=$new_calculated_height;
		return $new_dimensions;
	}
	//function uploadImage($file_field, $maxSize, $max_zone_width, $fullPath, $relPath, $colorR, $colorG, $colorB, $max_zone_height = null){
	function uploadImage($file_field,$max_zone_width,$max_zone_height = null,$fullPath, $relPath, $colorR, $colorG, $colorB,$new_media_width,$new_media_height,$blank_height,$top_offset,$media_extension,$file_name_with_extension_size)
	{
		switch($this->media_type_group)
		{
			case "flash":
				jimport('joomla.filesystem.file');
				//Retrieve file details from uploaded file, sent from upload form
				$file=$_FILES[$file_field];//JRequest::getVar('ad_image', null, 'files', 'array');
				//Clean up filename to get rid of strange characters like spaces etc
				$filename = JFile::makeSafe($file['name']);
				//Set up the source and destination of the file
				$src=$file['tmp_name'];
				
				$filename = strtolower($filename);
				$filename = preg_replace('/\s/', '_', $filename);
				$timestamp = time();
				//$file_name_without_extension=$this->get_media_file_name_without_extension($filename); 
				$filename = $file_name_with_extension_size;
				$dest =$fullPath."swf".DS.$filename;
				
				//First check if the file has the right extension, we need swf only
				if(JFile::upload($src,$dest))
				{
					$dest = $fullPath."swf".DS.$filename;
					return $dest;
				}
				
			break;
			
			case "video":
				jimport('joomla.filesystem.file');
				//Retrieve file details from uploaded file, sent from upload form
				$file = $_FILES[$file_field];//JRequest::getVar('ad_image', null, 'files', 'array');
				//Clean up filename to get rid of strange characters like spaces etc
				$filename = JFile::makeSafe($file['name']);
				//Set up the source and destination of the file
				$src = $file['tmp_name'];
				
				$filename = strtolower($filename);
				$filename = preg_replace('/\s/', '_', $filename);
				$timestamp = time();
				//$file_name_without_extension=$this->get_media_file_name_without_extension($filename);
				$filename = $timestamp."_".$file_name_with_extension_size;

				$dest = $fullPath."vids".DS.$filename;
				if(JFile::upload($src,$dest))
				{
					$dest = $fullPath."vids".DS.$filename;
					return $dest;
				}
			break;
 		}
		$errorList= array();
		 //$folder = $relPath;
		 $folder=$fullPath;  // ADDED BY @VIDYASAGAR
		$match = "";
		$filesize = $_FILES[$file_field]['size'];
		
		if($filesize > 0)
		{	
			$filename = strtolower($_FILES[$file_field]['name']);
			$filename = preg_replace('/\s/', '_', $filename);
			
		   	if($filesize < 1){ 
				$errorList[] = JText::_('FILE_EMPTY');
			}
				
			if(count($errorList)<1)
			{
				$match = "1"; // File is allowed
				$NUM = time();
				//$front_name = $file_name_with_extension_size;
				//$newfilename = $front_name.".".$media_extension;
				$newfilename = $file_name_with_extension_size;
				$save = $folder.$newfilename;
				if(!file_exists($save))
				{
					list($this->width, $this->height) = getimagesize($_FILES[$file_field]['tmp_name']);
					$image_p = imagecreatetruecolor($new_media_width, $blank_height);
					$white = imagecolorallocate($image_p, $colorR, $colorG, $colorB);
					//START added to preserve transparency
					imagealphablending($image_p, false);
					imagesavealpha($image_p,true);
					$transparent = imagecolorallocatealpha($image_p, 255, 255, 255, 127);
					imagefill($image_p, 0, 0, $transparent);
					//END added to preserve transparency
				
					switch($media_extension)
					{
						/*case "gif":
							$gr = new qtc_gifresizer;//New Instance Of GIFResizer
							//echo 
							$gr->temp_dir = $folder.'frames'; //Used for extracting GIF Animation Frames
							//if folder is not present create it
							if(!JFolder::exists($gr->temp_dir)){
								@mkdir($gr->temp_dir);
							}
							//$gr->resize("gifs/1.gif","resized/1_resized.gif",50,50); //Resizing the animation into a new file.
							$gr->resize($_FILES[$file_field]['tmp_name'],$save,$new_media_width,$new_media_height); //Resizing the animation into a new file.
						break;*/

						case "jpg":
							$image = @imagecreatefromjpeg($_FILES[$file_field]['tmp_name']);
							@imagecopyresampled($image_p, $image, 0, $top_offset, 0, 0, $new_media_width, $new_media_height, $this->width, $this->height);
						break;
						
						case "jpeg":
							$image = @imagecreatefromjpeg($_FILES[$file_field]['tmp_name']);
							@imagecopyresampled($image_p, $image, 0, $top_offset, 0, 0, $new_media_width, $new_media_height, $this->width, $this->height);
						break;
						
						case "png":
							$image = @imagecreatefrompng($_FILES[$file_field]['tmp_name']);
							@imagecopyresampled($image_p, $image, 0, $top_offset, 0, 0, $new_media_width, $new_media_height, $this->width, $this->height);
						break;
					}
				
				
					switch($media_extension)
					{
						/*
						case "gif":
							if(!@imagegif($image_p, $save)){
								$errorList[]= JText::_('FILE_GIF');
							}
					
						break;
						*/
						case "jpg":
							if(!@imagejpeg($image_p, $save, 100)){
								$errorList[]= JText::_('FILE_JPG');
							}
						break;
						case "jpeg":
							if(!@imagejpeg($image_p, $save, 100)){
								$errorList[]= JText::_('FILE_JPEG');
							}
						break;
						case "png":
							if(!@imagepng($image_p, $save, 0)){
								$errorList[]= JText::_('FILE_PNG');
							}
						break;
					}
					@imagedestroy($filename);
				}
				else
				{
					$errorList[]=  JText::_('FILE_EXIST');
				}	
			}
		}
		else
		{
			$errorList[]= JText::_('FILE_NO');
		}
		if(!$match){
		   	$errorList[]= JText::_('FILE_ALLOW').":". $filename;
		}
		if(sizeof($errorList) == 0){
			return $fullPath.$newfilename;
		}
		else
		{
			$eMessage = array();
			for ($x=0; $x<sizeof($errorList); $x++){
				$eMessage[] = $errorList[$x];
			}
		   	return $eMessage;
		}
	}
	function get_optimal_dimensions($max_zone_width, $max_zone_height)
	{

		$top_offset=0;//@TODO not sure abt this
		if($max_zone_height == null)
		{
			if($this->width < $max_zone_width){
				$new_calculated_width = $this->width;
			}else{
				$new_calculated_width = $max_zone_width;
			}
			$ratio_orig = $this->width/$this->height;
			$new_calculated_height = $new_calculated_width/$ratio_orig;

			$blank_height = $new_calculated_height;
			$top_offset = 0;
	
		}
		else{
			if($this->width <= $max_zone_width && $this->height <= $max_zone_height){
				$new_calculated_height = $this->height;
				$new_calculated_width = $this->width;
			}else{
				if($this->width > $max_zone_width){
					$ratio = ($this->width / $max_zone_width);
					$new_calculated_width = $max_zone_width;
					$new_calculated_height = ($this->height / $ratio);
					if($new_calculated_height > $max_zone_height){
						$ratio = ($new_calculated_height / $max_zone_height);
						$new_calculated_height = $max_zone_height;
						$new_calculated_width = ($new_calculated_width / $ratio);
					}
				}
				if($this->height > $max_zone_height){
					$ratio = ($this->height / $max_zone_height);
					$new_calculated_height = $max_zone_height;
					$new_calculated_width = ($this->width / $ratio);
					if($new_calculated_width > $max_zone_width){
						$ratio = ($new_calculated_width / $max_zone_width);
						$new_calculated_width = $max_zone_width;
						$new_calculated_height = ($new_calculated_height / $ratio);
					}
				}
			}
			
			if($new_calculated_height == 0 || $new_calculated_width == 0 || $this->height == 0 || $this->width == 0){
				//die(JText::_('FILE_VALID'));
			}
			if($new_calculated_height < 45){
				$blank_height = 45;
				$top_offset = round(($blank_height - $new_calculated_height)/2);
			}else{
				$blank_height = $new_calculated_height;
			}
		}
		
		$new_dimensions['new_calculated_width']=$new_calculated_width;
		$new_dimensions['new_calculated_height']=$new_calculated_height;
		$new_dimensions['top_offset']=$top_offset;
		$new_dimensions['blank_height']=$blank_height;

		return $new_dimensions;
	}
}
