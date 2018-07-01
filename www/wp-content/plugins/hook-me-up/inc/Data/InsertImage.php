<?php
/**
 * Created by PhpStorm.
 * User: nourlatreche
 * Date: 16/02/2018
 * Time: 09:39
 */

namespace Inc\Data;


class InsertImage
{
	function insert_image_folder()
	{
		global $wpdb;

		$upload_dir = wp_upload_dir();
		$dir =  $upload_dir['basedir'].'/products_images';
		$files = scandir($dir, 1);





		foreach ($files as $file){

			$bname = basename($file, ".jpg");

			$name = str_replace(["-", "–"], '', $bname);


			$postname = str_replace(["-", "–"], ' ',basename($files[0], ".jpg") );
			$mypost = get_page_by_title($postname, OBJECT, 'product');
			@$product_id = $mypost->ID;


			if($postname) {




				if( $name ){
					$featured = $name;
					$featured_url = $dir.'/'.$file;

				}
				if( $name.'1' ){

					$g_one_url = $dir.'/'.$file;
				}
				if( $name.'2' ){

					$g_two_url = $dir.'/'.$file;
				}
				if( $name.'3' ){

					$g_three_url = $dir.'/'.$file;
				}



				if( isset($product_id) ) {
					//
					$attachment_id = $this->attach_image ($product_id, $featured_url);

					//featured image
					if($attachment_id) {
						update_post_meta($product_id, '_thumbnail_id', $attachment_id);
					}




					// add to gallery
					$ids = array();
					$images = array(
						array(
							'url' => $g_one_url
						),
						array(
							'url' => $g_two_url
						),
						array(
							'url' => $g_three_url
						),
					);

					foreach ($images as $image)
					{
						$ids[] = $this->attach_image( $product_id, $image['url']);

					}
					if($attachment_id) {

						update_post_meta($product_id, '_product_image_gallery', implode(',', $ids));
					}

				}else {
					echo 'Couldn\'t find the post name';
					exit();


				}
			}
		} // endforeach


	}

	function handle_csv_OLD($file)
	{
		global $wpdb;

		//$csv_file                        = 'http://localhost/wp_treehouse/wp-content/plugins/hook-me-up-csv/users.csv';
		// $csv_file = $this->plugin_url.'users.csv';
		$csv_file = $file;

		//for checking headers
		$requiredHeaders = array('Internal ID','Product Code','Suggested Name','Website Category','Sub-brand,Description','Short Description','Certification','');

		$fptr = fopen($csv_file, 'r');
		$firstLine = fgets($fptr); //get first line of csv file
		fclose($fptr);
		$foundHeaders = str_getcsv(trim($firstLine), ',', '"'); //parse to array


		//check the headers of file
		/* if ($foundHeaders !== $requiredHeaders) {
			 echo 'File Header not the same';
			 die();
		 }**/
		$getfile = fopen($csv_file, 'r');
		//$users     = array();
		if (false !== ($getfile = fopen($csv_file, 'r'))) {

			$data = fgetcsv($getfile, 1000, ',');
			//display table headers
			//var_dump($data  );

			$update_cnt = 0;
			$insert_cnt = 0;
			$count = 0;
			while (false !== ($data = fgetcsv($getfile, 1000, ','))) {
				if ($data[0] != NULL) {  // ignore blank lines
					$count++;
					$result = $data; // two sperate arrays
					$str = implode(',', $result); // join the two sperate arrays
					$slice = explode(',', $str); // remove ,

					//variables
					$fileurl = $slice[0];
					$gallery_one = $slice[1];
					$gallery_two = $slice[2];

					$bname = basename($fileurl, ".jpg");
					$name = str_replace(["-", "–"], ' ', $bname);
					$mypost = get_page_by_title($name, OBJECT, 'product');
					@$product_id = $mypost->ID;;



					if( isset($product_id) ) {
						//
						$attachment_id = $this->attach_image ($product_id, $fileurl);

						//featured image
						if($attachment_id) {
							update_post_meta($product_id, '_thumbnail_id', $attachment_id);
						}




						// add to gallery
						$ids = array();
						$images = array(
							array(
								'url' => $gallery_one
							),
							array(
								'url' => $gallery_two
							),
						);

						foreach ($images as $image)
						{
							$ids[] = $this->attach_image( $product_id, $image['url']);

						}
						if($attachment_id) {

							update_post_meta($product_id, '_product_image_gallery', implode(',', $ids));
						}

					}else {
						echo 'Couldn\'t find the post name';
						exit();


					}




				}//if empty lines


			}//end of while




		}
		return $ids;


	}



	function attach_image ($product_wp_id, $filename)
	{
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		$post_id = $product_wp_id;
		$desc = "The WordPress Logo";
		$bname = basename($filename);

		$uploaddir = wp_upload_dir();
		$uploadfile = $uploaddir['path'] . '/'. $bname ;
		$uploadfile_from = $uploaddir['basedir'].'/products_images'. '/'. $bname ;

		if(file_exists($uploadfile)){
            $attach_id = attachment_url_to_postid($uploadfile);
            echo 'File exists';

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



}