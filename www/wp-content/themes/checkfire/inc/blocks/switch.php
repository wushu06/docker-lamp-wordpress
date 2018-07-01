


<div class="block_switch">
    <div class="container">

    	<?php $i = 0; ?>
    	<?php if( $items = theme( 'items' ) ) { ?>
    		<?php foreach( $items as $item ) { ?>
    			
		        <?php if( $i % 2 == 0 ) { ?>
		        	<div class="row">
			            <div class="col-md-6  block-choose-img text-center align-self-start">
					
			                <img src="<?php echo $item['image']['sizes']['technical']; ?>" title="<?php echo esc_attr( $item['title'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" class="img-responsive"  />
			                
			            </div>

			            <div class="col-md-6">
			            	<div class="block_switch_content">
			                    <h2 class="block__title h3 t-brand"><?php echo $item['title']; ?></h2>



			                    <?php echo $item['body']; ?>
			                </div>
			            </div>
		        	</div>
		        <?php } else { ?>
		         <div class="row" >

		         	    <div class="col-md-6 block-choose-content">
			            	<div class="content-wrap" >
			                    <h2 class="block__title h3 t-brand"><?php echo $item['title']; ?></h2>


			                    <?php echo $item['body']; ?>
			                </div>
			            </div>
			            <div class="col-md-6 block-choose-img ">
			            
			                <img src="<?php echo $item['image']['sizes']['technical']; ?>" title="<?php echo esc_attr( $item['title'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" class="img-responsive" />
			            </div>
			            

			        
		        	</div>
		        <?php } ?>

		        <?php ++$i; ?>
			<?php } ?>
		<?php } ?>

        
    </div>
</div>