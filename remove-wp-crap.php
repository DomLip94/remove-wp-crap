<?php
/*
Plugin Name: Remove WP Crap
Plugin URI: https://github.com/domlip94/remove-wp-crap
Description: Removes all the crap from Wordpress outputs
Version: 0.0.3
Author: Dominic Lipscombe
Author URI: http://github.com/domlip94
Text Domain: remove-wp-crap
Network: true
*/
class DLRemoveWpCrap {
        public function disableEmojis()
        {
                remove_action('wp_head', 'print_emoji_detection_script', 7);
                remove_action('admin_print_scripts', 'print_emoji_detection_script');
                remove_action('wp_print_styles', 'print_emoji_styles');
                remove_action('admin_print_styles', 'print_emoji_styles');
                remove_filter('the_content_feed', 'wp_staticize_emoji');
                remove_filter('comment_text_rss', 'wp_staticize_emoji');
                remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
                add_filter('tiny_mce_plugins', ['DLRemoveWpCrap','disableEmojisTinymce']);
                add_filter('wp_resource_hints', ['DLRemoveWpCrap','disableEmojisRemoveDnsPrefetch'], 10, 2);
        }
        public function disableEmojisTinymce($plugins)
        {
                if (is_array($plugins)) {
                        return array_diff($plugins, array(
                                'wpemoji'
                        ));
                } else {
                        return array();
                }
        }
        public function disableEmojisRemoveDnsPrefetch($urls, $relation_type)
        {
                if ('dns-prefetch' == $relation_type) {
                        $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');
                        $urls = array_diff($urls, array(
                                $emoji_svg_url
                        ));
                }
                return $urls;
        }

        public function removeVersion()
        {
                return '';
        }
        public function cleanupQueryString($src)
        {
                $parts = explode('?', $src);
                return $parts[0];
        }
        public function fbFilterQuery($query, $error = true)
        {
                if (is_search()) {
                        $query->is_search = false;
                        $query->query_vars[s] = false;
                        $query->query[s] = false;

                        // to error
                        if ($error == true)
                                $query->is_404 = true;
                }
        }
        public function returnNull()
        {
                return null;
        }

        public function unsetPingback()
        {
                unset($headers['X-Pingback']);
                return $headers;
        }

        public function exec()
        {
                add_filter('wp_headers', ['DLRemoveWpCrap', 'unsetPingback']);
                add_filter('post_comments_feed_link', ['DLRemoveWpCrap', 'returnNull']);
                add_filter('script_loader_src', ['DLRemoveWpCrap','cleanupQueryString'], 15, 1);
                add_filter('style_loader_src', ['DLRemoveWpCrap','cleanupQueryString'], 15, 1);
                add_filter('xmlrpc_enabled', '__return_false');
                add_filter('get_search_form', create_function('$a', "return null;"));
                add_filter('the_generator', ['DLRemoveWpCrap','removeVersion']);
                add_action('init', ['DLRemoveWpCrap','disableEmojis']);
                add_action('parse_query', ['DLRemoveWpCrap','fbFilterQuery']);
                remove_action('wp_head', 'rest_output_link_wp_head', 10);
                remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
                remove_action('template_redirect', 'rest_output_link_header', 11, 0);
                remove_action('wp_head', 'rsd_link');
                remove_action('wp_head', 'wlwmanifest_link');
                remove_action('wp_head', 'wp_shortlink_wp_head');
                remove_action('wp_head', 'wlwmanifest_link');
                remove_action('wp_head', 'feed_links', 2);
        }
}
DLRemoveWpCrap::exec();
