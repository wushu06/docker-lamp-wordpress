<?php
// 0. Set up our custom option pages
if( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	=> 'Theme Settings',
		'menu_title'	=> 'Theme Settings',
		'menu_slug' 	=> 'theme-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));

}

/*if( ! defined( 'WP_DEBUG' ) || WP_DEBUG == false ) {

    // 1. Hide ACF field group menu item
    add_filter('acf/settings/show_admin', '__return_false');
}*/

// 2. Save ACF dynamically
/*add_filter( 'acf/settings/save_json', 'my_acf_json_save_point' );

function my_acf_json_save_point( $path ) {
    // update path
    $path = get_template_directory() . '/inc/acf';

    return $path;
}*/

// 3. Set ACF to load from JSON
/*add_filter( 'acf/settings/load_json', 'my_acf_json_load_point' );

function my_acf_json_load_point( $paths ) {
    // remove original path (optional)
    unset($paths[0]);

    // append path
    $paths[] = get_template_directory() . '/inc/acf';

    // return
    return $paths;
}*/

// 4. Update our Flexible Content block titles
add_filter( 'acf/fields/flexible_content/layout_title', 'my_layout_title', 10, 4 );

function my_layout_title($title, $field, $layout, $i) {
    if($value = get_sub_field('title')) {
        return $value;
    } else {
        foreach($layout['sub_fields'] as $sub) {
            if($sub['name'] == 'title') {
                $key = $sub['key'];
                if(array_key_exists($i, $field['value']) && $value = $field['value'][$i][$key])
                    return $value;
            }
        }
    }
    return $title;
}