<?php

namespace Inc\Data;

use \Inc\Base\BaseController;
use \Inc\Base\Email;

class InsertUser extends BaseController
{

    public $user_id;
    public $dataCheck;
    public $user_data = array();
    public $email;

    public function __construct ()
    {
        $this->email = new Email();

    }



    function handle_csv($file)
    {
        $reault_array = array();

        //$csv_file                        = 'http://localhost/wp_treehouse/wp-content/plugins/hook-me-up-csv/users.csv';
        // $csv_file = $this->plugin_url.'users.csv';
        $csv_file = $file;

        //for checking headers
        // $requiredHeaders                 = array( 'Product id', 'User', 'Price' );
        $requiredHeaders = array('Internal ID','Account Number','Name','Status','Phone','Email','Login Access','Price Level','Pricing Group','Consignment Stock Customer','Postal Code','Billing Address 1','Billing Address 2','Billing Address 3','Shipping Carrier','Primary Contact','Alt. Email','Special Notes');

        $fptr = fopen($csv_file, 'r');
        $firstLine = fgets($fptr); //get first line of csv file
        fclose($fptr);
        $foundHeaders = str_getcsv(trim($firstLine), ',', '"'); //parse to array


        //check the headers of file
        if ($foundHeaders !== $requiredHeaders) {
            echo 'File Header not the same';
            die();
        }
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
		           // $ID = $slice[0]; irrelevant
		            $custom_id = $slice[1];
		            $username = $slice[2];
		            $status = $slice[3];
		            $phone = $slice[4];
		            $email = $slice[5];
		            $login_acess = $slice[6];
		            $role = strtolower(str_replace([" ", " "], '-',$slice[7]));
		            $pricing_group = $slice[8];
		            $cons = $slice[9];
		            $post_code = $slice[10];
		            $address = $slice[11];
		            $city = $slice[12];
		            $billing_address_3 = $slice[13];
		            $carrier = $slice[14];
		            $alt_email = $slice[15];
		            $special_note = $slice[16];


		            $reault_array[] = $this->insert_update_user( $custom_id, $username, $role, $phone, $email, $post_code, $address, $city);

		            // echo $reault_array['msg'];
		            //echo ($reault_array['check'] == true ? 'Send Email' : 'dont send email');

	            }

            }//end of while

        }
        return $reault_array;


    }

    function insert_update_user($custom_id, $username, $role, $phone,   $email,$post_code,$address,$city)
    {


        $this->user_data ['username'] = $custom_id;
        $this->user_data ['email'] = $email;
       // wp_suspend_cache_addition(true);
        $password = $this->randomPassword();
        $pass = wp_hash_password($password);

        $userdata = array(
            'user_login' => $custom_id,
            'user_pass' => $pass,
            'user_email' => $email,
            'first_name' => $username,
            'last_name' => $username,
            'role' => $role
        );

        global $wpdb;

        // getting users by email

        //$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE user_email = %s", $email));

        // getting users by username

        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE user_email = %s", $email));


        if ($count == 1) { //

            $v =  get_user_by('email', $email);

            if($v){

               echo $ID = $v->ID;

	            $user_meta=get_userdata($ID);
	            $user_roles=$user_meta->roles;
	            if (!in_array($role, $user_roles)){

		            if ($this->email->role_chaned_email($username, $email)) {
			            $msg = 'email was sent';
			            $check = true;
		            } else {
			            $msg = "Couldn't send role email to: ".$email;
			            $check = false;
		            }
	            }

            }

            $user_id = wp_update_user(array(
                                        'ID' => @$ID,
                                        'user_login' => $custom_id,
                                        'user_email' => $email,
                                        'first_name'=>$username,
                                        'last_name'=>$username,
                                        'role'=>$role
                                        ));
		     /*   $wpdb->update(
			        $wpdb->users,
			        array(
				        'custom_id'=>$custom_id
			        ),
			        array(
				        'ID' =>$ID
			        ),

			        array(
				        '%s'
			        )
		        );*/

            if (is_wp_error($user_id)) {
                $msg =  "There was an error, probably that user doesn't exist";
                $check = false;

            } else {
                $msg =  ' User has been updated!';
                $check = true;




            }
            $havemeta = get_user_meta(31, '_user_custom_id', false);

            if ($havemeta){
                update_user_meta( $user_id, '_user_custom_id', $custom_id);


            } else {
                add_user_meta( $user_id, '_user_custom_id', $custom_id);
            }

            update_user_meta( $user_id, 'shipping_address_1', $address);
            update_user_meta( $user_id, 'billing_address_1', $address);
            update_user_meta( $user_id, 'shipping_city', $city);
            update_user_meta( $user_id, 'billing_city', $city);
            update_user_meta( $user_id, 'shipping_postcode', $post_code);
            update_user_meta( $user_id, 'billing_postcode', $post_code);




        } else {

            $user_id = wp_insert_user($userdata);
	/*        $wpdb->update(
		        $wpdb->users,
		        array(
			        'custom_id'=>$custom_id
		        ),
		        array(
			        'ID' =>$user_id
		        ),

		        array(
			        '%s'
		        )
	        );*/

            $msg =  $custom_id . ' New User <br>';

            //On success
            if (!is_wp_error($user_id)) {

                $msg = "User created : " . $custom_id . ' ID: ' . $user_id;



                if ($this->email->retrieve_password($custom_id)) {
                    $msg =  "Reset Password link has been sent to ".$email;
                    $check = true;
                } else {
                    $msg = "Couldn't send reset password email to ".$email;
                    $check = false;
                }


            } else {

                $msg = "Couldn't create ";

            }

            add_user_meta( $user_id, '_user_custom_id', $custom_id);
            update_user_meta( $user_id, 'shipping_address_1', $address);
            update_user_meta( $user_id, 'billing_address_1', $address);
            update_user_meta( $user_id, 'shipping_city', $city);
            update_user_meta( $user_id, 'billing_city', $city);
            update_user_meta( $user_id, 'shipping_postcode', $post_code);
            update_user_meta( $user_id, 'billing_postcode', $post_code);


        }

         return array('username'=>$custom_id,'msg'=>$msg, 'check'=>@$check);

    }

    public function randomPassword() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }


     /**
     * Handles sending password retrieval email to user.
     *
     * @uses $wpdb WordPress Database object
     * @param string $user_login User Login or Email
     * @return bool true on success false on error
     */





}


