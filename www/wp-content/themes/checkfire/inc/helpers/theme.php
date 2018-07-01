<?php

/*
 * Custom theme functions
 * @v 1.0.0
 * @author: Noureddine Latreche
 *
 */

class Theme
{
    /*
     * get posts using main wp loop
     */
    public  function theme_main_loop()
    {

        $r = array();
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                //
                $r[] = array(
                    'title' => get_the_title(),
                    'content' => get_the_content(),
                    'thumbnail' => get_the_post_thumbnail_url()
                );

                //
            } // end while
        }
        wp_reset_query();

        return $r;


    }

    /*
     * get wp query
     */

    public static function theme_custom_loop($post_type,$posts_per_page=-1, $tax = '', $term = '', $exclude_tax = '', $exclude_term = '', $relation = 'AND')
    {


        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => $posts_per_page,
            'orderby' => array(
                'ID' => 'DESC',
            ),

        );

        if ($tax != '' && $term != '' && $exclude_tax == '') {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => $tax,
                    'field' => 'slug',
                    'terms' => array($term),

                )
            );

        }
        if ($tax != '' && $term != '' && $exclude_tax != '') {
            $args['tax_query'] = array(
                'relation' => $relation,
                array(
                    'taxonomy' => $tax,
                    'field' => 'slug',
                    'terms' => array($term),

                ),
                array(
                    'taxonomy' => $exclude_tax,
                    'field' => 'slug',
                    'terms' => array($exclude_term),
                ),
            );

        }

        $post = array();


        $query = new WP_Query($args);

        while ($query->have_posts()) {
            $query->the_post();

            $post[] = array(
                'ID' => get_the_ID(),
                'title' => get_the_title(),
                'content' => get_the_content(),
                'thumbnail' => get_the_post_thumbnail_url(),
                'date'=> get_the_date('Y.m.d'),
                'author'=> get_the_author()
            );


        }
        wp_reset_query();

        return $post;


    }

    /*
     * get terms
     */
    public static function theme_terms($ID, $tax )
    {
        global $post;
        $term_result = array();

        if( !is_archive() ):

            if($ID !='') {

            /*
             * the terms of the post (usually used inside post loop)
             */
                $terms = wp_get_post_terms($ID, $tax, array("fields" => "all"));
                if($terms) {
                    foreach ($terms as $term) {
                        $term_result[] = array(
                            'ID' => $term->term_id,
                            'name' => $term->name,
                            'permalink' => get_term_link($term),
                            'slug' => $term->slug,
                            'count' => $term->count

                        );

                    }
                }
            }else {

            /*
             * get terms based on tax
             */
                $terms = get_terms( $tax, 'orderby=count&hide_empty=1' );
                if($terms) {
                    foreach ($terms as $term) {
                        $term_result[] = array(
                            'ID' => $term->term_id,
                            'name' => $term->name,
                            'permalink' => get_term_link($term),
                            'slug' => $term->slug,
                            'count' => $term->count

                        );
                    }
                }

            }
        else:
            $queried_object = get_queried_object();
            $tax = $queried_object->taxonomy;
            $term_name = $queried_object->name;
            $term_id = $queried_object->term_id;
            $term_slug = $queried_object->slug;
            $term_description = $queried_object->description;

            /*
             * the current archive term detials
             */

            $term_result['archive_tax']= $tax;
            $term_result['archive_term']= $term_name;
            $term_result['archive_id']= $term_id;
            $term_result['archive_slug']= $term_slug;
            $term_result['archive_description']= $term_description;

            /*
             * the rest of archive termS detials
             */


            $terms = get_terms( $tax, 'orderby=count&hide_empty=1' );
            if($terms) {
                foreach ($terms as $term) {
                    $term_result[] = array(
                        'ID' => $term->term_id,
                        'name' => $term->name,
                        'permalink' => get_term_link($term),
                        'slug' => $term->slug,
                        'count' => $term->count

                    );
                }
            }


        endif;


        return $term_result;

    }

    /*
     * get acf repeater
     */
    public static function theme_carousel($field_name)
    {
        $images = '';
        if ( function_exists( 'get_field' ) ) {

                $result = '';
                if ($images = get_sub_field($field_name)) :
                endif;

            return $images;

        }


    }
}

$theme = new Theme;


