<?php
/**
 * @package wp-categories-statistics
 * @version 1.0
 */
/*
Plugin Name: WP Categories Statistics
Plugin URI: https://github.com/dfinchenko/wp-categories-statistics
Description: Wordpress tools for working with categories statistics.
Author: Denys Finchenko
Version: 1.0
Author URI: https://github.com/dfinchenko
*/

add_action( 'admin_menu', 'cats_stats_page_create' );

function cats_stats_page_create() {
    $page_title = 'WP Categories Statistics';
    $menu_title = 'WP Categories Statistics';
    $capability = 'edit_posts';
    $menu_slug = 'wp_cats_stats';
    $function = 'wp_cats_stats';
    $icon_url = '';
    $position = 24;

    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}

function wp_cats_stats() {
    $args_cats = array(
    'orderby' => 'name',
    'taxonomy' => 'category',
    'hide_empty ' => 0,
    );
    $all_cats = get_categories( $args_cats );
    echo '<h1>WP Categories Statistics</h1>';
?>

<form method="post" action="">
<?php
if( $all_cats ){ ?>
    <select name="cat[]">
	<?php foreach( $all_cats as $cat ){ ?>

<option value="<?php echo $cat->term_id; ?>"><?php echo $cat->name; ?></option>
        <?php } ?>
</select>
<?php } ?>
<input type="date" name="posts-date" value="<?php echo $_POST['posts-date']; ?>" />
<input type="submit" name="submit" value="OK" />
</form>

<?php

if(isset( $_POST['submit'] )){
    $today = getdate( strtotime( $_POST['posts-date'] ));
    $args_posts = array(
	'showposts' => -1,
	'orderby' => 'ASC',
    'cat' => $_POST['cat'][0],
    'post_status' => 'publish',
    'date_query' => array(
		array(
			'year'  => $today['year'],
			'month' => $today['mon'],
			'day'   => $today['mday'],
		),
	),
);

$query = new WP_Query( $args_posts );

echo '<h2>Total Published Posts In Selected Category Matches Your Criteria:</h2><br />';

if ( $query->have_posts() ) {
	while ( $query->have_posts() ) {
		$query->the_post();
		echo '<li>' . get_the_title() . '</li>';
	}
    echo '<br /><h3>Total Counts Posts Matches Your Criteria - '.count($query).'</h3>';
} else {
_e( '<h3>No posts in this category matches your criteria</h3>', 'textdomain' );
}

wp_reset_postdata();

}

}
