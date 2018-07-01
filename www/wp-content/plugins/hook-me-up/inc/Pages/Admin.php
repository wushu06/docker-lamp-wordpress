<?php 

namespace Inc\Pages; 

use \Inc\Base\BaseController;

use \Inc\Api\SettingsApi;

use \Inc\Api\Callbacks\AdminCallbacks;

use \Inc\Api\Callbacks\FieldsCallbacks;

class Admin extends BaseController {

    public $settings;
	public $admin_callbacks;
	public $fields_callbacks;
    public $pages = array();
	public $subpages = array();

   

    function register() {
		

		$this->settings = new SettingsApi();
		
				$this->admin_callbacks = new AdminCallbacks();
				$this->fields_callbacks = new FieldsCallbacks();
		
				$this->set_pages();
		
				$this->setSubpages();
		
				$this->setSettings();
				$this->setSections();
				$this->setFields();
		
				$this->settings->add_pages( $this->pages )->withSubPage( 'Dashboard' )->addSubPages( $this->subpages )->register();

				
    }

    /*
    * create menu 
    */
        
    function set_pages () {
        $this->pages = array(
            array(
                    'page_title' => 'Hook Me Up', 
                    'menu_title' => 'Hook Me Up', 
                    'capability' => 'manage_options', 
                    'menu_slug' => 'hmu_plugin', 
                    'callback' => array( $this->admin_callbacks, 'hmu_plugin' ), 
                    'icon_url' => $this->plugin_url.'assets/images/hook.png',
                    'position' => 110
                )
            );
  
    

	}
	public function setSubpages()
	{
		foreach ($this->subpagesOutput as $slug => $title_callback) {
			    $this->subpages[] = array (
				'parent_slug' => 'hmu_plugin', 
				'page_title' => $title_callback[0], 
				'menu_title' => $title_callback[0], 
				'capability' => 'manage_options', 
				'menu_slug' => $slug, 
				'callback' => array( $this->admin_callbacks, $title_callback[1] ), 
			);

		}

	}
    /*
    * create fields
    */
	public function setSettings()
	{
        /*
         * # for each page create group of fields and give each group option name
         * #
         */


		$args = array(
			array(
				'option_group' => 'hmu_dashboard_options_group',
				'option_name' => 'hmu_dashboard',
				//'callback' => array( $this->fields_callbacks,'sanitizeCallback' )
			),
            array(
                'option_group' => 'hmu_import_options_group',
                'option_name' => 'hmu_import',
                'callback' => array( $this->fields_callbacks, 'sanitizeCallback2' )
            ),
            array(
                'option_group' => 'hmu_cron_options_group',
                'option_name' => 'hmu_cron',
                'callback' => array( $this->fields_callbacks, 'sanitizeCallback2' )
            )
			
		);
	
		

		$this->settings->setSettings( $args );
	}

	public function setSections()
	{
		$args = array(
            array(
                'id' => 'hmu_dashboard_index',
                'title' => 'Dashboard',
                'callback' => array( $this->fields_callbacks, 'dashboardSectionManager' ),
                'page' => 'hmu_plugin' //dahboard page
            ),
			array(
				'id' => 'hmu_import_index',
				'title' => 'Upload Settings',
				'callback' => array( $this->fields_callbacks, 'adminSectionManager' ),
				'page' => 'import_users'
			),
           array(
                'id' => 'hmu_cron_index',
                'title' => 'Cron Tasks',
                'callback' => array( $this->fields_callbacks, 'cronSectionManager' ),
                'page' => 'cron_task'
            )
		);

		$this->settings->setSections( $args );
	}

	public function setFields()
	{
		$args = array ();

		/*foreach ($this->fieldsOutput as $id => $title_callback ) {

			$args[] = array (
				'id' => $id,
				'title' => $title_callback[0],
				'callback' => array( $this->fields_callbacks, $title_callback[1] ),
				'page' => 'import_users',
				'section' => 'hmu_admin_index',
					'args' => array(
						'option_name' => 'hmu_plugin',
						'label_for' => $id,
						'class' => 'hmu-upload'
					)
				);
		}*/

		foreach ($this->dahboardFields   as $id_dash => $dashtitle_callback ) {
			
			$args[] = array (
				'id' => $id_dash,
				'title' => $dashtitle_callback[0],
				'callback' => array( $this->fields_callbacks, $dashtitle_callback[1] ),
				'page' => $dashtitle_callback[2],
				'section' => $dashtitle_callback[3],
					'args' => array(
						'option_name' => $dashtitle_callback[4],
						'label_for' => $id_dash,
						'class' => 'hmu-upload'
					)
				);
		}
		

		$this->settings->setFields( $args );
	}

}