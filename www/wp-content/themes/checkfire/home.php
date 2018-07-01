<!-- Blog - home.php - -->
<?php get_header();

?>

<div class="block_blog">
    <div class="block_small_container">
        <div class="row block_blog_title">
            <div class="col-xs-8 col-md-8 break-mobile">
                <h1>Latest Articles</h1>
                <div class="title-separator"></div>
            </div>
            <div class="col-xs-4 col-md-4 break-mobile">

                <div class="block_blog_form pull-right">
                    <form action="<?php echo site_url() ?>/wp-admin/admin-ajax.php" method="POST" id="filter">
				        <?php
				        if( $terms = get_terms( 'category', 'orderby=name' ) ) :
					        echo '<select id="select" name="categoryfilter" ><option>Filter by </option>';
					        foreach ( $terms as $term ) :
						        echo '<option value="' . $term->term_id . '">' . $term->name .' ('.$term->count. ')</option>';
					        endforeach;
					        echo '</select>';
				        endif;
				        ?>


                        <input type="hidden" name="action" value="customfilter">
                        <i class="fa fa-chevron-down" aria-hidden="true"></i>
                    </form>
                </div>
            </div>

        </div>
<div class="clearfix"></div>


<?php $i = 0; ?>


        <div id="lazyload">

        <div class=" row" >





            <?php $rs = $theme::theme_custom_loop('product', 'product_cat', 'test', 'product_cat', 'foam', 'OR');   ?>


            <?php foreach ($rs as $r):     ?>

            <?php
            $t = $theme::theme_terms('', 'product_cat' );
            var_dump($t);

            ?>








                             <!--   <div class="col-md-4 col-xs-12 block_blog_content">
                                        <div class="block_blog_wrapper_image">
                                            <a href="<?php /*// the_permalink() */?>" class="animsition-link " data-animsition-out-class="zoom-out-sm">
                                                <img src="<?php /*echo $r['thumbnail'] */?>" alt="">

                                                <div class="block_blog_wrapper_image_overlay">
                                                    <span>Find out more</span>
                                                </div>
                                            </a>
                                        </div>
                                    <a href="" class="animsition-link " data-animsition-out-class="zoom-out-sm">
                                        <h2><?php /*echo $r['title'] */?></h2>
                                    </a>



                                </div>
-->

                        </div><!-- end wrapper -->
                    </div><!-- end col -->
    <?php endforeach ; ?>

        </div><!-- end row -->
</div>
<div class="clearfix"></div>



    </div>
</div>




<?php get_footer(); ?>
