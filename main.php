<?php

/*
  Plugin Name: WP-Rental-Listing
  Plugin URI: http://sabirul-mostofa.blogspot.com
  Description: Import Rental Listing from Craigslist
  Version: 1.0
  Author: Sabirul Mostofa
  Author URI: http://sabirul-mostofa.blogspot.com
 */

//include 'featured-post-widget.php';
$wpRentalImport = new wpRentalImport();

class wpRentalImport {

    public $table = '';
    public $image_dir = '';
    public $prefix = 'wprent';
    public $meta_box = array();

    function __construct() {
        global $wpdb;
        //$this->set_meta();
        $this->table = $wpdb->prefix . 'rental_city_list';
        $this->image_dir = plugins_url('/', __FILE__) . 'images/';
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'front_scripts'));
        add_action('wp_print_styles', array($this, 'front_css'));
        add_action('admin_menu', array($this, 'CreateMenu'), 50);
        add_action('wp_ajax_city_remove', array($this, 'ajax_remove_city'));
        register_activation_hook(__FILE__, array($this, 'create_table'));
    }

    function CreateMenu() {
        add_submenu_page('options-general.php', 'Rental City Settings', 'Rental Settings', 'activate_plugins', 'wpRentalImport', array($this, 'OptionsPage'));
    }

    function OptionsPage() {
        include 'options-page.php';
    }

      function admin_scripts() {    
            wp_enqueue_script('rt_admin_script', plugins_url('/', __FILE__) . 'js/script_admin.js');
            wp_register_style('rt_admin_css', plugins_url('/', __FILE__) . 'css/style_admin.css', false, '1.0.0');
            wp_enqueue_style('rt_admin_css');
        
    }
    function front_scripts() {
        global $post;
        if (is_page() || is_single()) {
            wp_enqueue_script('jquery');
            if (!(is_admin())) {
                // wp_enqueue_script('wpvr_boxy_script', plugins_url('/' , __FILE__).'js/boxy/src/javascripts/jquery.boxy.js');
                wp_enqueue_script('wpvr_front_script', plugins_url('/', __FILE__) . 'js/script_front.js');
            }
        }
    }

    function front_css() {
        if (!(is_admin())):
            wp_enqueue_style('wpvr_front_css', plugins_url('/', __FILE__) . 'css/style_front.css');
        endif;
    }
    
    function ajax_remove_city(){
        $id = $_POST['id'];
       
        global $wpdb;
        $res = $wpdb->query("delete from $this->table where id=$id");
        if($res)
            echo 1;
        
     
        exit;
    }

    function create_table() {
        global $wpdb;
        $sql = "CREATE TABLE IF NOT EXISTS $this->table  (
		`id` int unsigned NOT NULL AUTO_INCREMENT,		
		`city_name` varchar(30)  NOT NULL,	
		`city_url` varchar(60)  NOT NULL,	
		 PRIMARY KEY (`id`),				 	
		 key `city_name`(`city_name`)		 	
		)";



        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
    }

}
