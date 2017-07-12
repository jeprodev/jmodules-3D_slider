<?php
/**
 * @package     modules
 * @subpackage  mod_art_slider
 * @link        http://jeprodev.net/index.php
 *
 */
// prevent direct access from users
defined('_JEXEC') or die(JText::_("MOD_3D_ART_SLIDER_RESTRICTED_AREA_MESSAGE") . '<a href="' . JURI::base() .'" >' . JText::_("JHOME"). '</a>');

class ModArticleSliderHelper {
    const cacheDirectory = 'art_slider';

    /**
     * method to retrieve the selected articles to be used in the slider.
     * @param $params
     * @return array of $articles.
     */
    public static function getArticlesList($params){
        $db = JFactory::getDbo();
        $imageWidth = $params->get('jeprodev_art_slider_image_width', 400);
        $imageHeight = $params->get('jeprodev_art_slider_image_height', 300);

        $ids  = preg_split('/,/', $params->get('jeprodev_art_slider_articles_ids', ''));

        if(!is_dir(JPATH_SITE . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . self::cacheDirectory)){
            JFolder::create(JPATH_SITE . DIRECTORY_SEPARATOR  . 'cache' . DIRECTORY_SEPARATOR . self::cacheDirectory);
        }
        $noImageFilePath = 'cache' . DIRECTORY_SEPARATOR . self::cacheDirectory
            . DIRECTORY_SEPARATOR . $imageWidth .'x' . $imageHeight . '-no-photo.jpg';

        if(!file_exists(JPATH_SITE . DIRECTORY_SEPARATOR . $noImageFilePath)){
            $noImageFilePath = 'modules' . DIRECTORY_SEPARATOR . 'mod_art_slider' . DIRECTORY_SEPARATOR
                . 'assets'. DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'no-photo.jpg';
            self::renderThumb($noImageFilePath, $imageWidth, $imageHeight );
        }

        $authorized = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
        $access = !JComponentHelper::getParams('com_content')->get('show_noauth');

        $items = [];

        $query = "SELECT article.*, category.alias AS category_alias FROM " . $db->quoteName('#__content') . " AS article LEFT JOIN "
            . $db->quoteName('#__categories') . " AS category ON (article." . $db->quoteName('catid') .  " = category." .
            $db->quoteName('id') . ") WHERE article." . $db->quoteName('state') . " = 1 AND article." . $db->quoteName('id') . " = ";

        foreach ($ids as $id){
            $db->setQuery($query . $id);
            $item = $db->loadObject();

            if($item != null) {
                $item->slug = $item->id . ':' . $item->alias;
                $item->cat_slug = $item->catid . ':' . $item->category_alias;

                if($access || in_array($item->access, $authorized)){
                    $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->cat_slug));
                }else{
                    $item->link = JRoute::_("'index.php?option=com_users&view=login'");
                }

                $item  = self::parseImage($item);
                if($item->main_image && $image = self::renderThumb($item->main_image, $imageWidth, $imageHeight, $item->title)){
                    $item->main_image = $image;
                }else{
                    $item->main_image = JURI::base() . 'cache/' . self::cacheDirectory . '/'. $imageWidth .'x' . $imageHeight . '-no-photo.jpg';
                }
                $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * A function to render an image thumb
     * @param $path
     * @param int $width
     * @param int $height
     * @param string $title
     * @return String $path rendered image file path
     */
    public static function renderThumb($path, $width=100, $height=100, $title=''){
        $path = str_replace(JURI::base(), '', $path);
        $imageSource = JPATH_SITE . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);

        if(file_exists($imageSource)) {
            $tmp = explode('/', $path);
            $imageName = $width .'x' . $height . '-' . $tmp[count($tmp)-1];
            $thumbPath = JPATH_SITE . DIRECTORY_SEPARATOR  . 'cache' .DIRECTORY_SEPARATOR . self::cacheDirectory .DIRECTORY_SEPARATOR . $imageName;

            if(!file_exists($thumbPath)){
                $image = self::loadImage($imageSource);
                $image = self::resizeImage($image, $width, $height);
                self::save($image, $thumbPath, IMAGETYPE_JPEG); //, 75, 644);
            }
            $path = JURI::base() . 'cache/' . self::cacheDirectory . '/' . $imageName;
        }
        return $path;
    }

    /**
     * @param $imageSource
     * @param $width
     * @param $height
     * @return resource
     */
    static function resizeImage($imageSource, $width, $height){
        $newImage = imagecreatetruecolor($width, $height);
        imagecopyresampled($newImage, $imageSource, 0, 0, 0, 0, $width, $height, imagesx($imageSource), imagesy($imageSource));
        return $newImage;
    }

    /**
     * @param $imageSource
     * @return resource
     */
    static function loadImage($imageSource){
        $imageInfo = getimagesize($imageSource);
        $imageType = $imageInfo[2];

        switch ($imageType){
            case IMAGETYPE_PNG :
                $image = imagecreatefrompng($imageSource);
                break;
            case IMAGETYPE_GIF :
                $image = imagecreatefromgif($imageSource);
                break;
            default:
                $image = imagecreatefromjpeg($imageSource);
                break;
        }
        return $image;
    }

    /**
     * function to save an image
     * @param $imageData
     * @param $thumbPath
     * @param int $type
     * @param int $compression
     * @param null $permission
     */
    public static function save($imageData, $thumbPath, $type = IMAGETYPE_JPEG, $compression=75, $permission=null){
        if($type == IMAGETYPE_JPEG){
            imagejpeg($imageData, $thumbPath, $compression);
        }else if($type == IMAGETYPE_GIF){
            imagegif($imageData, $thumbPath);
        }else if($type == IMAGETYPE_PNG){
            imagepng($imageData, $thumbPath);
        }

        if($permission != null){
            chmod($thumbPath, $permission);
        }
    }

    static function parseImage($item){
        if(isset($item->images->image_fulltext) && isset($item->image->image_intro)){
            $item->thumbnail = $item->images->images_intro;
            $item->main_image = $item->image_fulltext;

            if(empty($item->images->image_fulltext)){
                $item->main_image  = $item->images->image_intro;
            }

            if(empty($item->images->intro)){
                $item->thumbnail = $item->images->image_fulltext;
            }
        }

        if(empty($item->thumbnail) && empty($item->main_image)){
            $text = $item->introtext;
            $regex = '/<img.+src=[\'\"](?P<src>.+?)[\'\"].*>/i';
            preg_match($regex, $text, $matches);
            $images = (count($matches)) ? $matches : array();

            if(count($images)){
                $item->main_image = $images['src'];
                $item->thumbnail = $images['src'];
            }else{
                $item->main_image = '';
                $item->thumbnail = '';
            }
        }
        return $item;
    }
}