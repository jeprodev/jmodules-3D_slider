<?php
/**
 * @package     modules
 * @subpackage  mod_art_slider
 * @link        http://jeprodev.net/index.php
 *
 */

//prevent direct access from users
defined('_JEXEC') or die(JText::_("MOD_3D_ART_SLIDER_RESTRICTED_AREA_MESSAGE") . '<a href="' . JURI::base() .'" >' . JText::_("JHOME"). '</a>');

jimport('joomla.filesystem.file');
jimport('joomla.plugin.helper');

require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'helper.php');

$uniqueId = $module->id;
$artSliderWrapperHeight = $params->get('jeprodev_art_slider_wrapper_height', 400);
$artSliderWrapperWidth = $params->get('jeprodev_art_slider_wrapper_width', 810);
$artSliderImageHeight = $params->get('jeprodev_art_slider_image_height', 300);
$artSliderImageWidth = $params->get('jeprodev_art_slider_image_width', 400);
$artSliderAnimation = $params->get('jeprodev_art_slider_animation', 'Rotate3D');

$styleSheet = $params->get('jeprodev_art_slider_style_sheet', 'default');
$selector = $params->get('jeprodev_art_slide_selector', 'slide_item');
$articlesList = ModArticleSliderHelper::getArticlesList($params);

$document = JFactory::getDocument();
$document->addStyleSheet('modules/mod_art_slider/assets/css/' . $styleSheet . '.css');
//$document->addStyleSheet(JURI::base(true) . 'modules/mod_art_slider/assets/css/' . $styleSheet . '.php?mod_id=' . $uniqueId . '&selector=' . $selector);
$document->addScript('modules/mod_art_slider/assets/js/ArticleCarouselExtra.js');

// Get module layout
require JModuleHelper::getLayoutPath('mod_art_slider', $params->get('jeprodev_art_slider_layout', 'default'));