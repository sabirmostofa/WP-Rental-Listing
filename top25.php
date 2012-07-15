<?php
/**
 * WARNING: This file is part of the core Genesis framework. DO NOT edit
 * this file under any circumstances. Please do all modifications
 * in the form of a child theme.
 *
 * Template Name: Top25
 * This file handles archives pages.
 *
 * @package Genesis
 */

/** Remove standard post content output **/
remove_action( 'genesis_post_content', 'genesis_do_post_content' );
add_action('genesis_before_post_title','add_grading_before_title_common');

global $wpdb,$wp_query,$wpVoteRate;
$ar = $wpdb -> get_col("select post_id from {$wpdb -> prefix}vote_rate_average order by average_grade limit 25");

/*
foreach($ar as $single){
	echo $single, ':', $wpVoteRate -> get_av_grade($single), '<br/>';
	}
*/
unset($wp_query);
$wp_query = new WP_Query( array( 'meta_key'=>'post_av_rating' ,'orderby' => 'meta_value', 'order' =>'ASC', 'posts_per_page' => 25 ) );

genesis();

