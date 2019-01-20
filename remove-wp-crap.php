<?php

/*
Plugin Name: Remove WP Crap
Plugin URI: https://github.com/domlip94/remove-wp-crap
Description: Removes all the crap from Wordpress outputs 
Version: 0.0.1
Author: Dominic Lipscombe
Author URI: http://github.com/domlip94
Text Domain: remove-wp-crap
Network: true
*/

/**
 * Disable the emoji's
 */
function disable_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('tiny_mce_plugins', 'disable_emojis_tinymce');
    add_filter('wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2);
}
add_action('init', 'disable_emojis');
/**
 * Filter function used to remove the tinymce emoji plugin.
 *
 * @param array $plugins
 * @return array Difference betwen the two arrays
 */
function disable_emojis_tinymce($plugins)
{
    if (is_array($plugins)) {
        return array_diff($plugins, array(
            'wpemoji'
        ));
    } else {
        return array();
    }
}
/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param array $urls URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array Difference betwen the two arrays.
 */
function disable_emojis_remove_dns_prefetch($urls, $relation_type)
{
    if ('dns-prefetch' == $relation_type) {
        /** This filter is documented in wp-includes/formatting.php */
        $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');
        $urls          = array_diff($urls, array(
            $emoji_svg_url
        ));
    }
    return $urls;
}
remove_action('wp_head', 'wlwmanifest_link');
add_filter('xmlrpc_enabled', '__return_false'); // Hide xmlrpc.php in HTTP 
add_filter('wp_headers', function($headers)
{
    unset($headers['X-Pingback']);
    return $headers;
});
remove_action('wp_head', 'feed_links', 2);
add_filter('post_comments_feed_link', function()
{
    return null;
});
function crunchify_remove_version()
{
    return '';
}
add_filter('the_generator', 'crunchify_remove_version');

remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
remove_action('template_redirect', 'rest_output_link_header', 11, 0);

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_shortlink_wp_head');

function crunchify_cleanup_query_string($src)
{
    $parts = explode('?', $src);
    return $parts[0];
}
add_filter('script_loader_src', 'crunchify_cleanup_query_string', 15, 1);
add_filter('style_loader_src', 'crunchify_cleanup_query_string', 15, 1);
function fb_filter_query($query, $error = true)
{
    
    if (is_search()) {
        $query->is_search     = false;
        $query->query_vars[s] = false;
        $query->query[s]      = false;
        
        // to error
        if ($error == true)
            $query->is_404 = true;
    }
}
add_action('parse_query', 'fb_filter_query');
add_filter('get_search_form', create_function('$a', "return null;"));
// ******************** Clean up WordPress Header END ********************** //
