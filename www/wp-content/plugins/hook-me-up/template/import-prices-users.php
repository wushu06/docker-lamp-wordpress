<?php // MyPlugin - Settings Page


// disable direct file access
if (!defined('ABSPATH')) {

    exit;

}

use Inc\Data\InsertPriceByUser;
use Inc\Data\InsertPriceByRole;
use Inc\Data\InsertUser;
use Inc\Base\Email;
use Inc\Data\InsertProducts;
use Inc\Data\Submit;
use Inc\Data\InsertLocations;
use Inc\Data\UploadFile;
use Inc\Data\InsertImage;

$insert_by_user = new InsertPriceByUser();
$insert_by_role = new InsertPriceByRole();

$insert_products = new InsertProducts();
$insert_locations = new InsertLocations();

$submit = new Submit();
$upload = new UploadFile();
$email = new Email ();
$image = new InsertImage();
?>
    <h1>
        <?php echo esc_html(get_admin_page_title()); ?>
    </h1>


    <div class="wrap">
        <h2>Upload CSV File</h2>
        <span>

			</span>
        <hr>


        <div class="container">

            <ul class="tabs">
                <li class="tab-link current" data-tab="tab-1"><i class="fas fa-pound-sign"></i> User Based Prices</li>
                <li class="tab-link" data-tab="tab-2"><i class="fas fa-pound-sign"></i> Role Based Prices</li>
                <li class="tab-link" data-tab="tab-3"><i class="fas fa-upload"></i> Import Users</li>
                <li class="tab-link" data-tab="tab-4"><i class="fas fa-truck"></i> Import Products</li>
                <li class="tab-link" data-tab="tab-5"><i class="fas fa-thumbtack"></i> Import Locations</li>
                <li class="tab-link" data-tab="tab-6"><i class="fas fa-image"></i> Import Product Images</li>

            </ul>


            <div id="tab-1" class="tab-content  current">


                <form action="" method="post" enctype="multipart/form-data">
                    <label for="">Insert New Prices(By User):</label><br>
                    <input type="file" name="file_prices_users" id="locationUpload1">
                    <button id="importTable1" class="upload-file hmu-btn"> Upload File </button>
                    <input class="hmu-input hmu-primary  hidden" type="submit" value="Insert/Update Prices"  name="submit_prices_users">
                </form>


            </div>

            <div id="tab-2" class="tab-content">

                <form action="" method="post" enctype="multipart/form-data">
                    <label for="">Insert New Products(By Role):</label><br>
                    <input type="file" name="file_price_role" id="locationUpload2">
                    <button id="importTable2" class="upload-file hmu-btn"> Upload File </button>
                    <input class="hmu-input hmu-primary  hidden" type="submit" value="Insert/Update Prices" name="submit_prices_role">
                </form>
            </div>

            <div id="tab-3" class="tab-content">
                <span>New users must have unique username and email.</span>
                <form action="" method="post" enctype="multipart/form-data">
                    <label for="">Insert Users:</label><br>
                    <input type="file" name="file_users" id="locationUpload3">
                    <button id="importTable3" class="upload-file hmu-btn"> Upload File </button>
                    <input class="hmu-input hmu-primary  hidden" type="submit" value="Insert/Update Users" name="submit_users">
                </form>
                <?php if(@get_option('hmu_dashboard')['activate_email'] == false) : ?>
                    <a href="<?php echo get_admin_url()  ?>?page=hmu_plugin">Activate email</a>
                    <?php else: ?>
                 <form action="" method="post">
                    <input class=" hmu-input hmu-success" type="submit" value="Send result to the admin's email" name="send_email">
                    <input type="hidden" value="<?php if(isset($email_results )){ foreach ($email_results as $key=>$email_result ){echo $email_result.', ';}} ?>" name="username">
                </form>
                <?php endif; ?>
            </div>


            <div id="tab-4" class="tab-content">

                <form action="" method="post" enctype="multipart/form-data">
                    <label for="">Import Products:</label><br>
                    <input type="file" name="file_products" id="locationUpload4">
                    <button id="importTable4" class="upload-file hmu-btn"> Upload File </button>
                    <input class="hmu-input hmu-primary  hidden" type="submit" value="Insert/Update Products" name="submit_products">
                </form>
            </div>

            <div id="tab-5" class="tab-content">

                <form action=""   method="post" enctype="multipart/form-data">
                    <label for="">Import Locations:</label><br>
                    <input  type="file" name="file_locations" id="locationUpload5">
                    <button id="importTable" class="upload-file hmu-btn"> Upload File </button>
                    <input class="hmu-input hmu-primary  hidden" type="submit" value="Insert/Update Locations" name="submit_locations">
                </form>

            </div>

            <div id="tab-6" class="tab-content">

                <form action=""   method="post" enctype="multipart/form-data">
                    <label for="">Import Images:</label><br>
                    <!--  <input  type="file" name="file_images" id="locationUpload6">-->
                    <!--                    <button id="importTable" class="upload-file hmu-btn"> Upload File </button>
                    -->                    <input class="hmu-input hmu-primary " type="submit" value="Insert/Update Images" name="submit_images">
                </form>


            </div>

        </div><!-- container -->

        <div class="output-table">


        </div>


        <?php



        if (isset($_POST["submit_prices_users"]) && !empty($_FILES["file_prices_users"]["name"])) {

            echo '<h1>Upload Prices By User</h1>';
            $FILE_POST = $_FILES["file_prices_users"]['tmp_name'];
            $result = $submit->submit_data( 'submit_prices_users', $FILE_POST);
            foreach ($result as $key => $value ) {
                echo $value;
            }



        }

        if (isset($_POST["submit_prices_role"]) && !empty($_FILES["file_price_role"]["name"])) {?>
            <div class="notice notice-success is-dismissible">
                <h1>Prices Uploaded:</h1>
            </div>
            <?php
            //  echo    $csv_file                        = $this->plugin_url.'user_price.csv';
            //$file = $this->plugin_path.'price.csv';
            $FILE_POST = $_FILES["file_price_role"]['tmp_name'];
            $result = $submit->submit_data( 'submit_prices_role', $FILE_POST);
            foreach ($result as $key => $value ) {
                echo $value;
            }
        }


        if (isset($_POST["submit_users"]) && !empty($_FILES["file_users"]["name"])) {?>
            <div class="notice notice-success is-dismissible">
                <h1>Users Uploaded:</h1>
            </div>
            <?php
            $email_results = array();
            $FILE_POST = $_FILES["file_users"]['tmp_name'];
            $result =  $submit->submit_data( 'submit_users', $FILE_POST);

            $output ="<table class='widefat fixed' >\n\n";
            $output .= "<thead>\n\n";
            $output .= "<tr>\n\n";
            $output .= "<th > Username </th>";
            $output .= "<th> message</th>";
            $output .= "<th> result</th> ";
            $output .= "</tr>\n\n";
            $output .= "</thead>\n\n";
            $output .= "<tbody> \n";


            foreach ($result as  $key=>$value ) {
                $email_results[] =  $value['username'];
                $output .= "<tr>\n";
                $output .= "<td>".$value['username']."</td>";
                $output .= "<td>" . $value['msg'] . "</td>";
                $output .= "<td>" . ($value['check'] == true ? 'Success' : 'Failed') . "</td>";
                $output .= "</tr>\n";

            }


            $output .= "</tbody> \n ";
            $output .= "\n</table>";

            echo $output;

            // var_dump($email_results);




            //  var_dump($result);
            // if($value['check'] == true ){echo 'true';}else {echo 'false';}



        }

        if (isset($_POST["send_email"] ) ) {
            echo $email->retrieve_password('TBB5');
        }




        if (isset($_POST["submit_products"]) && !empty($_FILES["file_products"]["name"]) ) {?>

            <?php
            // $file = $this->plugin_path.'new.csv';
            $FILE_POST = $_FILES["file_products"]['tmp_name'];
            $result =  $submit->submit_data( 'submit_products', $FILE_POST);
            if($result) { ?>
                <div class="notice notice-success is-dismissible">
                    <h1>Products Uploaded:</h1>
                </div>
                <div class="hmu-result  ">
                    <?php foreach ($result as $key => $value ) {?>

                        <?php   echo $value; ?>

                    <?php }?>
                </div>
            <?php }



        }

        if (isset($_POST["submit_locations"]) ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <h1>Locations Uploaded:</h1>
            </div>
            <?php
            $FILE_POST = $_FILES["file_locations"]['tmp_name'];
            echo $submit->submit_data( 'submit_locations', $FILE_POST);
            // echo $insert_locations->handle_csv($FILE_POST);


        }

        if (isset($_POST["submit_images"]) ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <h1>Images Uploaded:</h1>
            </div>
            <?php
            // $FILE_POST = $_FILES["file_images"]['tmp_name'];
            //  echo $submit->submit_data( 'submit_images', '');
            get_products_images();







        }


        ?>
    </div>



<?php




global $wpdb;












/*function get_duplicates ($array) {
	return array_unique( array_diff_assoc( $array, array_unique( $array ) ) );
}

var_dump(get_duplicates ($a));*/

function get_products_images(){



    $upload_dir = wp_upload_dir();
    $dir = $upload_dir['basedir'] . '/products_images';
    $files = scandir($dir, 1);


    foreach ($files as $file) {

        $titles = array();
        $product_ids = array();
        $ids = array();




        $bname = basename($file, ".jpg");
        $name = str_replace(["-", "–"], '', $bname);
        $postname = str_replace(["-", "–"], ' ', $bname);
        $product_id_ob = get_page_by_title($postname, OBJECT, 'product');

        if($product_id_ob ) {

            $product_id = $product_id_ob->ID;
            $titles = get_the_title($product_id_ob);
            $fileurl = $dir.'/'.$file;
            $attachment_id = attach_image_l ($product_id,  $fileurl);

            //featured image
            if($attachment_id) {
                update_post_meta($product_id, '_thumbnail_id', $attachment_id);
            }

            if (in_array($bname.'-1.jpg', $files)){
                $g_one = $dir.'/'.$bname.'-1.jpg';

            }

            if (in_array($bname.'-2.jpg', $files)){
                $g_two = $dir.'/'.$bname.'-2.jpg';

            }

            if (in_array($bname.'-3.jpg', $files)){
                $g_three = $dir.'/'.$bname.'-3.jpg';

            }

            $images = array(
                array(
                    'url' => @$g_one
                ),
                array(
                    'url' =>@$g_two
                ),
                array(
                    'url' =>@$g_three
                )
            );

            foreach ($images as $image)
            {
                $ids[] = attach_image_l( $product_id, $image['url']);

            }
            if($attachment_id) {

                update_post_meta($product_id, '_product_image_gallery', implode(',', $ids));
            }




        }









    }






}




function attach_image_l ($product_wp_id, $filename)
{
    $attach_id ='';
    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');
    $post_id = $product_wp_id;
    $desc = "The WordPress Logo";
    $bname = basename($filename);

    $uploaddir = wp_upload_dir();
    $uploadfile = $uploaddir['path'] . '/'. $bname ;


    if(file_exists($uploadfile)){
        $wp_filetype = wp_check_filetype(basename($uploadfile), null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => $uploadfile,
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment( $attachment, $uploadfile );

        //$attach_id = attachment_url_to_postid($uploadfile);
       return $attach_id;



    } else {
        $contents= file_get_contents($filename);
        $savefile = fopen($uploadfile, 'w');
        fwrite($savefile, $contents);
        fclose($savefile);
        $wp_filetype = wp_check_filetype(basename($filename), null );

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => $filename,
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment( $attachment, $uploadfile );
        $imagenew = get_post( $attach_id );
        $fullsizepath = get_attached_file( $imagenew->ID );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $fullsizepath );
        wp_update_attachment_metadata( $attach_id, $attach_data );

    }

    return $attach_id;


}


