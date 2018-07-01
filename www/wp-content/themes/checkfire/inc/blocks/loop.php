<!-- -/ custom loop /- -->
<?php if( theme('use_loop') == true ) :?>
	<!-- === our brands ===  -->
	<div class="block_three-col" >
		<div class="block_small_container">

			<div class="row">
                <div class="col-md-12">
				<h1>Our brands</h1>
                <div class="title-separator"></div>
                </div>
			        <?php checkfire_home_loop ('commander'); ?>
                    <?php checkfire_home_loop ('commander-edge'); ?>
                    <?php checkfire_home_loop ('contempo'); ?>

			</div><!-- end row -->

		</div>
	</div>







	<!-- === what we do ===  -->

<div class="block_three-col" >
	<div class="block_small_container">

		<div class="row">
			<h1>What we do</h1>

					<?php
					$args = array(
						'post_type' => 'product',
						'posts_per_page' => '3',
						'orderby' => array(
							'ID' => 'DESC',
						),
						'product_cat' => 'fire-extinguishers'

					);



					$query = new WP_Query($args);

					while ($query->have_posts()) {
						$query->the_post(); ?>


					<div class="col-md-4 col-sm-6 col-xs-12">
						<div class="block_three-col_wrapper">
                            <a href="<?php echo the_permalink() ?>">
							<div class="block_three-col_wrapper_image">
								<?php if(has_post_thumbnail()): ?>
								<img class="img-responsive" src="<?php echo get_the_post_thumbnail_url() ?>" alt=""  width="450" height="600"/>
									<?php else: ?>
									<img class="img-responsive" src="http://via.placeholder.com/450x600" alt="" />
								<?php endif; ?>
							</div>
                            </a>
                            <a href="<?php echo the_permalink() ?>">
							<div class="block_three-col_wrapper_content">

									<div class="content_title">
										<h3><?php the_title(); ?></h3>
										<div class="content_btn">
											<a href="<?php echo the_permalink() ?>" class="button">FIND OUT MORE</a>

										</div>

									</div>


							</div>
                            </a>




						</div><!-- end wrapper -->
					</div><!-- end col -->

					<?php } wp_reset_query(); ?>

		</div><!-- end row -->












	</div>
</div>
<?php endif; ?>