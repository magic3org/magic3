<?php

defined('_JEXEC') or die;

class DesignerShortcodes
{
    public static $shortcodes = array();
    public static $filters = array();

    public static function atts($defaults, $atts) {
        $atts = (array)$atts;
        $ret = array();
        foreach($defaults as $name => $default) {
            if (array_key_exists($name, $atts))
                $ret[$name] = $atts[$name];
            else
                $ret[$name] = $default;
        }
        return $ret;
    }

    public static function getRegexp($tag) {
        return '\\[(\\[?)' . "($tag)" . '(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)';
    }

    public static function process($content) {
        foreach(DesignerShortcodes::$filters as $filter => $func)
            $content = call_user_func($func, $content);
        $content = DesignerShortcodes::buildShortCodes($content);
        return $content;
    }

    public static function buildShortCodes($content) {
        foreach(DesignerShortcodes::$shortcodes as $tag => $func) {
            $pattern = self::getRegexp($tag);
            $content = preg_replace_callback( "/$pattern/s", 'DesignerShortcodes::replacer', $content );
        }
        return $content;
    }

    public static function replacer($matches) {

        $tag = $matches[2];
        $attr = self::parseAttr( $matches[3] );

        if (isset($matches[5])) {
            return $matches[1] . call_user_func( DesignerShortcodes::$shortcodes[$tag], $attr, $matches[5], $tag ) . $matches[6];
        } else {
            return $matches[1] . call_user_func( DesignerShortcodes::$shortcodes[$tag], $attr, null,  $tag ) . $matches[6];
        }
    }

    public static function parseAttr($text) {
        $atts = array();
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) and strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }

}

function button_styling($atts, $content='') {
    extract(DesignerShortCodes::atts(array(
        'link' => '/',
        'type' => 'default',
        'style' => '',
        'size' => '',
        'icon' => ''
    ), $atts));

    $classNames = 'bd-button';
    $linkContent = $content;
    $styles = array('default' => 'btn-default', 'primary' => 'btn-primary', 'success' => 'btn-success',
        'info' => 'btn-info', 'warning' => 'btn-warning', 'danger' => 'btn-danger', 'link' => 'btn-link');
    $sizes = array('large' => 'btn-lg', 'small' => 'btn-sm', 'xsmall' => 'btn-xs');

    if ($type === 'bootstrap') {
        $classNames = 'btn';
        array_key_exists(strtolower($style), $styles) ? $classNames .= ' ' . $styles[strtolower($style)] : '';
        array_key_exists(strtolower($size), $sizes) ? $classNames .= ' ' . $sizes[strtolower($size)] : '';
    }

    if ($icon !== '') {
        $linkContent = '<span class="' . $icon . '">&nbsp;</span>' . $linkContent;
    }

    return '<a class="' . $classNames . '" href="' . $link . '">' . $linkContent . '</a>';
}

DesignerShortcodes::$shortcodes['button'] = 'button_styling';

function googlemap_styling($atts){
   extract(DesignerShortCodes::atts(array(
        'address' => '',
        'zoom' => '',
        'map_type' => '',
        'language' => '',
        'css' => 'height:300px;width:100%',
    ), $atts));

    $languages = array("eu", "ca", "hr", "cs", "da", "nl", "en", "fi", "fr", "de", "gl", "el", "hi", "id", "it", "ja", "no",
                        "nn", "pt", "rm", "ru", "sr", "sk", "sl", "es", "sv", "th", "tr", "uk", "vi");

    if ($address !== '') {
          $address = '&q=' . $address;
      }

    if ($zoom !== ''){
        $num = (int) $zoom;
        if ($num>0){
            $zoom = '&z=' . $num;
        }
        else{
            $zoom = '';
        }
    }

    if ($map_type !== ''){
        switch ($map_type) {
          case "road":
            $map_type = '&t=m';
            break;
          case "satelite":
            $map_type = '&t=k';
            break;
          default:
            $map_type = '';
        }
    }

    if ($language !== '' && in_array($language, $languages)){
        $language = '&hl=' . $language;
    }
    else{
        $language = '';
    }

    $divs = '<div style="' . $css . '"><div class="embed-responsive" style="height: 100%; width: 100%;">';
    $iframe = '<iframe class="embed-responsive-item" src="http://maps.google.com/maps?output=embed' . $address . $zoom . $map_type . $language . '"></iframe>';
    $divEnd = '</div>';

    return $divs . $iframe . $divEnd . $divEnd;
}

DesignerShortcodes::$shortcodes['googlemap'] = 'googlemap_styling';

// [box css="" full_width="yes|no" content_width="yes|no"]content with shortcodes[/box]
function box_styling($atts, $content='') {
    extract(DesignerShortCodes::atts(array(
        'css' =>'',
        'full_width' => 'no',
        'content_width' => 'yes'
    ), $atts));
    $result = '';
    if ('yes' === $full_width) {
        $result .= '</div>';
    }
    $result .= '<div';
    if ($css !== '') {
        $result .= ' style="' . $css .'"';
    }
    $result .= '>';
    if ('yes' === $content_width) {
        $result .= '<div class="bd-container-inner">';
    }
    $result .= $content;
    if ('yes' === $content_width) {
        $result .= '</div>';
    }
    $result .= '</div>';
    if ('yes' === $full_width) {
        $result .= '<div class="bd-container-inner">';
    }
    return $result;
}
DesignerShortcodes::$shortcodes['box'] = 'box_styling';

// [[video link="https://www.youtube.com/watch?v=f20ym8X-9IU" autoplay="yes" loop="yes" title="no" lightBar="yes" style="width: 300px"][/video]
function video_styling($atts, $content='') {
    extract(DesignerShortCodes::atts(array(
        'link' => '/',
        'autoplay' => 'no',
        'loop' => 'no',
        'title' => 'yes',
        'light_control_bar' => 'no',
        'show_control_bar' => 'show',
        'css' => ''
    ), $atts));

    $isYouTube = strrpos($link, 'youtube');
    $isVimeo = strrpos($link, 'vimeo');

    if ($isYouTube !== false) {
        list(, $id) = explode('=', $link);
        list($id,) = explode('&', $id);
        $url = 'https://www.youtube.com/embed/' . $id . '?';

        if ($autoplay === 'yes')
            $url .= 'autoplay=1&';

        if ($title === 'no')
            $url .= 'showinfo=1&';

        if (light_control_bar === 'yes')
            $url .= 'theme=light&';

        if ($loop === 'yes')
            $url .= 'loop=1&playlist=' . $id . ' ';

        if ($show_control_bar === 'autohide')
            $url .= 'autohide=1&';
        else if ($show_control_bar === 'hide')
             $url .= 'controls=0&';

        $iframe = '<iframe src="' . $url . '"></iframe>';
    } else if ($isVimeo !== false) {
       $id = end(explode('/', $link));
       $url = 'https://player.vimeo.com/video/' . $id . '?';

       if ($autoplay === 'yes')
           $url .= 'autoplay=1&';

       if ($title === 'no')
           $url .= 'title=1&';

       if (light_control_bar === 'yes')
           $url .= 'color=ffffff&';

       if ($loop === 'yes')
           $url .= 'loop=1';

       $iframe = '<iframe src="' . $url . '"></iframe>';
    }

    return '<div class="embed-responsive embed-responsive-16by9" style="' . $css . '">' . $iframe . '</div>';
}
DesignerShortcodes::$shortcodes['video'] = 'video_styling';

function textGroup_styling($atts, $content='') {
    extract(DesignerShortCodes::atts(array(
        'image' => '',
        'image_link' => '',
        'image_position' => 'left',
        'image_width' => '',
        'image_height' => '',
        'image_css' => '',
        'header' => '',
        'header_tag' => 'h4',
        'header_css' => '',
        'css' => ''
    ), $atts));

    $image_positions = array('left' => 'pull-left', 'right' => 'pull-right', 'top' => 'top', 'bottom' => 'bottom', 'middle' => 'middle');

    $headers = array('h1' => 'h1', 'h2' => 'h2', 'h3' => 'h3', 'h4' => 'h4', 'h5' => 'h5', 'h6' => 'h6');
    array_key_exists(strtolower($header_tag), $headers) ? $_header = $headers[strtolower($header_tag)] : $_header = 'h4';
    $_header = $header !== '' ? '<' . $_header . ' class="media-heading"' .
                    ($header_css !== '' ? ' style="' . $header_css . '"' : '') . '>' . $header . '</' . $_header . '>' : '';

    $_ip = strtolower($image_position);

    $_i = '';
    if ($image !== '') {
        $_iWidth = $image_width !== '' ? ' width="' . ($image_width) . '"' : '';
        $_iHeight = $image_height !== '' ? ' height="' . ($image_height) . '"' : '';
        $_i = '<img class="bd-imagestyles media-object img-responsive"' . ($image_css !== '' ? ' style="' . $image_css . '"' : '')
            . ' src="' . $image . '"' . $_iWidth . $_iHeight . '/>';
        if (array_key_exists($_ip, $image_positions)) {
            if ($_ip === 'left' || $_ip === 'right') {
                $_i = '<a class="' . $image_positions[$_ip] . '" href="' . ($image_link) . '">' . $_i . '</a>';
            } else {
                $_i = '<a href="' . ($image_link) . '">' . $_i . '</a>';
            }
        }
    }

    $_c = '<div class="media"' . ($css !== '' ? ' style="' . $css . '"' : '') . '>';

    if ($_ip === 'middle') {
        return $_c . '<div class="media-body">' . $_header . $_i . $content . '</div></div>';
    }

    $_content = '<div class="media-body">' . $_header . $content . '</div>';

    if ($_ip !== 'bottom') {
        return $_c . $_i . $_content . '</div>';
    }

    return $_c . $_content . $_i . '</div>';
}

DesignerShortcodes::$shortcodes['text_group'] = 'textGroup_styling';

/*
 [slider css="" wide_slides="yes|no" wide_carousel="yes|no" interval="3000"]
    [slide css="" image="http:// | id" link="" linktarget=""]any slide content here[/slide]
 [/slider]
*/

function existsCssProperty($property, $css) {
    $existsProperty = false;
    if ($css !== '') {
        $styles = explode(';', $css);
        foreach ($styles as $i => $style) {
            $parts = explode(':', $style);
            if ($property === trim($parts[0]) && count($parts) > 1) {
                $existsProperty = true;
            }
        }
    }
    return $existsProperty;
}

function slider_styling($atts, $content='') {
    extract(DesignerShortCodes::atts(array(
        'wide_slides' => 'yes',
        'wide_carousel' => 'yes',
        'carousel' => 'yes',
        'interval' => '3000',
        'indicators' => 'yes',
        'wide_indicators' => 'no',
        'indicators_position' => 'left top',
        'css' => '',
        'id' => ''
    ), $atts));
    if (!existsCssProperty('height', $css)) {
        $css = 'height:200px;' . $css;
    }
    if (!$id) {
        $id = uniqid('slider');
    }

    $pattern = DesignerShortCodes::getRegexp('slide');
    $countSlides = preg_match_all('/' . $pattern . '/', $content);

    $orig_shortcode_tags = DesignerShortcodes::$shortcodes;

    DesignerShortcodes::$shortcodes = array();
    DesignerShortcodes::$shortcodes['slide'] = 'slide_styling';
    $content = DesignerShortcodes::buildShortCodes($content);

    DesignerShortcodes::$shortcodes = $orig_shortcode_tags;

    $before = '';
    $after = '';
    if ('no' === $wide_slides) {
        $before = '<div class="bd-container-inner">';
        $after = '</div>';
    }
    $content_indicators_style  = '';
    if ('yes' === $indicators) {
        $parts = explode(' ', $indicators_position);
        $align =  isset($parts[0]) ? $parts[0] : 'left';
        $vAlign = isset($parts[1]) ? str_replace('center', 'middle', $parts[1]) : 'top';
        $content_indicators_style = <<<EOL
    #$id .slider-indicators-wrapper {
        text-align: $align;
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        white-space: nowrap;
        pointer-events: none;
        z-index:20;
    }
    #$id .slider-indicators-wrapper:before {
        content: '';
        display: inline-block;
        height: 100%;
        width: 0;
        vertical-align: $vAlign;
    }
    #$id .slider-indicators-wrapper .bd-indicators {
        pointer-events: auto;
        vertical-align: $vAlign;
    }
EOL;
        $indicators = array();
        for($i = 0; $i < $countSlides; $i++) {
            $indicators[] = sprintf('<li class="bd-menuitem-11' . (0 === $i ? ' active' : '') . '"><a href="#" data-target="#%s" data-slide-to="%s"> </a></li>', $id, $i);
        }
        $content_indicators = sprintf(
            '<div class="slider-indicators-wrapper"><ol class=" bd-indicators">%s</ol></div>',
            implode("", $indicators)
        );
        $before =  ('yes' === $wide_indicators) ?  $content_indicators . $before : $before . $content_indicators;
    }

    if ('yes' === $carousel) {
        $content_nav = <<<EOL
    <div class="left-button">
        <a class=" bd-carousel" href="#" role="button" data-slide="prev">
            <span class="bd-icon-15"></span>
        </a>
    </div>
    <div class="right-button">
        <a class=" bd-carousel" href="#" role="button" data-slide="next">
            <span class="bd-icon-15"></span>
        </a>
    </div>
EOL;
        $after =  ('yes' === $wide_carousel) ? $content_nav . $after : $after . $content_nav;
    }
    return <<<EOL
<style>
    #$id {
        $css
    }
    #$id .carousel-inner {
        height: 100%;
    }
    #$id .item {
        height:100%;
        background-size: cover;
        background-position: center top;
    }
$content_indicators_style
    #$id .left-button {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 15;
    }
    #$id .right-button {
        position: absolute;
        top: 0;
        bottom: 0;
        right: 0;
        z-index: 15;
    }
</style>
<div id="$id" class="carousel slide">
    $before
    <div class="carousel-inner">$content</div>
    $after
</div>
<script>
    if ('undefined' !== typeof initSlider) {
        initSlider('#$id', 'left-button', 'right-button', '.bd-carousel', '.bd-indicators', '$interval');
    }
</script>
EOL;
}

function slide_styling($atts, $content='') {
    extract(DesignerShortCodes::atts(array(
        'image' => '',
        'link' => '',
        'linktarget' => '',
        'css' => '',
        'title' => ''
    ), $atts));
    if ('' !== $image) {
        if (preg_match("/^https?:\/\//i", $image)) {
            $css = 'background-image:url(' . $image . ');' . $css;
        } else {
            $css = 'background-image:url(' . JURI::root() . $image . ');' . $css;
        }
    }

    $data_attr = '';
    if ('' !== $link) {
        $data_attr = ' data-url="' . $link . '"';
        if ('' !== $linktarget) {
            $data_attr .= ' data-target="' . $linktarget . '"';
        }
    }
    return sprintf('<div class="item" title="%s" style="%s" %s>%s</div>', $title, $css, $data_attr, $content);
}

DesignerShortcodes::$shortcodes['slider'] = 'slider_styling';

class ThemeColumns {
    /*
        [column width_lg="6" width="8" width_sm="12" width_xs="6"] Your Content Here [/column]
        [one_half last] 1/2 [/one_half]
        [one_third] 1/3 [/one_third]
        [two_third last] 2/3 [/two_third]
        [one_fourth]  1/4 [/one_fourth]
        [three_fourth] 3/4 [/three_fourth]
    */
    public static function column($atts, $content = '') {
        extract(DesignerShortcodes::atts(array(
            'width_lg' => '',
            'width' => '4',
            'width_sm' => '',
            'width_xs' => '',
            'css' => '',
            'auto_height' => '',
            'vertical_align' => 'top',
            'collapse_spacing' => '0',
            'last' => false
        ), $atts));

        if (false === $last && is_array($atts) && !empty($atts)) {
            foreach( $atts as $key => $value) {
                if (is_numeric($key) && 'last' ===  $value) {
                    $last = true;
                    break;
                }
            }
        }

        $classes = array();
        if (intval($width_lg) > 0) {
            $classes[] = 'col-lg-' . $width_lg;
        }
        if (intval($width) > 0) {
            $classes[] = 'col-md-' . $width;
        }
        if (intval($width_sm) > 0) {
            $classes[] = 'col-sm-' . $width_sm;
        }
        if (intval($width_xs) > 0) {
            $classes[] = 'col-xs-' . $width_xs;
        }

        $row_classes = array();
        if ($auto_height === 'yes') {
            $row_classes[] = 'bd-row-flex';
            if ($vertical_align) {
                $row_classes[] = 'bd-row-align-' . $vertical_align;
            }
        }
        if ($collapse_spacing === 'yes') {
            $row_classes[] = 'bd-collapsed-gutter';
        }

        $result = '<!--Column="' . implode(' ', $classes) . '"-->';
        if ($css !== '') {
            $result .= '<div style="' . $css .'">' . $content . '</div>';
        } else {
            $result .=  $content;
        }
        $result = '<!--Column<' . implode(' ', $row_classes) . '>="' . implode(' ', $classes) . '"-->';
        $result .= '<div class="bd-layoutcolumn-shortcode" style="' . $css .'"><div class="bd-vertical-align-wrapper">' . $content . '</div></div>';
        $result .=  '<!--/Column' . (false !== $last ? 'Last' : '') .  '-->';

        return $result;
    }

    public static function one_half($atts, $content = '') {
        $atts['width'] = "12";
        return ThemeColumns::column($atts, $content);
    }

    public static function one_third($atts, $content = '') {
        $atts['width'] = "8";
        return ThemeColumns::column($atts, $content);
    }

    public static function two_third($atts, $content = '') {
        $atts['width'] = "16";
        return ThemeColumns::column($atts, $content);
    }

    public static function one_fourth($atts, $content = '') {
        $atts['width'] = "6";
        return ThemeColumns::column($atts, $content);
    }

    public static function three_fourth($atts, $content = '') {
        $atts['width'] = "18";
        return ThemeColumns::column($atts, $content);
    }

    public static $row = false;
    public static function filter($content) {

        $orig_shortcode_tags = DesignerShortcodes::$shortcodes;

        DesignerShortcodes::$shortcodes = array();
        DesignerShortcodes::$shortcodes['column'] = 'ThemeColumns::column';
        DesignerShortcodes::$shortcodes['one_half'] = 'ThemeColumns::one_half';
        DesignerShortcodes::$shortcodes['one_third'] = 'ThemeColumns::one_third';
        DesignerShortcodes::$shortcodes['two_third'] = 'ThemeColumns::two_third';
        DesignerShortcodes::$shortcodes['one_fourth'] = 'ThemeColumns::one_fourth';
        DesignerShortcodes::$shortcodes['three_fourth'] = 'ThemeColumns::three_fourth';
        $content = DesignerShortcodes::buildShortCodes($content);

        DesignerShortcodes::$shortcodes = $orig_shortcode_tags;

        ThemeColumns::$row = false;
        $content = preg_replace('/(<!--\/Column)(?:Last){0,1}(-->)(?!.*<!--\/Column)/s', '$1Last$2', $content, 1);
        return  preg_replace_callback('|<!--Column<([^>]*?)>(="[^"]*?")-->([\s\S]*?)<!--\/Column(Last){0,1}-->|s','ThemeColumns::callback', $content);
    }

    public static function callback($matches)
    {
        $result = '';
        if (!ThemeColumns::$row) {
            $result .= '<div class="row ' . $matches[1] . '">';
            ThemeColumns::$row = true;
        }
        $result .= '<div class'.$matches[2] . '>'.$matches[3] .'</div>';
        if (isset($matches[4])) {
            $result .= '</div>';
            ThemeColumns::$row = false;
        }
        return $result;
    }
}

DesignerShortcodes::$filters[] = 'ThemeColumns::filter';