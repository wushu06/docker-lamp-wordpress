<?php use Inc\Base\BaseController; ?>
<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

<?php // if ( ! $this->activated( 'hmu_plugin', 'activate_cron' ) ) : ?>
<?php if(@get_option('hmu_dashboard')['activate_cron'] == false) : ?>

<h2>Cron Task Not Activated.</h2>

<p><a href="<?php echo get_admin_url()  ?>?page=hmu_plugin">Activate Here</a></p>

<?php else: ?>
<h2>Cron Activated</h2>


<?php settings_errors(); ?>

<form method="post" class="hmu-general-form" action="options.php" enctype="multipart/form-data">
    <?php
    settings_fields( 'hmu_cron_options_group' );
    do_settings_sections( 'cron_task' );
    submit_button( 'Create task', 'hmu-btn hmu-primary', 'btnSubmit' );
    ?>
<?php
    $option  = get_option ('hmu_cron');
    if( $option ):
  /*  if( $option['error'] ){
        echo 'File couldnt be moved';
        exit();

    }*/


    $output ="<table class='widefat fixed' cellspacing='0'>\n\n";
    $output .= "<thead>\n\n";
    $output .= "<tr>\n\n";
    $output .= "<th > Task ID</th>";
    $output .= "<th> Task Name</th>";
    $output .= "<th> Task Schedule</th>";
    $output .= "<th> Task Function</th>";
    $output .= "<th> Task Path</th>";
    $output .= "<th> Delete</th>";

    $output .= "</tr>\n\n";
    $output .= "</thead>\n\n";
    $output .= "<tbody> \n";



            foreach (@$option as $key => $value) {
                $output .= "<tr>\n";
                $output .=  "<td>" . $key . "</td>";
                $output .=  "<td>" .$value['cron_name']. "</td>";
                $output .=  "<td>" .$value['cron_time']. "</td>";
                $output .=  "<td>" .$value['cron_function']. "</td>";
                $output .=  "<td>" .(($value['cron_upload'] !='')? $value['cron_upload'] : $value['cron_url'])  . "</td>";
                $output .=  "<td><a href='".admin_url()."admin.php?page=cron_task&cron_delete=".$key."'><i class='fa fa-close'></i></a></td>";
                $output .= "</tr>\n";

            }

    $output .= "</tbody> \n ";
    $output .= "\n</table>";

    echo $output;


endif;




?>

</form>
    <form method="POST" action="">
        <input class="hmu-input hmu-delete" name="delete_cron" type="submit" value="Delete cron task">
    </form>

<?php endif; ?>
<?php


if(isset($_POST['delete_cron'])){

    delete_option( 'hmu_cron' );
    wp_clear_scheduled_hook( 'hmu_upload_file_cron' );
    $default = array();
     add_option('hmu_cron', $default);
    $output = get_option('hmu_cron');
    $output = array();


    echo 'Task has been deleted';
}

if(isset($_GET['cron_delete']) && !empty( $option)){

    $id = $_GET['cron_delete'];
    $option  = get_option ('hmu_cron');
    $option_id = $option[$id];
    $option_id = array();

}




?>
<?php

/*echo '<pre>';
var_dump(get_option ('hmu_cron'));
echo '</pre>';*/






