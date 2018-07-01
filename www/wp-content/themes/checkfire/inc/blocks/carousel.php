<!-- sec carousel -->
<div class="block_sec_carousel">
    <div class="block_small_container">
<h1>carousel</h1>
        <div class="row ">
            <div class="js-fadeUp">
                <h1><?php echo theme('title'); ?></h1>
            </div>
            <div class="block_sec_carousel_images">
                <?php if( $images = theme( 'gallery' ) ) :

                    ?>



                        <?php while( have_rows('gallery') ): the_row();

                            // vars
                            $image = get_sub_field('image');
                            $text = get_sub_field('title');
                            $link = get_sub_field('link');
                            $hover = get_sub_field('hover_text');

                            ?>

                        <div class="block_sec_carousel_images_wrapper">
                            <a href="<?php echo site_url().$link ?>">
                                <div class="block_sec_carousel_images_slide">

                                    <img src="<?php echo $image['url']; ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>" width='300px' />
                                    <?php if($hover): ?>
                                    <div class="block_sec_carousel_images_slide_overlay " >
                                        <p><?php echo $hover; ?></p>
                                    </div>
                                    <?php endif; ?>
                                    <div class="block_sec_carousel_images_slide_title <?php echo $hover ? 'has-hover' : '' ; ?> " >
                                        <h1 class="pull-right "><?php echo $text;?></h1>
                                    </div>

                                </div>
                            </a>
                            </div>
                        <?php endwhile; ?>

                <?php endif; ?>






            </div>
        </div>



    </div>
</div>