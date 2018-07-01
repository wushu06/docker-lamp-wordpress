<?php 

namespace Inc\Api\Callbacks; 

use \Inc\Base\BaseController;

use \Inc\Api\SettingsApi;

class FieldsCallbacks extends BaseController {

    public $cron_name;

    public function sanitizeCallback2( $input )
    {
        $output = array();
      /*  $r = array();
        if(!empty($_FILES["hmu_cron"]["tmp_name"]))
        {


                $newFilename =  time().'_'.$_FILES["hmu_cron"]["name"];
                $location = $this->plugin_path.'Upload/'. $newFilename;
                move_uploaded_file($_FILES["hmu_cron"]["tmp_name"], $location);
                $r ['cron_upload'] =  $location;



        }*/

        if(isset($_POST['btnSubmit'])):
            $output = get_option('hmu_cron');
            if(!empty($_FILES["hmu_cron"]["tmp_name"]))
            {
                $newFilename =  time().'_'.$_FILES["hmu_cron"]["name"];
                $location = $this->plugin_url.'Upload/'. $newFilename;
              //  move_uploaded_file($_FILES["hmu_cron"]["tmp_name"], $location);
                $movefile = wp_handle_upload($_FILES["hmu_cron"], array('test_form' => FALSE));



            }



                if (empty($output)) {
                    $output['1'] = $input;
                    $output['1']['cron_upload'] = $movefile['url'];

                } else {

                    foreach ($output as $key => $value) {
                        $count = count($output);
                        if ($key < $count) {
                            $output[$key] = $value;
                            $output[$key]['cron_upload'] =$movefile['url'];

                        } else {
                            $output[$key + 1] = $input;
                            $output[$key + 1]['cron_upload'] =$movefile['url'];

                        }


                    }
                }

        endif;


        return $output;

    }

    public function sanitizeCallback( $options )
	{
		// return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        //return ( isset($input) ? true : false );
        //$output = array();

        /*  foreach ($this->dahboardFields   as $id_dash => $dashtitle_callback ) {

             /* if ($dashtitle_callback[5] == 'string' ) {
                     if( isset( $input[$id_dash] ) ) {

                         // Strip all HTML and PHP tags and properly handle quoted strings
                         $output[$id_dash] = strip_tags( stripslashes( $input[ $id_dash ] ) );

                     }
                 }else if ($dashtitle_callback[5] == 'boolean' ){
                     $output[$id_dash] = isset( $input[$id_dash] ) ? true : false;

                 }

             }*/

    
           // return $output;
      /*  foreach($_FILES['hum_import']['tmp_name'] as $key => $tmp_name)
        {
            $file_name = $key.$_FILES['hum_import']['name'][$key];
            $urls = wp_handle_upload($key.$_FILES['hum_import']['name'][$key], array('test_form' => FALSE));
            $temp = $urls["url"];
            $input = $temp;
        }*/
      /* if(!empty($_FILES["hmu_import['upload_file']"]["tmp_name"]))
      {
           $urls = wp_handle_upload($_FILES["hmu_import['upload_file']"], array('test_form' => FALSE));
           $temp = $urls["url"];
            return $temp;

        }*/

/*        $output = array();
     if(isset($_POST['btnSubmit'])):
            $output = get_option('hmu_cron');


        if (  empty($output) ) {
            $output['1'] = $input;
        }else {

            foreach ($output as $key => $value) {
                 $count = count($output);
                  if($key < $count) {
                    $output[$key] = $value;
                }else {
                    $output[$key+1] = $input;
                }



            }
        }
        endif;*/


    }
   /* public function inputSanitize( $input )
	{
            // Create our array for storing the validated options
            $output = array();
            
        // Loop through each of the incoming options
        foreach ($this->dahboardFields  as $id_dash => $title_callback) {
                
            // Check to see if the current option has a value. If so, process it.
            if( isset( $input[$id_dash] ) ) {
                
                // Strip all HTML and PHP tags and properly handle quoted strings
                $output[$id_dash] = strip_tags( stripslashes( $input[ $id_dash ] ) );
                    
            } // end if
                
        } // end foreach
            
        // Return the array processing any additional functions filtered by this action
        return $output;

    }*/

	public function adminSectionManager()
	{
		echo 'Import Prices and Users';
    }
    public function dashboardSectionManager ()
    {
        echo 'Dashboard Control';
    }
    public function cronSectionManager ()
    {

    }




    function emailActivationField ($args) {

        $name = $args['label_for'];
		$classes = $args['class'];
        $option_name = $args['option_name'];
        $checkbox = get_option( $option_name );
        $checked_email = isset($checkbox[$name]) ? ($checkbox[$name] ? true : false) : false;
        
        echo '<div id="toggles">
                <input id="checkboxEmail" class="ios-toggle" type="checkbox" name="' . $option_name . '[' . $name . ']" value="1"   ' . ($checked_email  ? "checked": "") . '>
                <label for="checkboxEmail" class="checkbox-label" data-off="off" data-on="on">
                </label>';
    }
    function cronActivationField ($args) {
        
                $name = $args['label_for'];
                $classes = $args['class'];
                $option_name = $args['option_name'];
                $checkbox = get_option( $option_name );
                $checked = isset($checkbox[$name]) ? ($checkbox[$name] ? true : false) : false;

                
                echo '<div id="toggles">
                        <input id="checkboxCron" class="ios-toggle" type="checkbox" name="' . $option_name . '[' . $name . ']" value="1"   ' . ($checked ? "checked": "") . '>
                        <label for="checkboxCron" class="checkbox-label" data-off="off" data-on="on">
                        </label>';
    }

    function cronTimeField ($args) {
       $name = $args['label_for'];
        $classes = $args['class'];
        $option_name = $args['option_name'];
        $value =  get_option( $option_name );
        $cron_value = isset($value[$name]) ? $value[$name]  : 'Select Time';


        echo '
         <select name="' . $option_name . '[' . $name . ']">
            <option value="">'.$cron_value.'</option>
            <option value="every_one_minute">1min</option>
            <option value="hourly">hourly</option>
             <option value="twicedaily">twicedaily</option>
            <option value="Daily">Daily</option>
            
           
          </select><br>
                       ';

    }
    function cronNameField ($args) {
        $name = $args['label_for'];
        $classes = $args['class'];
        $option_name = $args['option_name'];
        $value =  get_option( $option_name );
        $isvalue = isset($value[$name]) ? $value[$name]  : '';
        $this->cron_name = $isvalue;

            echo '<input type="text" class="regular-text hmu-input" name="'. $option_name.'['.$name.']"  value="' . $isvalue . '"  placeholder="Name of the task">';


    }

    function cronFile($args) {

        $name = $args['label_for'];
        $classes = $args['class'];
        $option_name = $args['option_name'];
        $value =  get_option( $option_name );


        if( empty($value[$name]) ){
            echo '<button type="button" class="button button-secondary" value="Upload Profile Picture" id="upload-button">
                    <span class="sunset-icon-button dashicons-before dashicons-format-image"></span> Upload Cron File</button>
                    <input type="hidden" id="profile-picture" name="'. $option_name.'['.$name.']"  value="" />';
        } else {
            echo '<button type="button" class="button button-secondary" value="Replace cron file" id="upload-button">
                    <span class="sunset-icon-button dashicons-before dashicons-format-image"></span> Replace Cron File</button>
                    <input type="hidden" id="profile-picture" name="'. $option_name.'['.$name.']" value="'.esc_attr($value[$name]).'" />
                     <button type="button" class="button button-secondary" value="Remove" id="remove-picture">
                     <span class="sunset-icon-button dashicons-before dashicons-no"></span> Remove</button>';
        }

    }
    function cronURL ($args) {
        $name = $args['label_for'];
        $classes = $args['class'];
        $option_name = $args['option_name'];
        $value =  get_option( $option_name );
        $isvalue = isset($value[$name]) ? $value[$name]  : '';
        $this->cron_name = $isvalue;

        echo '<input type="text" class="regular-text hmu-input" name="'. $option_name.'['.$name.']"  value="' . $isvalue . '"  placeholder="File url">';


    }
    function cronUpload ($args) {



        $name = $args['label_for'];
        $classes = $args['class'];
        $option_name = $args['option_name'];
        $value =  get_option( $option_name );
        $isvalue = isset($value[$name]) ? $value[$name]  : '';
        $this->cron_name = $isvalue;


     echo '<input  type="file" name="'. $option_name.'" id="hmu_import" value="' . $isvalue . '"  />';
     //echo get_option("hmu_import");


    }
    function cronFunction ($args) {

        $name = $args['label_for'];
        $classes = $args['class'];
        $option_name = $args['option_name'];
        $value =  get_option( $option_name );
        $cron_value = isset($value[$name]) ? $value[$name]  : 'Select Function';


        echo '
         <select name="' . $option_name . '[' . $name . ']">
            <option value="">'.$cron_value.'</option>
            <option value="insert-location">Insert Locations</option>
            <option value="insert-users">Insert Users</option>
             <option value="insert-products">Insert Products</option>
            <option value="insert-prices">Insert Prices</option>
            
           
          </select><br>
                       ';


    }




    
}
?>
