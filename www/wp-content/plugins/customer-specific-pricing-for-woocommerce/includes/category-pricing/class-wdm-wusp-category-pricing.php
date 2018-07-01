<?php

namespace cspCategoryPricing;

if (!class_exists('WdmWuspCategoryPricing')) {
    class WdmWuspCategoryPricing
    {
    	private static $product_categories;
    	private static $discountOptions;
    	private static $minusIcon;
    	private static $plusIcon;
        public function __construct()
        {
        	self::$minusIcon   = plugins_url('/images/minus-icon.png', dirname(dirname(__FILE__)));
			self::$plusIcon      = plugins_url('/images/plus-icon.png', dirname(dirname(__FILE__)));
        	$catArgs = array(
			    'order'      => 'ASC',
			    'hide_empty' => 0,
			    'posts_per_page' =>'-1'
			);

        	self::$product_categories = get_terms( 'product_cat', $catArgs );
        	self::$discountOptions = array('-1' => __('Discount Type', CSP_TD),'1'=>__('Flat', CSP_TD), '2'=>'%');
        	add_action('admin_enqueue_scripts', array($this, 'cspCategoryEnqueueScripts'), 15);
        	add_action('csp_show_user_data', array($this, 'showUserPricingRecords'), 10);
        	add_action('csp_show_role_data', array($this, 'showRolePricingRecords'), 10);
        	add_action('csp_show_group_data', array($this, 'showGroupPricingRecords'), 10);
        	add_action('admin_init', array($this, 'saveCategoryRecords'), 10);
        }

        public function saveCategoryRecords()
        {
        	// page=customer_specific_pricing_single_view&tabie=category_pricing

        	if (!isset($_POST['save_records'])) {
        		return;
        	}

        	if (isset($_REQUEST['page']) && $_REQUEST['page'] != 'customer_specific_pricing_single_view' && isset($_REQUEST['tabie']) &&  $_REQUEST['tabie'] != 'category_pricing') {
        		return;
        	}

        	$nonce = $_REQUEST['_save_category'];
        	$nonce_verification = wp_verify_nonce($nonce, 'csp_save_category_pricing');
        	if (! $nonce_verification) {
				echo "Security Check";
				exit;
        	}
     	
        	$this->saveUserRecords();
        	$this->saveRoleRecords();

            $active_plugins  = apply_filters('active_plugins', get_option('active_plugins'));
            if (in_array('groups/groups.php', $active_plugins)) {
                $this->saveGroupRecords();
            }
        }

        public function saveUserRecords()
        {
        	global $addCatRecords, $deleteCatRecords;        	

        	$userCatArray = isset($_POST['wdm_woo_user_category']) ? $_POST['wdm_woo_user_category'] : array();
        	$userIdsArray = isset($_POST['wdm_woo_username']) ? $_POST['wdm_woo_username'] : array();
        	$userPriceArray = isset($_POST['wdm_user_value']) ? $_POST['wdm_user_value'] : array();
        	$userMinQtyArray = isset($_POST['wdm_user_qty']) ? $_POST['wdm_user_qty'] : array();
        	$userTypeArray = isset($_POST['wdm_user_price_type']) ? $_POST['wdm_user_price_type'] : array();

        	// $userRecords = $this->filterUnselectedRecords($userCatArray,)

        	if (empty($userIdsArray)) {
        		//delete user records
        		$deleteCatRecords->deleteAllUserRecords();
        	} else {
        		$addCatRecords->addUserCategoryRecords($userCatArray, $userIdsArray, $userPriceArray, $userMinQtyArray, $userTypeArray);
        	}
        }

        public function saveRoleRecords()
        {
        	global $addCatRecords, $deleteCatRecords;        	
        	$roleCatArray = isset($_POST['wdm_woo_role_category']) ? $_POST['wdm_woo_role_category'] : array();
        	$rolesArray = isset($_POST['wdm_woo_roles']) ? $_POST['wdm_woo_roles'] : array();
        	$rolePriceArray = isset($_POST['wdm_role_value']) ? $_POST['wdm_role_value'] : array();
        	$roleMinQtyArray = isset($_POST['wdm_role_qty']) ? $_POST['wdm_role_qty'] : array();
        	$roleTypeArray = isset($_POST['wdm_role_price_type']) ? $_POST['wdm_role_price_type'] : array();

        	if (empty($rolesArray)) {
        		//delete role records
        		$deleteCatRecords->deleteAllRoleRecords();
        	} else {
        		$addCatRecords->addRoleCategoryRecords($roleCatArray, $rolesArray, $rolePriceArray, $roleMinQtyArray, $roleTypeArray);
        	}
        }

        public function saveGroupRecords()
        {
        	global $addCatRecords, $deleteCatRecords;
        	$groupCatArray = isset($_POST['wdm_woo_group_category']) ? $_POST['wdm_woo_group_category'] : array();
        	$groupIdsArray = isset($_POST['wdm_woo_groupname']) ? $_POST['wdm_woo_groupname'] : array();
        	$groupPriceArray = isset($_POST['wdm_group_value']) ? $_POST['wdm_group_value'] : array();
        	$groupMinQtyArray = isset($_POST['wdm_group_qty']) ? $_POST['wdm_group_qty'] : array();
        	$groupTypeArray = isset($_POST['wdm_group_price_type']) ? $_POST['wdm_group_price_type'] : array();

        	if (empty($groupIdsArray)) {
        		//delete group records
        		$deleteCatRecords->deleteAllGroupRecords();
        	} else {
        		$addCatRecords->addGroupCategoryRecords($groupCatArray, $groupIdsArray, $groupPriceArray, $groupMinQtyArray, $groupTypeArray);
        	}
        }

        public function removeUnselectedRecords($userRecords)
        {
			return array_filter($userRecords, function($value, $key) {
			    return $value !== '-1';
			}, ARRAY_FILTER_USE_BOTH);
        }

        public function cspCategoryEnqueueScripts()
        {
        	if (isset($_GET['page']) && isset($_GET['tabie']) && $_GET['tabie'] == 'category_pricing') {
	        	wp_enqueue_script('jquery-ui-accordion');
	        	wp_enqueue_style('jquery-ui-style');

	        	wp_enqueue_script(
	                'wdm_csp_cat_pricing_js',
	                plugins_url('/js/category-js/wdm-csp-cat-pricing-script.js', dirname(dirname(__FILE__))),
	                array('jquery', 'jquery-ui-accordion'),
	                CSP_VERSION
	            );

	            wp_localize_script('wdm_csp_cat_pricing_js', 'cat_pricing_object', array(
	            	'loading_image_path' => plugins_url('/images/loading .gif', dirname(dirname(__FILE__))),
	            	'add_image_path' => self::$plusIcon,
	            	'remove_image_path' => self::$minusIcon,
				));

	        	wp_enqueue_style(
	                'wdm_csp_cat_pricing_css',
	                plugins_url('/css/category-css/wdm-csp-cat-pricing-style.css', dirname(dirname(__FILE__))),
	                array(),
	                CSP_VERSION
	            );
	        }
        }

        public function cspShowCategoryPricing()
        {
	        include_once('category-template.php');
        }

        public function isLastRecord($ctr, $length)
        {
        	return $ctr == $length ? true : false ;
        }

        public function isPercentValue($priceType)
        {
        	return $priceType == 2 ? true : false ;
        }

        public function showUserPricingRecords()
        {
			global $getCatRecords;
			$userData = $getCatRecords->getAllUserCategoryPricingPairs();

			if (empty($userData)) {
				$this->userHtml();
				return;
			}

			$ctr = 0;
			$length = count($userData);

			foreach ($userData as $userRecord) {
				$addbutton = $this->isLastRecord(++$ctr, $length);
				$this->userHtml($addbutton, $userRecord->user_id, $userRecord->cat_slug, $userRecord->price_type, $userRecord->min_qty, $userRecord->price);
			}
        	?>

			<?php
        }

        public function showRolePricingRecords()
        {
        	global $getCatRecords;
			$roleData = $getCatRecords->getAllRolesCategoryPricingPairs();
			
			if (empty($roleData)) {
				$this->roleHtml();
				return;
			}

			$ctr = 0;
			$length = count($roleData);

			foreach ($roleData as $roleRecord) {
				$addbutton = $this->isLastRecord(++$ctr, $length);
				$this->roleHtml($addbutton, $roleRecord->role, $roleRecord->cat_slug, $roleRecord->price_type, $roleRecord->min_qty, $roleRecord->price);
     	
			}
        }

        public function showGroupPricingRecords()
        {
        	global $getCatRecords;
			$groupData = $getCatRecords->getAllGroupCategoryPricingPairs();

			if (empty($groupData)) {
				$this->groupHtml();
				return;
			}

			$ctr = 0;
			$length = count($groupData);

			foreach ($groupData as $groupRecord) {
				$addbutton = $this->isLastRecord(++$ctr, $length);
        		$this->groupHtml($addbutton, $groupRecord->group_id, $groupRecord->cat_slug, $groupRecord->price_type, $groupRecord->min_qty, $groupRecord->price);
			}
        }

        public function userHtml($addbutton = true, $user = "", $catSlug = "", $priceType = "", $minQty = "", $value = "")
        {
			$categoryName = "wdm_woo_user_category[]";
			$userName = "wdm_user_price_type[]";
			$valueName = "wdm_user_value[]";
			$qtyName = "wdm_user_qty[]";
			$valueClasses = $this->isPercentValue($priceType) ? "csp-percent-discount wdm_price" : "wdm_price" ;
			$typeClasses = "csp_wdm_action";
			$qtyClasses = "wdm_qty";
			?>
			<div class="category-row user-row">
				<?php $this->generateUserDropdown($user); ?>
				<select name="<?php echo $categoryName; ?>">
				<?php $this->generateCategoryOptions($catSlug); ?>
				</select>
				<select class = "<?php echo $typeClasses; ?>" name="<?php echo $userName; ?>">
				<?php $this->generateDiscountOptions($priceType); ?>
				</select>
				<div>
					<input type="number" min="1" class = "<?php echo $qtyClasses; ?>" name="<?php echo $qtyName; ?>" placeholder="Min Qty" value = "<?php echo $minQty; ?>" />
					<input type="text" class = "<?php echo $valueClasses; ?>" name="<?php echo $valueName; ?>" placeholder="Value" value = "<?php echo $value; ?>" />
					<span class = "add_remove_button">
						<img class="remove_user_row_image" alt="Remove Row" title="Remove Row" src="<?php echo self::$minusIcon; ?>" />
						<?php if ($addbutton) { ?><img class='add_new_user_row_image' title="Add Row" src='<?php echo self::$plusIcon; ?>' /><?php } ?>
					</span>
				</div>
			</div>
			<?php
        }

        public function roleHtml($addbutton = true, $role = "", $catSlug = "", $priceType = "", $minQty = "", $value = "")
        {
			$valueClasses = $this->isPercentValue($priceType) ? "csp-percent-discount wdm_price" : "wdm_price" ;
			$typeClasses = "csp_wdm_action";
			$qtyClasses = "wdm_qty";
        	?>
			<div class="category-row role-row">
				<?php echo $this->generateRoleDropdown($role); ?>
				<select name='wdm_woo_role_category[]'>
				<?php $this->generateCategoryOptions($catSlug); ?>
				</select>
				<select class = "<?php echo $typeClasses; ?>" name='wdm_role_price_type[]'>
				<?php $this->generateDiscountOptions($priceType); ?>
				</select>
				<div>
					<input type="number" min="1" class = "<?php echo $qtyClasses; ?>" name="wdm_role_qty[]" placeholder="Min Qty" value = "<?php echo $minQty; ?>" />
					<input type="text" class = "<?php echo $valueClasses; ?>" name="wdm_role_value[]" placeholder="Value" value = "<?php echo $value; ?>" />
					<span class = "add_remove_button">
						<img class="remove_role_row_image" alt="Remove Row" title="Remove Row" src="<?php echo self::$minusIcon; ?>" />
						<?php if ($addbutton) { ?><img class="add_new_role_row_image" src="<?php echo self::$plusIcon; ?>" /><?php } ?>
					</span>
				</div>
			</div>
			<?php
        }

        public function groupHtml($addbutton = true, $groupId = "", $catSlug = "", $priceType = "", $minQty = "", $value = "")
        {
			$valueClasses = $this->isPercentValue($priceType) ? "csp-percent-discount wdm_price" : "wdm_price" ;
			$typeClasses = "csp_wdm_action";
			$qtyClasses = "wdm_qty";
        	?>
			<div class="category-row group-row">
				<?php $this->generateGroupDropdown($groupId); ?>
				<select name='wdm_woo_group_category[]'>
				<?php $this->generateCategoryOptions($catSlug); ?>
				</select>
				<select class = "<?php echo $typeClasses; ?>" name='wdm_group_price_type[]'>
				<?php $this->generateDiscountOptions($priceType); ?>
				</select>
				<div>
					<input type="number" min="1" class = "<?php echo $qtyClasses; ?>" name="wdm_group_qty[]" placeholder="Min Qty" value = "<?php echo $minQty; ?>" />
					<input type="text" class = "<?php echo $valueClasses; ?>" name="wdm_group_value[]" placeholder="Value" value = "<?php echo $value; ?>" />
					<span class = "add_remove_button">
						<img class="remove_group_row_image" alt="Remove Row" title="Remove Row" src="<?php echo self::$minusIcon; ?>">
						<?php if ($addbutton) { ?><img class="add_new_group_row_image" src="<?php echo self::$plusIcon; ?>" /><?php } ?>
					</span>
				</div>
			</div>
			<?php
        }

        public function generateUserDropdown($user = "")
        {
        	global $wp_version;

        	// Fall back for wordpress version below 4.5.0
			$show_user = 'user_login';
			
			if ($wp_version >= '4.5.0') {
			    $show_user = 'display_name_with_login';
			}

			$userArgs = apply_filters('wdm_usp_user_dropdown_params', array(
			    'show_option_all'        => null, // string
			    'show_option_none'       => 'Select User', // string
			    'hide_if_only_one_author'    => null, // string
			    'orderby'            => 'display_name',
			    'order'              => 'ASC',
			    'include'            => null, // string
			    'exclude'            => null, // string
			    'multi'              => false,
			    'show'               => $show_user,
			    'echo'               => false,
			    'selected'           => $user,
			    'include_selected'       => false,
			    'name'               => 'wdm_woo_username[]', // string
			    'id'                 => null, // integer
			    'class'              => 'chosen-select', // string
			    'blog_id'            => $GLOBALS['blog_id'],
			    'who'                => null, // string
			    ));

			echo wp_dropdown_users($userArgs);
        }

        public function generateRoleDropdown($role = "")
        {
        	?>
			<select id = "wdm_woo_roles" name = 'wdm_woo_roles[]'>
				<option value="-1">Select Role</option>
			<?php
	        	ob_start();
				$wdm_dropdown_content    = wp_dropdown_roles($role);
				$wdm_roles_dropdown  = ob_get_contents();
				ob_end_clean();
				echo $wdm_roles_dropdown;
			?>
			</select>
			<?php
        }

        public function generateGroupDropdown($groupId)
        {
        	global $wpdb;
        	$wdm_group_name_mapping      = $wpdb->prefix . 'groups_group';
			$array_of_groupid_name_pair  = $wpdb->get_results("SELECT group_id, name FROM {$wdm_group_name_mapping}");
			?>	
			<select id = "wdm_woo_groups" name='wdm_woo_groupname[]' class='chosen-select'>
					<option value="-1">Select Group</option>
			<?php
			foreach ($array_of_groupid_name_pair as $single_groupid_name_pair) {
			    if ($groupId == $single_groupid_name_pair->group_id) {
			    	echo "<option value=" . $single_groupid_name_pair->group_id . ' selected>' . esc_html($single_groupid_name_pair->name) . "</option>";
			    } else {
			    	echo "<option value=" . $single_groupid_name_pair->group_id . ' >' . esc_html($single_groupid_name_pair->name) . "</option>";
			    }
			}
			?>
			</select>
			<?php
        }

        public function generateDiscountOptions($discountType = -1)
        {
			foreach (self::$discountOptions as $key => $value) {
				if ($discountType == $key) {
					echo "<option value = '" . $key . "' selected>" . $value . "</option>";
				} else {
					echo "<option value = '" . $key . "'>" . $value . "</option>";
				}
			}
        }

        public function generateCategoryOptions($catSlug = null)
        {
        	?>
        	<option value="-1">Select Category</option>
			<?php 
			foreach ( self::$product_categories as $category ) {
				if ($catSlug == $category->slug) {
			    	echo "<option value = '" . esc_attr( $category->slug ) . "' selected>" . esc_html( $category->name ) . "</option>";
			    } else {
			    	echo "<option value = '" . esc_attr( $category->slug ) . "'>" . esc_html( $category->name ) . "</option>";
			    }
			}
        }
    }
}
