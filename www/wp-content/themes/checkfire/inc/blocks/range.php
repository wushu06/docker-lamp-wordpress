<div class="block_range">
	<div class="block_small_container">
		<div class="block_range_title">
			<h1><?php echo theme('range_title') ?></h1>
			<div class="title-separator"></div>

		</div>
		<div class="row">
            <?php  global $post;
           $post_slug = $post->post_name; ?>
			<?php
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'orderby' => array(
                        'ID' => 'DESC',
                    ),
	                'brands' => $post_slug

                );



                $query = new WP_Query($args);

                while ($query->have_posts()) {
                $query->the_post(); ?>

                <div class="col-md-4">
                    <div class="block_range_content">
                        <a href="<?php the_permalink() ?>">
                        <div class="block_range_content_image">
                            <?php if(has_post_thumbnail()){?>
                            <img class="img-responsive" src="<?php echo get_the_post_thumbnail_url() ?>" alt="">
                             <?php }else{?>
                                <img class="img-responsive" src="http://via.placeholder.com/300x300" alt="">
                            <?php } ?>
                            <div class="block_range_content_image_data">
                                <div>
                                    <div><img class="img-responsive small-image" src="http://via.placeholder.com/100x50" alt=""></div>
                                    <h2 class="text-center"><?php the_title(); ?></h2>
                                    <span class="text-center find-out">Find out more</span>
                                </div>

                            </div>

                        </div>
                        </a>

                    </div>

                </div>

            <?php } wp_reset_query(); ?>


		</div>

	</div>

</div>
