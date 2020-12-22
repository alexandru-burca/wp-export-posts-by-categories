<?php
/**
 * Plugin Name: Export Posts by Categories CSV
 * Description: Export posts from selected categories
 * Version: 0.0.1
 * Author: Alex Burca
 * Author URI: https://www.linkedin.com/in/burca-alexandru/
 * Text Domain: export-posts-by-cat-csv
 */

CLASS EXPORT_BULK_POSTS_BY_CATEGORIES_CSV{
    function __construct(){
        add_filter( 'bulk_actions-edit-category', array($this, 'register_bulk_action'));
        add_filter( 'handle_bulk_actions-edit-category', array($this, 'handle_bulk_action'), 10, 3 );
    }

    public function register_bulk_action($bulk_actions){
        $bulk_actions['exports_posts_by_cat'] = __('Export Posts', 'export-posts-by-cat-csv');
        return $bulk_actions;
    }

    public function handle_bulk_action($redirect_to, $doaction, $cat_ids ){
        if ( $doaction !== 'exports_posts_by_cat' ) {
            return $redirect_to;
        }
        $query = new WP_Query( array(
            'post_type' => 'post',
            'posts_per_page'=>-1,
            'category__in' => $cat_ids
        ));

        if ( $query->have_posts() ) {
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment; filename="posts.csv"' );
            header( 'Pragma: no-cache' );
            header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" );
            $out = fopen('php://output', 'w');
            fputcsv($out, array('Title', 'Excerpt', 'Categories', 'Date', 'Status'), ',');

            while ( $query->have_posts() ) { $query->the_post();
                $categories = get_the_category();
                $categoriesAsArray = array();
                foreach ($categories as $category){
                    $categoriesAsArray[] = $category->name;
                }
                fputcsv($out, array(
                    get_the_title(),
                    get_the_excerpt(),
                    implode( $categoriesAsArray, ', ' ),
                    get_the_date(),
                    get_post_status()
                ), ',');
            }
            exit();
        }
        return $redirect_to;
    }
}
new EXPORT_BULK_POSTS_BY_CATEGORIES_CSV;