<?php 
?>
<h1>
<?php echo esc_html( get_admin_page_title() ); ?>

</h1>
<div class="wrap">
<?php settings_errors(); ?>

<form method="post" class="hmu-general-form" action="options.php" enctype="multipart/form-data">
	<?php 
		settings_fields( 'hmu_import_options_group' );
		do_settings_sections( 'import_users' );
		submit_button( 'Upload Users', '', 'btnSubmit' );
	?>

</form>
    <?php echo get_option("hmu_import"); ?>
<?php
/*$option = get_option ('hmu_import');
@$picture = $option["profile_picture"];
 */?><!--
<?php /*if ($picture !=='') : */?>
<div id="profile-picture-preview" class="profile-picture" >
<img src="<?php /*echo $picture; */?>" alt="">
</div>
    <?php /*endif; */?>
</div>-->
<?php




