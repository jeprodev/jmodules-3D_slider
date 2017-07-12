<?php
/**
 * @package     modules
 * @subpackage  mod_art_slider
 * @link        http://jeprodev.net/index.php
 *
 */

//prevent direct access from users
defined('_JEXEC') or die(JText::_("MOD_3D_ART_SLIDER_RESTRICTED_AREA_MESSAGE") . '<a href="' . JURI::base() .'" >' . JText::_("JHOME"). '</a>');

$slideSelector = $params->get('jeprodev_slide_selector', 'slide_item');
$scale = 1;
$script = 'jQuery(document).ready(function(){ 
    jQuery("#slide_carousel_' . $uniqueId .'").ArticleCarouselExtra({
        activeClass:"' . $params->get('jeprodev_art_slider_active_class', 'selected') . '",
        container: "' . $params->get('jeprodev_art_slider_container', 'slide') . '",
        selector: "' . $params->get('jeprodev_art_slider_selector', 'slide_item') . '",
        scroll: ' . $params->get('jeprodev_art_slider_scroll', 1) . ',
        scale: ' . $scale . ',
        distance: ' . $params->get('jeprodev_art_slider_distance', 1) . ',
        circular: ' . ($params->get('jeprodev_art_slider_circular', 1) == 1 ? 'true' : 'false') . ',
        current: ' . $params->get('jeprodev_art_slider_current', 3) . ',
        animation: "' . $params->get('jeprodev_art_slider_animation', 'Rotate3D') . '",
        minimal_ratio : ' . (($scale == 1) ? $params->get('jeprodev_art_slider_min', .1) : .4) . ',
        radius: { x:0, y:' . (($scale == 1) ? $params->get('jeprodev_art_slider_y_radius', 10) : 50) . '},
        margin: ' . (($scale == 1) ? $params->get('jeprodev_art_slider_margin', 25) : 0). ',
        max_width: ' . $params->get('jeprodev_art_slider_image_width', 400) . ',
        offset_angle: ' .(($scale == 1) ? $params->get('jeprodev_art_slider_offset_angle', -195) : 0) . ',
        center_offset:{
            x: ' . $params->get('jeprodev_art_slider_center_x_offset', -100) . ',
            y: ' . $params->get('jeprodev_art_slider_center_y_offset', 0) . '
        },
        fx : { duration: ' . $params->get('jeprodev_art_slider_fx_duration', 300) . '},
        interval: ' . $params->get('jeprodev_art_slider_interval', 10) . ',
        delay: ' . $params->get('jeprodev_art_slider_delay', 10) . ',
        auto_start: ' . ($params->get('jeprodev_art_slider_auto_start', 1) == 1 ? 'true' : 'false') . '
    });
  })';

$document->addScriptDeclaration($script);
?>

<div id="slide_carousel_<?php echo $uniqueId ?>" class="art-slide-carousel"
     style="height: <?php echo $artSliderWrapperHeight  . 'px'; ?>; width: <?php echo $artSliderWrapperWidth . 'px'; ?>" >
    <div id="<?php echo $params->get('jeprodev_art_slider_container', 'slide'); ?>"  >
        <?php if(count($articlesList)){
            foreach ($articlesList as $art){ ?>
        <div class="item <?php echo $slideSelector; ?>"   >
            <a href="<?php echo $art->link; ?>">
                <img src="<?php echo $art->main_image; ?>" width="<?php echo $artSliderImageWidth . 'px'; ?>"
                    height="<?php echo $artSliderImageHeight . 'px'; ?>"/>
            </a>
            <span class="slide_caption" >
                <a href="<?php echo $art->link; ?>" ><?php echo $art->title; ?> </a>
            </span>
        </div>
        <?php    }
} ?>
    </div>
</div>
