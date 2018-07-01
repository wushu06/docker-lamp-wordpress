<!-- hero carousel -->

<div class="block_hero-carousel" >
    <div class="block_container">

        <div class="hero-slider ">

            <?php


            global $theme;

            $gs = $theme::theme_carousel('gallery') ;


                ?>
                <?php foreach( $gs as $i => $slide ):

                //  the_row();
                // vars
                $image = $slide['image'];
                $text = $slide['text'];

                $rand = mt_rand();
                ?>
                <div class="block_hero-carousel_images" id="block__background--<?php echo $rand; ?>" style="height: 90vh;">




                    <div class="block_hero-carousel_images_slide">

                        <div class="block_hero-carousel_images_slide_title block_small_container">
                            <h1 class=""><?php echo $text; ?></h1>
                            <div class="title-separator hero-separator "></div>

                        </div>
                    </div>
                    <?php echo feature_bg_render( $rand, $image['sizes'] ); ?>





                </div>
            <?php endforeach; ?>
            <?php  //endif; ?>

        </div> <!-- slider ends -->

        </div> <!-- slider ends -->


    </div>
</div>

<div class="clearfix"></div>

<div class="crosshairs rotate">
    <svg id="svg" xmlns="http://www.w3.org/2000/svg" height="100" style="" version="1.1" width="100">
        <path fill="none" height="100%" id="backgroundrect" stroke="none" width="100%" d="M0 0 L100 0 L100 100 L0 100 Z" class="sKLUyLVI_0"/>
        <style>
            .st0{fill-rule:evenodd;clip-rule:evenodd;fill:none;stroke:#CDCCCC;stroke-miterlimit:10;}

        </style>

        <g class="currentLayer">
            <title>Layer 1</title>
            <path class="st0 JzCgRzpC_0 selected sKLUyLVI_1" d="M39.8,50A10.2,10.2 0,1,1 60.2,50A10.2,10.2 0,1,1 39.8,50" id="svg_2"/>
            <path class="st0 JzCgRzpC_1 selected sKLUyLVI_2" d="M28.5,50A21.5,21.5 0,1,1 71.5,50A21.5,21.5 0,1,1 28.5,50" id="svg_3"/>

            <path class="st0 line-top sKLUyLVI_3" d="M28.5,50ANaN,NaN  0 0 71.5,50A21.5,21.5 0 1 1 28.5,50" id="svg_3"/>
            <path class="st0 JzCgRzpC_2 sKLUyLVI_4" d="M50,50 L50,102 " id="svg_4"/>
            <path class="st0 JzCgRzpC_2 sKLUyLVI_5" d="M50 ,-5 L50,50 " id="svg_7"/>





            <path class="st0 JzCgRzpC_3 selected sKLUyLVI_6" d="M0,50 L50,50 " id="svg_5"/>
            <path class="st0 JzCgRzpC_3 sKLUyLVI_7" d="M50,50 L100,50 " id="svg_6"/>

        </g>

    </svg>

</div>

