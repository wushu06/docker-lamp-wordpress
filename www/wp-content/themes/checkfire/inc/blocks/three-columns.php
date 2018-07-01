<!-- -/ block three column (Home Loop) /- -->
<div class="block_three-col"  id="threeColumns">
    <div class="block_small_container">

        <div class="row">
            <div class="col-md-12">
            <h1><?php echo theme('title'); ?></h1>
                <?php ( theme('title') == 'What we do' ) ? $class = 'what-we-do' : $class ='over-image';  ?>
            <div class="title-separator"></div>
            </div>

            <div class="mobile-wrapper-slick">




                <?php
                global $theme;

                $gs = $theme::theme_carousel('three_columns') ;
                foreach ($gs as $g ){

            $image = $g['main_image'];
            $sec_image = $g['second_image'];
            $title = $g['title'];
            $btn = $g['button'];
            $btn_url = $g['button_url'];
            ?>




            <div class="col-md-4 col-sm-6 col-xs-12">
                <a href="<?php echo site_url().'/fire-trade-products?filter='.$btn_url ?>" >
                <div class="block_three-col_wrapper ">
                    <div class="block_three-col_wrapper_image">
                        <img class="img-responsive" src="<?php echo $image['url']; ?>" alt="<?php echo $image['alt'] ?>" />
                        <div class="background-overlay"></div>
                    </div>

                    <div class="block_three-col_wrapper_content <?php echo $class; ?>">

                        <div class="content_image <?php echo $class ?>" id="over-image">
                            <img class="img-responsive" src="<?php echo $sec_image['url']; ?>" alt="<?php echo $sec_image['alt'] ?>"
                                 width="200" />
                            <h2><?php echo $title; ?></h2>
                            <div class="content_btn">
                                <a href="<?php echo site_url().'/fire-trade-products?filter='.$btn_url ?>" ><?php echo $btn; ?></a>

                            </div>

                        </div>


                    </div>

                </a>




                </div><!-- end wrapper -->
            </div><!-- end col -->

                <?php }
?>
        </div>

        </div><!-- end row -->












    </div>
</div>