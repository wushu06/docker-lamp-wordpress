<div class="block_masonry" >
    <div class="block_container">

        <div class="row">
           <h1><?php echo theme('page_title'); ?></h1>
            <a href="<?php echo site_url() ?>/about/school-staff/"><span class="pull-right all-staff">Click Here for a full list of our Staff </span></a>
            <div class="col-md-12">
                <div class="grid">
                    <div class="masonry">
                        <div class="grid-sizer"></div>
                        <div class="gutter-sizer"></div>


                                      <?php $i = 1; if( have_rows('profiles') ): ?>



                                <?php while( have_rows('profiles') ): the_row();

                                    // vars
                                    $image = get_sub_field('image');
                                    $overlay = get_sub_field('overlay_text');
                                    $popup = get_sub_field('popup');
                                    $title = get_sub_field('image_title');


                                    ?>


                                   <div class="item popup-gallery">
                                    <div class="overlay-wrapper">
                                        <a data-fancybox="images" data-src="#hidden-content<?php echo $i; ?>" href="<?php echo $image['url']; ?>">
                                            <img src="<?php echo $image['url']; ?>">
                                            <div class="item-background"></div>
                                            <div class="item-overlay">
                                                <span><?php echo $overlay; ?></span>

                                            </div>
                                        </a>
                                    </div>

                                       <div>
                                           <h4  ><?php echo $title; ?></h4>
                                       </div>

                                            <div class="hidden-content" style="display: none;" id="hidden-content<?php echo $i;  ?>">
                                                <div class="content-image">
                                                    <img src="<?php echo $image['url']; ?>">
                                                </div>
                                                <div class="content-text">
                                                    <?php echo $popup; ?>
                                                </div>


                                            </div>



                                   </div>





                                <?php $i++; endwhile; ?>


                        <?php endif;  ?>





                    </div>


                 </div>
             </div>

        </div>

    </div>

</div>

