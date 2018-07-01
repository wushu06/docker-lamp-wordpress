<?php get_header();?>
<!-- single -->

<div class="block_single">
    <div class="block_small_container">
        <div class="row">
            <div class="col-md-12 block_single_image">
                <img src="<?php echo get_the_post_thumbnail_url() ?>" class="img-responsive" alt="">
            </div>
        </div>
        <div class="row">
            <div class="col-md-9 col-sm-9 col-xs-12 block_single_content">
                <h1>
		            <?php the_title(); ?>
                </h1>
                <div class="title-separator"></div>
	            <?php
	            if ( have_posts() ) {
		            while ( have_posts() ) {
			            the_post();
			            //
			            // Post Content here
			            the_content();
			            //
		            } // end while
	            } // end if
	            ?>
            </div>
            <div class="col-md-3 col-sm-3 col-xs-12">
                <ul class="stick-scroll">
                    <li class="block_single_author">
		                <?php
		                // Retrieve The Post's Author ID
		                $user_id = get_the_author_meta('ID');
		                // Set the image size. Accepts all registered images sizes and array(int, int)
		                $size = 'thumbnail';

		                // Get the image URL using the author ID and image size params
		                $imgURL = get_cupp_meta($user_id, $size);

		                // Print the image on the page
		                echo '<img src="'. $imgURL .'" alt="" width="95">';
		                echo '<p class="name">by <strong>'.get_the_author().'</strong></p>';
		                ?>
                    </li>
                    <li><span class="text-center">SHARE</span></li>
                    <li >

                        <?php echo do_shortcode('[addthis tool="addthis_inline_share_toolbox_bgbw"]'); ?>
                    </li>
                </ul>

            </div>

        </div>

        <!-- prev and next posts -->



        </div>

    </div> <!--end container -->

</div> <!-- end block -->

<div class="clearfix"></div>



<div class="block_blog">
    <div class="block_small_container">
        <div class="row block_blog_title">
            <div class="col-xs-12 col-md-12">
                <h1>More like this</h1>
                <div class="title-separator"></div>
            </div>




        </div>




        <div class="row">

            <div id="lazyload">



	            <?php


	            $related = get_posts( array( 'category__in' => wp_get_post_categories($post->ID), 'numberposts' => 3, 'post__not_in' => array($post->ID) ) );
	            if( $related ):  foreach( $related as $post ) {
	            setup_postdata($post); ?>

                        <div class="col-md-4 col-xs-12">
                            <div class="block_blog_wrapper_image">
                                <a href="<?php the_permalink() ?>" class="animsition-link " data-animsition-out-class="zoom-out-sm">

                                    <img src="<?php echo get_the_post_thumbnail_url()?>" class="img-responsive" alt="">
                                    <div class="block_blog_wrapper_image_overlay">
                                        <span>Find out more</span>
                                    </div>
                                </a>
                            </div>
                            <a href="<?php the_permalink() ?>"><h2 class="height-fix"><?php the_title() ?></h2></a>
                            <div class="row block_blog_meta">
                                <div class="col-md-4">
									<?php
									// Retrieve The Post's Author ID
									$user_id = get_the_author_meta('ID');
									// Set the image size. Accepts all registered images sizes and array(int, int)
									$size = 'thumbnail';

									// Get the image URL using the author ID and image size params
									$imgURL = get_cupp_meta($user_id, $size);

									// Print the image on the page
									echo '<img src="'. $imgURL .'" alt="" width="95"> ';
									?>
                                </div>
                                <div class="col-md-8">
                                    <p>POSTED <strong><?php the_date('Y.m.d') ?></strong><br/>
                                        BY <strong><?php the_author() ?></strong></p>
                                </div>

                            </div>



                        </div>
					<?php   } endif;


				?>


            </div><!-- end wrapper -->
        </div><!-- end col -->
        <div class="block_single_back text-center">
            <a class="red-button hvr-sweep-to-right " href="<?php echo site_url() ?>/blog">BACK TO THE BLOG LIST</a>
            
        </div>

    </div><!-- end row -->



</div>
</div>



<?php get_footer();?>






