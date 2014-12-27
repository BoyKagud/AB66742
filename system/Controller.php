<?php
define('THUMBNAIL_IMAGE_MAX_WIDTH', 128);
define('THUMBNAIL_IMAGE_MAX_HEIGHT', 128);

define('UPLOADED_IMAGE_DESTINATION', './images/');
define('THUMBNAIL_IMAGE_DESTINATION', './thumbnails/');

class Controller {

	private $model;
	private $view;

	public function view($args = null) {
		$view = explode("_", get_calling_class());
		$view = $view[0];
		$class = $view.VIEW_SUFFIX;
		require_once('View.php'); 
		require_once('view/'.$class.".php"); 
		new $class($args);
	}

	public function model() { // revise from var naming convention to directories
		$model = explode("_", get_calling_class());
		$model = $model[0];
		$class = $model.MODEL_SUFFIX;
		require_once('Model.php');
		require_once("model/".$class.".php"); 

		$db = new $class();
		return $db;
	}

	public static function get_index() {
		require_once('controller/Home_Controller.php');
		new Home_Controller();
	}

	public static function get_page($page, $args = array()) {
		$pageContURI = 'controller/'.$page."_Controller.php";
		// echo $pageContURI;
		if(file_exists($pageContURI)) {
			require_once($pageContURI);
			$cont = $page."_Controller";
			new $cont($args);
		} else {Controller::get_index();}
	}

	public static function get_home_url() {
		return HOME_URL;
	}

	//source : http://php.net/manual/en/function.empty.php
	public static function array_empty($mixed) {
		if (is_array($mixed)) {
	        foreach ($mixed as $value) {
	            if (!self::array_empty($value)) {
	                return false;
	            }
	        }
	    }
	    elseif (!empty($mixed)) {
	        return false;
	    }
   		return true;
	}

	public static function getInt($str) {
		preg_match_all('!\d+!', $str, $matches);
		if(self::array_empty($matches)) {
			return 0;
		} else {
			return $matches;
		}
	}

	public static function getSpecialChars($str) {
		preg_match('/[^a-zA-Z]+/', $str, $matches);
		if(self::array_empty($matches)) return 0; else return $matches;
	}

	public static function isAlphaOnly($str) {
		$ret = true;
		if(self::getInt($str) == 0) $ret = true; else $ret = false;
		if(self::getSpecialChars($str) == 0) $ret = true; else $ret = false;
		return $ret;
	}

	public function sendMail($to, $subject, $message) {
		$headers = 'From: WeeTune.com <noreply@WeeTune.com>' . "\r\n"
						."Content-type: text/html; charset=iso-8859-1\r\n"
						."Content-Transfer-Encoding: 8bit\r\n\r\n";
			mail($to, $subject, $message, $headers);		
	}

	public function uploadIMG() {
		$result = process_image_upload('Image1');
		if ($result === false) {
		    echo '<br>An error occurred while processing upload';
		} else {
		    echo '<br>Uploaded image saved as ' . $result[0];
		    echo '<br>Thumbnail image saved as ' . $result[1];
		}
	}

		/*
	 * PHP function to resize an image maintaining aspect ratio
	 * http://salman-w.blogspot.com/2008/10/resize-images-using-phpgd-library.html
	 *
	 * Creates a resized (e.g. thumbnail, small, medium, large)
	 * version of an image file and saves it as another file
	 */
	function generate_image_thumbnail($source_image_path, $thumbnail_image_path)
	{
	    list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
	    switch ($source_image_type) {
	        case IMAGETYPE_GIF:
	            $source_gd_image = imagecreatefromgif($source_image_path);
	            break;
	        case IMAGETYPE_JPEG:
	            $source_gd_image = imagecreatefromjpeg($source_image_path);
	            break;
	        case IMAGETYPE_PNG:
	            $source_gd_image = imagecreatefrompng($source_image_path);
	            break;
	    }
	    if ($source_gd_image === false) {
	        return false;
	    }
	    $source_aspect_ratio = $source_image_width / $source_image_height;
	    $thumbnail_aspect_ratio = THUMBNAIL_IMAGE_MAX_WIDTH / THUMBNAIL_IMAGE_MAX_HEIGHT;
	    if ($source_image_width <= THUMBNAIL_IMAGE_MAX_WIDTH && $source_image_height <= THUMBNAIL_IMAGE_MAX_HEIGHT) {
	        $thumbnail_image_width = $source_image_width;
	        $thumbnail_image_height = $source_image_height;
	    } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
	        $thumbnail_image_width = (int) (THUMBNAIL_IMAGE_MAX_HEIGHT * $source_aspect_ratio);
	        $thumbnail_image_height = THUMBNAIL_IMAGE_MAX_HEIGHT;
	    } else {
	        $thumbnail_image_width = THUMBNAIL_IMAGE_MAX_WIDTH;
	        $thumbnail_image_height = (int) (THUMBNAIL_IMAGE_MAX_WIDTH / $source_aspect_ratio);
	    }
	    $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
	    imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
	    imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
	    imagedestroy($source_gd_image);
	    imagedestroy($thumbnail_gd_image);
	    return true;
	}

	/*
	 * Uploaded file processing function
	 */


	function process_image_upload($field)
	{
	    $temp_image_path = $_FILES[$field]['tmp_name'];
	    $temp_image_name = $_FILES[$field]['name'];
	    list(, , $temp_image_type) = getimagesize($temp_image_path);
	    if ($temp_image_type === NULL) {
	        return false;
	    }
	    switch ($temp_image_type) {
	        case IMAGETYPE_GIF:
	            break;
	        case IMAGETYPE_JPEG:
	            break;
	        case IMAGETYPE_PNG:
	            break;
	        default:
	            return false;
	    }
	    $uploaded_image_path = UPLOADED_IMAGE_DESTINATION . $temp_image_name;
	    move_uploaded_file($temp_image_path, $uploaded_image_path);
	    $thumbnail_image_path = THUMBNAIL_IMAGE_DESTINATION . preg_replace('{\\.[^\\.]+$}', '.jpg', $temp_image_name);
	    $result = generate_image_thumbnail($uploaded_image_path, $thumbnail_image_path);
	    return $result ? array($uploaded_image_path, $thumbnail_image_path) : false;
	}

}

function eliminateIntIndex(&$array) {
	// die(print_r($array));
	foreach($array as $index => $val) {
		if(is_array($val)) 
			eliminateIntIndex($val);
    	elseif(is_int($index))
        	unset($array[$index]);
    }

}

function get_calling_class() {

    //get the trace
    $trace = debug_backtrace();

    // Get the class that is asking for who awoke it
    $class = $trace[1]['class'];

    // +1 to i cos we have to account for calling this function
    for ( $i=1; $i<count( $trace ); $i++ ) {
        if ( isset( $trace[$i] ) ) // is it set?
             if ( $class != $trace[$i]['class'] ) // is it a different class
                 return $trace[$i]['class'];
    }
}