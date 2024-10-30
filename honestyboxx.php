<?php
/*
Plugin Name: HonestyBoxx
Plugin URI: http://wordpress.org/extend/plugins/honestyboxx/
Description: Displays your HonestyBoxx widget on your WordPress site.
Version: 1.0.1
Author: HonestyBoxx
Author URI: http://honestyboxx.com
License: GPL2
*/
/*  Copyright 2013  HonestyBoxx  (email : help@honestyboxx.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('HONESTYBOXX_VERSION', "1.0.1");
define('HONESTYBOXX_PLUGIN_URL', plugin_dir_url( __FILE__ ));

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit;
}

if (is_admin()){ // admin actions
    add_action('admin_menu', array('HonestyBoxxPluginAdmin', 'options_menu'));
    add_action('admin_init', array('HonestyBoxxPluginAdmin', 'init'));
}
else {
    add_filter('the_content', array('HonestyBoxxPlugin', 'render_inline_filter'));
    add_action('loop_end', array('HonestyBoxxPlugin', 'render_inline'));
    add_action('wp_footer', array('HonestyBoxxPlugin', 'render_flyout'));
}
    
add_action('init', array('HonestyBoxxPlugin', 'init'));

class HonestyBoxxPlugin {
    const inlineMarkup = '<div id="hb-widget-container"><a href="http://www.honestyboxx.com">Expert Advice powered by HonestyBoxx</a></div><script id="hb-widget-script" type="text/javascript" async src="//widget.honestyboxx.com/scripts/widget/inlinewidget.js" data-token="[Token]"></script>';
    const flyoutMarkup = '<div id="hb-flyout-container"><a href="http://www.honestyboxx.com">Expert Advice powered by HonestyBoxx</a></div><script id="hb-flyout-script" type="text/javascript" async src="//widget.honestyboxx.com/scripts/widget/flyoutwidget.js" data-token="[Token]"></script>';

    function init() {
        // Using add_option because it will only add an option if it doesn't already exist
        $options['widget_token'] = '';
        $options['widget_style'] = 'Inline';
        $options['display_location'] = 'Everywhere';
        add_option('honestyboxx_options', $options);
    }

    function render_inline() { 
        if(HonestyBoxxPlugin::display_inline()) {
            // Used for rendering at the end of the loop
            if(!is_page() && !is_single() && HonestyBoxxPlugin::display_everywhere()) {
                echo str_replace('[Token]', HonestyBoxxPlugin::get_token(), HonestyBoxxPlugin::inlineMarkup);
            }
        }
    }

    function render_inline_filter($content) {
        if(HonestyBoxxPlugin::display_inline()) {
            if(HonestyBoxxPlugin::display_everywhere() || (is_page() && HonestyBoxxPlugin::display_in_page()) || (is_single() && HonestyBoxxPlugin::display_in_post())) {
                // Used for Pages and Posts
                if(is_page() || is_single()) {
                    $content .= str_replace('[Token]', HonestyBoxxPlugin::get_token(), HonestyBoxxPlugin::inlineMarkup);
                }
            }
        }

        return $content;
    }

    function render_flyout() {
        if(HonestyBoxxPlugin::display_flyout()) {
            if (HonestyBoxxPlugin::display_everywhere() || (is_page() && HonestyBoxxPlugin::display_in_page()) || (is_single() && HonestyBoxxPlugin::display_in_post())) {
                // Suppress according to options
                echo str_replace('[Token]', HonestyBoxxPlugin::get_token(), HonestyBoxxPlugin::flyoutMarkup);
            }
        }
    }

    function display_in_page() {
        $options = get_option('honestyboxx_options');
        return $options['display_location'] == 'Pages' || $options['display_location'] == 'PagesAndPosts';
    }

    function display_in_post() {
        $options = get_option('honestyboxx_options');
        return $options['display_location'] == 'Posts' || $options['display_location'] == 'PagesAndPosts';
    }

    function display_everywhere() {
        $options = get_option('honestyboxx_options');
        return $options['display_location'] == 'Everywhere';
    }

    function display_inline() {
        $options = get_option('honestyboxx_options');
        return $options['widget_style'] == 'Inline' && $options['widget_token'] != '';
    }

    function display_flyout() {
        $options = get_option('honestyboxx_options');
        return $options['widget_style'] == 'Flyout' && $options['widget_token'] != '';
    }

    function get_token() {
        $options = get_option('honestyboxx_options');
        return $options['widget_token'];
    }
}

class HonestyBoxxPluginAdmin {
    function init() {
        register_setting( 'honestyboxx_options', 'honestyboxx_options', array('HonestyBoxxPluginAdmin', 'options_validate'));
        add_settings_section('honestyboxx_main', 'Main Settings', array('HonestyBoxxPluginAdmin', 'options_section_text'), 'honestyboxx');
        add_settings_field('honestyboxx_widget_token', 'Widget Token', array('HonestyBoxxPluginAdmin', 'widget_token_control'), 'honestyboxx', 'honestyboxx_main');
        add_settings_field('honestyboxx_widget_style', 'Widget Style', array('HonestyBoxxPluginAdmin', 'widget_style_control'), 'honestyboxx', 'honestyboxx_main');
        add_settings_field('honestyboxx_display_location', 'Display Widget On', array('HonestyBoxxPluginAdmin', 'display_location_control'), 'honestyboxx', 'honestyboxx_main');
    }

    function options_menu() {
        add_options_page('HonestyBoxx', 'HonestyBoxx', 'manage_options', 'honestyboxx_options', array('HonestyBoxxPluginAdmin', 'options_form'));
    }

    function options_section_text() {
        echo '<p>Please configure the HonestyBoxx widget with the options below.</p>';
    }

    function widget_token_control() {
        $options = get_option('honestyboxx_options');
        echo "<input id='honestyboxx_widget_token' name='honestyboxx_options[widget_token]' size='40' type='text' value='{$options['widget_token']}' />";
    }

    function widget_style_control() {
        $options = get_option('honestyboxx_options'); ?>
        <label><input type="radio" id="honestyboxx_widget_style_inline" name="honestyboxx_options[widget_style]" value="Inline" <?php if($options['widget_style'] == 'Inline') { ?> checked="checked" <?php } ?> /> Inline<br />
        <img src="<?php echo HONESTYBOXX_PLUGIN_URL ?>/images/inlinewidget-preview.png" /></label><br />
        <label><input type="radio" id="honestyboxx_widget_style_flyout" name="honestyboxx_options[widget_style]" value="Flyout" <?php if($options['widget_style'] == 'Flyout') { ?> checked="checked" <?php } ?> /> Flyout<br />
        <img src="<?php echo HONESTYBOXX_PLUGIN_URL ?>/images/fixedwidget-preview.png" /></label>
        <?php
    }

    function display_location_control() {
        $options = get_option('honestyboxx_options'); ?>
        <label><input type="radio" id="honestyboxx_display_location_posts" name="honestyboxx_options[display_location]" value="Posts" <?php if($options['display_location'] == 'Posts') { ?> checked="checked" <?php } ?> /> Posts</label><br />
        <label><input type="radio" id="honestyboxx_display_location_pages" name="honestyboxx_options[display_location]" value="Pages" <?php if($options['display_location'] == 'Pages') { ?> checked="checked" <?php } ?> /> Pages</label><br />
        <label><input type="radio" id="honestyboxx_display_location_pagesandposts" name="honestyboxx_options[display_location]" value="PagesAndPosts" <?php if($options['display_location'] == 'PagesAndPosts') { ?> checked="checked" <?php } ?> /> Pages and Posts</label><br />
        <label><input type="radio" id="honestyboxx_display_location_everywhere" name="honestyboxx_options[display_location]" value="Everywhere" <?php if($options['display_location'] == 'Everywhere') { ?> checked="checked" <?php } ?> /> Everywhere</label>
        <?php
    }

    function options_form() {
        if ( !current_user_can( 'manage_options' ) )  {
		    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	    }
        
	    include_once(dirname( __FILE__ ) . '/options.php');
    }

    function options_validate($input) {
        $newinput['widget_token'] = trim($input['widget_token']);
        if(!preg_match('/^[a-z0-9]{32}$/i', $newinput['widget_token'])) {
            $newinput['widget_token'] = '';
        }

        $newinput['widget_style'] = trim($input['widget_style']);
        if($newinput['widget_style'] != 'Inline' && $newinput['widget_style'] != 'Flyout') {
            $newinput['widget_style'] = 'Inline';
        }

        $newinput['display_location'] = trim($input['display_location']);
        if($newinput['display_location'] != 'Posts' && $newinput['display_location'] != 'Pages' && $newinput['display_location'] != 'PagesAndPosts' && $newinput['display_location'] != 'Everywhere') {
            $newinput['widget_style'] = 'Everywhere';
        }

        return $newinput;
    }
}

?>