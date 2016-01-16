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
function cats_stats_scripts() {
    wp_register_script('bootstrap-js', plugins_url('/lib/js/bootstrap.min.js', __FILE__));
    wp_register_style('bootstrap-styles',  plugins_url('/lib/css/bootstrap.min.css', __FILE__));
    wp_register_script('google_charts_api', 'https://www.google.com/jsapi');
    wp_enqueue_script('google_charts_api');
}

add_action('admin_init', 'cats_stats_scripts');

function cats_stats_plugin_scripts() {
    wp_enqueue_script('bootstrap-js');
}

function cats_stats_plugin_styles() {
    wp_enqueue_style('bootstrap-styles');
}

function cats_stats_page_create() {
    $page_title = 'WP Categories Statistics';
    $menu_title = 'WP Categories Statistics';
    $capability = 'edit_posts';
    $menu_slug = 'wp_cats_stats';
    $function = 'wp_cats_stats_callback';
    $icon_url = 'dashicons-chart-pie';
    $position = 24;
    $page = add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
    $sub_page = add_submenu_page($menu_slug, 'Categories Statistics By Criteria', 'Categories Statistics By Criteria', 'manage_options', 'wp_cats_stats_by_criteria', 'wp_cats_stats_by_criteria_callback');

    add_action('admin_print_scripts-' . $page, 'cats_stats_plugin_scripts');
    add_action('admin_print_styles-' . $page, 'cats_stats_plugin_styles');
    add_action('admin_print_scripts-' . $sub_page, 'cats_stats_plugin_scripts');
    add_action('admin_print_styles-' . $sub_page, 'cats_stats_plugin_styles');
}

add_action('admin_menu', 'cats_stats_page_create');


function wp_cats_stats() {
    $args_cats = array(
        'orderby' => 'name',
        'taxonomy' => 'category',
        'hide_empty ' => 0,
    );
    $all_cats = get_categories($args_cats);
    return $all_cats;
}

function get_data_arrays_for_statistics($jsons = NULL) {
   $all_cats = wp_cats_stats();
    $rows_arrays = array();
    $options_arrays = array();
    $all_arrays = array();

    foreach($all_cats as $cat){
        $rows_arrays[] = array((string)$cat->name, intval($cat->category_count));
        $options_arrays[$cat->term_id] = $cat->name;
    }

    if('json' === $jsons) {
        $rows_data = json_encode($rows_arrays);
        return $rows_data;
    } else {
        return $options_arrays;
    }

 }

function get_categories_statistics_results() {
    if(isset($_POST['submit'])) {
        $today = getdate(strtotime($_POST['posts-date']));
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

        $query = new WP_Query($args_posts);
        $categories_statistics_results = '<div class="col-md-10 col-md-offset-2"><h3>' . __('Total Published Posts In Selected Category Matches Your Criteria', 'wp-categories-statistics') . '</h3></div>';
        if ($query->have_posts()) {
            $categories_statistics_results .= '<table class="table table-bordered"><tr><td><b>Posts Titles</b></td><td><b>Posts Authors</b></td><td><b>Authors Emails</b></td></tr>';

	       while ($query->have_posts()) {
		      $query->the_post();
		      $categories_statistics_results .= '<tr><td><a href="'. get_the_permalink() .'" target="_blank">' . get_the_title() . '</a></td><td>' . get_the_author_meta('user_login') . '</td><td>' . get_the_author_meta('user_email') . '</td></tr>';
	       }
          $categories_statistics_results .= '</table><div class="col-md-4 col-md-offset-8"><label>Total Counts Posts Matches Your Criteria: '.count($query).'</label></div>';

        } else {
            $categories_statistics_results .= '<label>' . __('No posts in this category matches your criteria', 'wp-categories-statistics') . '</label>';
        }
        wp_reset_postdata();
        return $categories_statistics_results;
    }
}

function wp_cats_stats_by_criteria_callback() { ?>

<script type="text/javascript">
      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      // Callback that creates and populates a data table,
      // instantiates the pie chart, passes in the data and
      // draws it.
      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Categories');
        data.addColumn('number', 'Counts Of Posts');
        data.addRows(<?php echo get_data_arrays_for_statistics('json'); ?>);

        // Set chart options
        var options = {'title':'',
                       'width':800,
                       is3D: 'true',
                       'height':400};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.PieChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
</script>
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
  <h1>WP Categories Statistics</h1>
            </div>

        </div>
    <div class="row">
    <div class="col-md-3">
    <br/><br/><br/><br/>
        <label>Enter Your Criteria</label>
    <form method="post" action="" >
        <select name="cat[]" class="form-control">
            <?php
            $current_cat = (!empty($_POST['cat'][0])) ? $_POST['cat'][0] : 1;
            $options = get_data_arrays_for_statistics();

            foreach($options as $key => $value) { ?>

             <option value="<?php echo $key; ?>" <?php selected($key, $current_cat); ?>><?php echo $value; ?></option>

            <?php } ?>
    </select><br/>

<input type="date" name="posts-date" value="<?php echo $_POST['posts-date']; ?>" class="btn btn-default form-control" /><br/><br/>
<input type="submit" name="submit" value="OK" class="btn btn-primary" />
</form>
</div>
     <div class="col-md-9"> <div id="chart_div"></div>
    </div>
</div>
     <div class="row">

<?php if(isset($_POST['submit'])) { echo get_categories_statistics_results(); } ?>

    </div>
<?php }

function wp_cats_stats_callback() {
echo '<div class="row"><div class="col-md-11 col-md-offset-1"><h2>General statistics with different charts coming soon!</h2></div></div>';
}
?>
