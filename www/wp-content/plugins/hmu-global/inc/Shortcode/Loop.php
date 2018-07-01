<?php

namespace Inc\Shortcode;

class Loop
{
    function __construct()
    {
        // [bartag foo="foo-value"]
        add_shortcode( 'CUSTOMLOOP', array($this, 'hmu_g_customloop') );
    }





    function hmu_g_customloop( $atts, $content = NULL ) {

        $a = shortcode_atts( array(
            'foo' => 'something',
            'bar' => 'something else',
        ),
            $atts // attr we passing
        );



        // always use return
        return '<h1>d '.$a["foo"].$content.'</h1>';
    }


}
