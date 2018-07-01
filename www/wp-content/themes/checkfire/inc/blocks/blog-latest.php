<!-- -/ block blog latest (copy from home.php)/- -->

<div class="block_blog">
    <div class="block_small_container">
        <div class="row block_blog_title">
            <div class="col-xs-12 col-md-12">
                <h1>Latest Articles</h1>
                <div class="title-separator"></div>
            </div>

        </div>




        <div class="row">

            <div id="lazyload" class="block_blog_wrapper mobile-wrapper-slick">



				<?php
                global $theme;
                $posts = $theme::theme_custom_loop('post',3);



						// var_dump($post);

                        foreach ($posts as $post):



						?>
                        <div class="col-md-4 col-xs-12 ">
                            <div class="block_blog_wrapper_image">
                                <a href="<?php the_permalink() ?>" class="animsition-link " data-animsition-out-class="zoom-out-sm">

                                    <img src="<?php echo $post['thumbnail'];?>" class="img-responsive" alt="">
                                    <div class="block_blog_wrapper_image_overlay">
                                        <span>Find out more</span>
                                    </div>
                                </a>
                            </div>
                            <a href="<?php the_permalink() ?>"><h2 class="height-fix"><?php  echo $post['title']; ?></h2></a>
                            <div class="row block_blog_meta ">
                                <div class="col-md-4 col-xs-4 ">
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
                                <div class="col-md-8 col-xs-8">
                                    <p>POSTED <strong><?php echo $post['date']; ?></strong><br/>
                                        BY <strong><?php echo $post['author']; ?></strong></p>
                                </div>

                            </div>



                        </div>
					<?php   endforeach;


				?>









            </div><!-- end wrapper -->
        </div><!-- end col -->

    </div><!-- end row -->



</div>
</div>


