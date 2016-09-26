<?php

/*
Plugin Name: wpiu
Plugin URI: 
Description: 
Version: 1.0.0 
Author: Nils
Author URI: :banana:
*/

/*
* Enclose all your functions in a class.
* Main Class - WPIU stands for WordPress Image Uploader.
*/

Class WPIU {
/* --------------------------------------------*
* Attributes
* -------------------------------------------- */

/** Refers to a single instance of this class. */

private static $instance = null;

/* Saved options */
public $options;

/* --------------------------------------------*
* Constructor
* -------------------------------------------- */

/**
* Creates or returns an instance of this class.
*
* @return WPIU_Theme_Options A single instance of this class.
*/
public static function get_instance() {

if (null == self::$instance) {
self::$instance = new self;
}

return self::$instance;
}

// end get_instance;

/**
* Initialize the plugin by setting localization, filters, and administration functions.
*/
private function __construct() {
// Add the page to the admin menu.
add_action('admin_menu', array(&$this, 'image_menu_page'));

// Register javascript.
add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_js'));

// Add function on admin initalization.
add_action('admin_init', array(&$this, 'my_options_setup'));

// Call Function to store value into database.
add_action('init', array(&$this, 'store_in_database'));

// Call Function to delete image.
add_action('init', array(&$this, 'delete_image'));

// Add CSS rule.
add_action('admin_enqueue_scripts', array(&$this, 'add_stylesheet'));
}

/* --------------------------------------------*
* Functions
* -------------------------------------------- */

/**
* Function will add option page under Appearance Menu.
*/
public function image_menu_page() {
add_theme_page('media_uploader', 'Frontpage image', 'edit_theme_options', 'media_page', array($this, 'media_uploader'));
}

//Function that will display the options page.

public function media_uploader() {
global $wpdb;
$img_path = get_option('frontpage_image');
?>
<style>
	.ulimg
	{
		max-width: 90%;
	}
	</style>
<div class="wrap">	
	<form class="my_image" method="post" action="#">
	<h2> <b>Selecteer een beeldbestand voor de frontpage: </b></h2>
	<p>(Formaat: 1140px x 300px. )</p>
	<input type="text" name="path" class="image_path widefat" value="<?php echo $img_path; ?>" id="image_path">
	<input type="button" value="Upload Image" class="button-primary" id="upload_image"/><br />
	<div id="show_upload_preview">
	
	<?php if(! empty($img_path)){
	?>
	<p>Huidig frontpage bestand:</p>
	<hr />
	<img src="<?php echo $img_path ; ?>" class="img-responsive ulimg" />
	<input type="submit" name="remove" value="Remove Image" class="button-secondary " id="remove_image"/>
	<?php } ?>
	</div>
	<input type="submit" name="submit" class="save_path button-primary" id="submit_button" value="Gegevens opslaan">
	
	</form>
</div>
<?php
}

//Call three JavaScript library (jquery, media-upload and thickbox) and one CSS for thickbox in the admin head.

public function enqueue_admin_js() {
wp_enqueue_script('media-upload'); //Provides all the functions needed to upload, validate and give format to files.
wp_enqueue_script('thickbox'); //Responsible for managing the modal window.
wp_enqueue_style('thickbox'); //Provides the styles needed for this window.
wp_enqueue_script('script', plugins_url('upload.js', __FILE__), array('jquery'), '', true); //It will initialize the parameters needed to show the window properly.
}

//Function that will add stylesheet file.
public function add_stylesheet(){
wp_enqueue_style( 'stylesheet', plugins_url( 'stylesheet.css', __FILE__ ));
}

// Here the pages we are working with are checked to be sure they are the ones used by the Media Uploader.
public function my_options_setup() {
global $pagenow;
if ('media-upload.php' == $pagenow || 'async-upload.php' == $pagenow) {
// Now we will replace the 'Insert into Post Button inside Thickbox'
add_filter('gettext', array($this, 'replace_window_text'), 1, 2);
// add_filter ( 'action_tag' , array( $this , 'my_callback' ) , 30 );
// gettext filter and every sentence.
}
}

/*
* Referer parameter in our script file is for to know from which page we are launching the Media Uploader as we want to change the text "Insert into Post".
*/
function replace_window_text($translated_text, $text) {
if ('Insert into Post' == $text) {
$referer = strpos(wp_get_referer(), 'media_page');
if ($referer != '') {
return __('Upload Image', 'my');
}
}
return $translated_text;
}

// The Function store image path in option table.
public function store_in_database(){
if(isset($_POST['submit'])){
$image_path = $_POST['path'];
update_option('frontpage_image', $image_path);
}
}

// The Below Function Will Delete The Image.
function delete_image() {
if(isset($_POST['remove'])){
global $wpdb;
$img_path = $_POST['path'];

// We need to get the images meta ID.
$query = "SELECT ID FROM wwwwsp_posts where guid = '" . esc_url($img_path) . "' AND post_type = 'attachment'";
$results = $wpdb->get_results($query);

// And delete it
foreach ( $results as $row ) {
 wp_delete_attachment( $row->ID ); //delete the image and also delete the attachment from the Media Library.
}
delete_option('frontpage_image'); //delete image path from database.
}
}

}
// End class

WPIU::get_instance();

?>
