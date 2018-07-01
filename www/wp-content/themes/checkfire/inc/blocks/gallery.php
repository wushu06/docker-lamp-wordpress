<?php
// Page gallery
$args = array(
    // Only get published posts..
    'post_type'   => array('garage'),
    'post_status' => array('publish'),

    // Get the latest results
    'order'       => 'DESC',
    'orderby'     => 'rand',
);

// The Query
$garage = new WP_Query($args);
?>

<div class="test-parent">
	<div class="test-child">
		
	</div>
</div>


<div class="block block-gallery">
	<div class="container">

		<div class="grid">

			<?php // The Loop
if ($garage->have_posts()) {
    ?>


            <?php
while ($garage->have_posts()) {
        $garage->the_post();?>

			<div class="gallery-wrapper">
			
				<div class="grid-item">
				
					<div class="gallery-image">
						<img src="<?php echo get_the_post_thumbnail_url(); ?>"  class='img-responsive'>
						<a href="<?php the_permalink();?>" >
						<div class="overlay">
						<div class="text text-center">
							<h1><?php the_title();?></h1><br>
							<a class="link" href="<?php the_permalink();?>" >View</a>
							</div>
						</div>
						</a>
					</div>
					
				</div>

			</div>

		<?php }}?>







		</div>

	</div>
</div>


