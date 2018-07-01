<?php

function checkfire_home_loop ($brand_term) {

				$args = array(
					'post_type' => 'product',
					'posts_per_page' => '1',
					'orderby' => array(
						'ID' => 'DESC',
					),
					'tax_query' => array(

						'relation' => 'AND',
						array(
							'taxonomy' => 'brands',
							'field' => 'slug',
							'terms' => array($brand_term ),
						),

					),
				);


				$query = new WP_Query($args);
				while ($query->have_posts()) {
					$query->the_post();
					$terms = get_the_terms( get_the_ID(), 'brands');
					foreach( $terms as $term ) {
						$term_image = get_field('brands_image', $term);
						$image =  $term_image['url'];
					}
					?>

					<div class="col-md-4 col-sm-6 col-xs-12">
						<div class="block_three-col_wrapper">
							<div class="block_three-col_wrapper_image">
								<a href="<?php echo site_url().'/'.$brand_term  ?>">

									<?php if(has_post_thumbnail()):  ?>
										<img class="img-responsive" src="<?php echo get_the_post_thumbnail_url(get_the_ID(),'large'); ?>" alt=""  width="450" height="600"/>
									<?php else: ?>
										<img class="img-responsive" src="http://via.placeholder.com/450x600" alt="" />
									<?php endif; ?>
								</a>
							</div>
							<div class="block_three-col_wrapper_content">
								<a href="<?php echo site_url().'/'.$brand_term  ?>">
									<?php if ($image!=''):   ?>
										<div class="content_image">
											<img src="<?php echo $image; ?>" alt="" width="200" />
											<div class="content_btn">
												<a href="<?php echo site_url().'/'.$brand_term ?>" >FIND OUT MORE</a>

											</div>

										</div>
									<?php  endif; ?>
								</a>


							</div>




						</div><!-- end wrapper -->
					</div><!-- end col -->

				<?php } wp_reset_query();
}