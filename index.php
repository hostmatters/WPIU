<?php
/*
Plugin Name: Wordpress IMG UPLOADER
Plugin URI: 
Description: 
Version: 0.2 
Author: Niels
Author URI: url.com
*/

Class WPIU {
	private static $instance = null;
	public $options;
	public static function get_instance() {

		if (null == self::$instance) {
			self::$instance = new self;
		}
	
		return self::$instance;
	}

	private function __construct() {
		add_action('admin_menu', array(&$this, 'image_menu_page'));
		add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_js'));
		add_action('admin_init', array(&$this, 'my_options_setup'));
		add_action('init', array(&$this, 'store_in_database'));
		add_action('init', array(&$this, 'delete_image'));
		add_action('admin_enqueue_scripts', array(&$this, 'add_stylesheet'));
	}


	public function image_menu_page() {
		add_theme_page('media_uploader', 'Frontpage image', 'edit_theme_options', 'media_page', array($this, 'media_uploader'));
	}

	public function media_uploader() {
		global $wpdb;
		$img_path = get_option('frontpage_image');
		$frontpage_url = get_option('frontpage_url');
		$frontpage_text = get_option('frontpage_text');
		$frontpage_header = get_option('frontpage_header');

?>

	<!-- table -->
		<form class="my_image" method="post" action="#">
		<div id="wrap">
			<table class="widefat " cellspacing="5">
				<tr>
		            <td class="manage-column " colspan="2">
            			<h2> <b>Selecteer een beeldbestand voor de frontpage: </b></h2>
						<p>(Formaat: 1140px x 300px. )</p>
						<input type="text" name="path" class="image_path widefat" value="<?php echo $img_path; ?>" id="image_path">
						<input type="button" value="Upload Image" class="button-primary" id="upload_image"/>
		            </td>

				</tr>
				
				<tr>
		            <td class="manage-column "  colspan="2">
			         			<div id="show_upload_preview">
									<?php if(! empty($img_path)){ ?>
									<p>Huidig frontpage bestand:</p>
									<hr />
									<img src="<?php echo $img_path ; ?>" class="img-responsive ulimg" />
									<input type="submit" name="remove" value="Remove Image" class="button-secondary right " id="remove_image"/>
								<?php } ?>
								</div>   
			            
		            </td>
				</tr>    
				
				
				<tr>
		            <td class="manage-column " ><strong class="widefat">Header Text: </strong></td>
		            <td class="manage-column " ><input type="text" name="front_header" class="widefat" value="<?php echo $frontpage_header; ?>" id="front_header"></td>
	
				</tr> 			

				<tr>
		            <td class="manage-column " ><strong class="widefat">link: </strong></td>
		            <td class="manage-column " ><input type="text" name="front_link" class="widefat" value="<?php echo $frontpage_url; ?>" id="front_link"></th>
	
				</tr> 
				<tr>
		            <td class="manage-column " ><strong>Text: </strong></td>
		            <td class="manage-column1 " ><input type="text" name="front_text" class="widefat" value="<?php echo $frontpage_text; ?>" id="front_text"></th>
	
				</tr> 
				<tr>
		            
		            <td class="manage-column" colspan="2">
			            <input type="submit" name="submit" class="save_path button-primary right" id="submit_button" value="Gegevens opslaan">
			        </td>
	
				</tr> 								
			</table>
		
		
	</div>
	</form>	
	<!-- -->
	

<?php
}

	public function enqueue_admin_js() {
		wp_enqueue_script('media-upload'); //Provides all the functions needed to upload, validate and give format to files.
		wp_enqueue_script('thickbox'); //Responsible for managing the modal window.
		wp_enqueue_style('thickbox'); //Provides the styles needed for this window.
		wp_enqueue_script('script', plugins_url('upload.js', __FILE__), array('jquery'), '', true); 
	}

	public function add_stylesheet(){
		wp_enqueue_style( 'stylesheet', plugins_url( 'stylesheet.css', __FILE__ ));
	}

	public function my_options_setup() {
		global $pagenow;
		if ('media-upload.php' == $pagenow || 'async-upload.php' == $pagenow) {
			add_filter('gettext', array($this, 'replace_window_text'), 1, 2);
		}
	}

	function replace_window_text($translated_text, $text) {
		if ('Insert into Post' == $text) {
			$referer = strpos(wp_get_referer(), 'media_page');
			if ($referer != '') {
				return __('Upload Image', 'my');
			}
		}
		return $translated_text;
	}

	public function store_in_database(){
		if(isset($_POST['submit'])){
			$image_path = $_POST['path'];
			update_option('frontpage_image', $image_path);
			
			$frontpage_url = $_POST['front_link'];
			update_option('frontpage_url', $frontpage_url);
			
			$frontpage_text = $_POST['front_text'];
			update_option('frontpage_text', $frontpage_text);
			
			
			$frontpage_header = $_POST['front_header'];
			update_option('frontpage_header', $frontpage_header);						
			
		}
	}

	function delete_image() {
		if(isset($_POST['remove'])){
			global $wpdb;
			$img_path = $_POST['path'];
			
			$query = "SELECT ID FROM wwwwsp_posts where guid = '" . esc_url($img_path) . "' AND post_type = 'attachment'";
			$results = $wpdb->get_results($query);
			
				foreach ( $results as $row ) {
				 wp_delete_attachment( $row->ID ); 
				}
				
				delete_option('frontpage_image'); //delete image path from database.
			}
		}
	
	}
WPIU::get_instance();
?>