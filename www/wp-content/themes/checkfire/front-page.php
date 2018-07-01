<!-- front page -->
<?php
// Page

get_header();
if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();


        get_page_structure( 'blocks' );

    }
}


get_footer();


?>

