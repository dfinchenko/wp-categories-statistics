<?php
/**
 * @package WP-categories-statistics
 * @version 1.0
 */
/*
Plugin Name: WP categories statistics
Plugin URI: https://github.com/dfinchenko/wp-categories-statistics
Description: Wordpress tools for working with categories statistics.
Author: Denys Finchenko
Version: 1.0
Author URI: https://github.com/dfinchenko
*/

add_action('admin_menu', 'awesome_page_create');

function awesome_page_create() {
    $page_title = 'WP categories statistics';
    $menu_title = 'WP categories statistics';
    $capability = 'edit_posts';
    $menu_slug = 'wp_cats_stats';
    $function = 'wp_cats_stats';
    $icon_url = '';
    $position = 24;

    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}

function wp_cats_stats() {
    echo '<h1>My Page!!!</h1>';
}
