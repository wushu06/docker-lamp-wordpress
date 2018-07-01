<!-- page -->
<?php
// Page
get_header();

if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
        $old = get_post_meta(get_the_ID(), 'customdata_group', true);
        echo '<pre>';
print_r($old);
echo '</pre>';

foreach ($old['hero'] as $field){
    @$hero = $field['mb_text'];
    if($hero){
        echo $hero;
    }

}
foreach ($old['two_col'] as $field){

    echo @$two_col_text = $field['mb_2col_text'];
    echo @$two_col_textarea = $field['mb_2col_textarea'];
}

        the_content();



    }
}


get_footer();
