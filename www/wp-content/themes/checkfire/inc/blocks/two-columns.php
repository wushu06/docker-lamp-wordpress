<div class="block_two-col" >
    <div class="block_small_container">

        <div class="row">
            <h5 class="block_two-col_small-title"><?php echo theme( 'small_title' ); ?></h5>


            <div class="col-md-5 block_two-col_content">


                <div class="block_two-col_title ">
                    <h1 class=" js-fadeUp">
                        <?php echo theme( 'title' ); ?>

                    </h1>
                    <div class="title-separator"></div>
                </div>
                <div class="block_two-col_content">
                    <?php echo theme( 'content' ); ?>
                </div>
                <?php if(theme( 'button_url' )): ?>
                <div class="block_two-col_button">
                    <a class=" hvr-sweep-to-right red-button" href="<?php echo theme( 'button_url' ); ?>">
                        <?php echo theme( 'button' ); ?>
                    </a>
                </div>
                <?php endif; ?>

            </div>
            <div class="col-md-7">
                <div class="block_two-col_image ">

                    <img  class="home-image img-responsive" src="<?php echo theme('image')['url']; ?>" alt="">
                </div>
            </div>

        </div><!-- end row -->
    </div>
</div>