<!-- half carousel -->
<div class="block_half-carousel">
    <div class="block_small_container ">
        <h1 class="before-title"><?php echo theme('title'); ?></h1>
        <div class="half_slide">

                        <?php
                        $r = rand();
                            if(have_rows('content')):
                                while ( have_rows('content') ) : the_row();

                        ?>

                <div class="row">

                        <div class="col-sm-12 col-md-6">
                                <div class="block_half-carousel_image">
                                    <img class="img-responsive" src="<?php echo get_sub_field( 'content_image' )['url']; ?>" alt="" width='450' />
                                </div>
                        </div>
                        <div class="col-sm-12 col-md-6 block_half-carousel_content">

                                <div class="block_half-carousel_title js-fadeUp" >
                                    <h1>
                                       <strong><?php echo  the_sub_field( 'content_title' ); ?></strong>
                                    </h1>
                                </div>
                                <div class="block_half-carousel_body">
                                    <?php echo  the_sub_field( 'content_body' ); ?>
                                </div>
                                <div class="block_half-carousel_button">
                                    <?php if(theme('button')) : ?>
                                    <a <?php if(theme('button_url') == '')  { ?> data-fancybox="images" data-src="#<?php echo $r  ?>" <?php } ?>  class="button-white hvr-shutter-out-vertical" href="<?php  echo the_sub_field( 'button_url' ) ?>">
                                        <?php echo theme('button') ?>
                                    </a>
                                        <?php endif; ?>

                                </div>
                         </div>

                </div>

                        <?php endwhile; endif; ?>


            </div>
        <?php if(theme('popup')) : ?>
        <!-- if the button is checked show popup -->
        <div class="hidden-content" style="display: none;" id="<?php echo $r   ?>">
            <div class="content-image">
                <img class="img-responsive" src="<?php echo theme('popup_image')['url'] ?>" alt="" width='450' />
            </div>
            <div class="content-text">
                <?php echo theme('popup_content') ?>
            </div>



        </div>
        <?php endif; ?>

    </div>
</div>