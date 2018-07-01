<?php
/**
 * Load partials.
 *
 * @since 1.0.2
 */

function partial( $partial, $vars = array() ) {
	extract( (array) $vars );

	if( $template = locate_template( '/inc/partials/' . $partial . '.php' ) ) {
		include( $template );
	}
}


/**
 * Render page structure via ACF Flexible Content.
 *
 * Uses the ACF Flexible Content extension to generate a series of content blocks, passing content to each.
 *
 * @since 1.0.2
 */

function get_page_structure( $field_name, $post_id = false ) {
	if ( have_rows( $field_name, $post_id ) ) {
		while ( have_rows( $field_name, $post_id ) ) {
			the_row();
			
			include( locate_template( '/inc/blocks/' . str_replace( '_', '-', get_row_layout() ) . '.php' ) );
		}
	}
}


/**
 * Load config variables from Redux and ACF.
 *
 * @since 1.0.2
 */

function theme( $key, $child_key = false, $default = '', $post_id = false ) {

    if ( function_exists( 'get_field' ) ) {

        $var = ( is_numeric( $post_id ) || ( gettype( $post_id ) == 'object' && get_class( $post_id ) == 'WP_Term' ) ) ? get_field( $key, $post_id ) : get_field( $key );

        if ( ! $var ) {
            if ( function_exists( 'get_sub_field' ) ) {
                $var = ( is_numeric( $post_id ) || ( gettype( $post_id ) == 'object' && get_class( $post_id ) == 'WP_Term' ) ) ? get_sub_field( $key, $post_id ) : get_sub_field( $key );
            }
        }

        if ( $var ) {
            if ( $child_key ) {

                if ( is_array( $var ) && count( $var ) == 1 ) {
                    $var = reset( $var );
                }

                if( isset( $var[ $child_key ] ) ) {
                    return $var[ $child_key ];
                }

                return false;
            }

            return $var;
        }

        if ( $var = get_field( $key, 'option' ) ) {
        	if( is_array( $var ) && count( $var ) == 1 ) {
        		$var = reset( $var );
        	}

            if ( $child_key && isset( $var[$child_key] ) ) {
            	return $var[$child_key];
            }

            return $var;
        }
    }

    return $default;
}



function feature_bg_render( $ref, $image_sizes ) {

    if( ! $ref ) {
        return '';
    }


    $styles =
        '<style>
    #block__background--' . $ref . ' {
    background-image: url(' . $image_sizes['large'] . ');
        background-size: cover;
            background-position: 80%;
            }

    @media (min-width:501px) {
    #block__background--' . $ref . ' {
    background-image: url(' . $image_sizes['medium_large'] . ');
        background-size: cover;
            background-position: right;
                }
            }

            @media (min-width:651px) {
    #block__background--' . $ref . ' {
    background-image: url(' . $image_sizes['medium_large'] . ');
        background-size: cover;
            background-position: right;
                    
                }
            }

            @media (min-width:801px)  {
    #block__background--' . $ref . ' {
    background-image: url(' . $image_sizes['large'] . ');
                         background-size: cover;
                             background-position: right;
                   
                }
            }

            @media (min-width:1201px) {
    #block__background--' . $ref . ' {
    background-image: url(' . $image_sizes['large'] . ');
          background-size: cover;
                }
            }
            
            @media (min-width:1601px) {
    #block__background--' . $ref . ' {
    background-image: url(' . $image_sizes['large'] . ');
          background-size: cover;
                }
            }
         </style>';

    return $styles;

}
