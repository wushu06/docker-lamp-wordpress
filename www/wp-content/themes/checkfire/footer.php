<?php wp_footer();?>

    <footer class="footer text-center">
        <div class="block_small_container">
            <div class="row">
                <div class="col-md-8 col-xs-8">
                    <div class="footer_content">
                        <?php echo get_field('footer_content', 'option'); ?>

                    </div>

                </div>
                <div class="col-md-4 col-xs-4">
                    <div class="footer_social">
                        <?php
                        $rows = get_field('footer_social', 'option');
                        if($rows)
                        {
                        echo '<ul>';

                            foreach($rows as $row)
                            {
                            echo '<li> ' . $row['social'] . '</li>';
                            }

                            echo '</ul>';
                        }
                        ?>

                    </div>

                </div>

            </div>


        </div>

    </footer>





    <?php wp_footer();?>
</body>
</html>