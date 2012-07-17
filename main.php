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
        $this->table_data = $wpdb->prefix . 'rental_data';
        $this->image_dir = plugins_url('/', __FILE__) . 'images/';
        add_action('init', array($this, 'add_post_type'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'front_scripts'));
        add_action('wp_print_styles', array($this, 'front_css'));
        add_action('admin_menu', array($this, 'CreateMenu'), 50);
        add_action('wp_rental_cron', array($this, 'start_cron'));
        add_action('wp_ajax_city_remove', array($this, 'ajax_remove_city'));
        register_activation_hook(__FILE__, array($this, 'create_table'));
        register_activation_hook(__FILE__, array($this, 'init_cron'));
        register_deactivation_hook(__FILE__, array($this, 'deactivation_tasks'));
    }

    function CreateMenu() {
        add_submenu_page('options-general.php', 'Rental City Settings', 'Rental Settings', 'activate_plugins', 'wpRentalImport', array($this, 'OptionsPage'));
    }

    function OptionsPage() {
        include 'options-page.php';
    }

    //adding post type
    function add_post_type() {
        register_post_type('rentallisting', array(
            'labels' => array(
                'name' => __('Rental Listing'),
                'singular_name' => __('Rental')
            ),
            'public' => true,
            'has_archive' => true,
            'capability_type' => 'post',
            'taxonomies' => array('category', 'post_tag')
                )
        );
    }

    function start_cron() {
        include 'cr-cron.php';
    }

    function init_cron() {
        if (!wp_get_schedule('wp_rental_cron'))
            wp_schedule_event(time(), 'daily', 'wp_rental_cron');
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

    function ajax_remove_city() {
        $id = $_POST['id'];

        global $wpdb;
        $res = $wpdb->query("delete from $this->table where id=$id");
        if ($res)
            echo 1;


        exit;
    }

    function not_in_table($city) {
        global $wpdb;
        $var = $wpdb->get_var("select city_url from $this->table where city_name='$city'");
        if ($var == null)
            return true;
    }

    function create_table() {
        global $wpdb;
        $sql = "CREATE TABLE IF NOT EXISTS $this->table  (
		`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,		
		`city_name` varchar(30)  NOT NULL,	
		`city_url` varchar(60)  NOT NULL,	
		 PRIMARY KEY (`id`),				 	
		 key `city_name`(`city_name`)		 	
		)";

        $sql1 = "CREATE TABLE IF NOT EXISTS $this->table_data  (
		`post_id` bigint(20) unsigned NOT NULL,		
		`city_id` bigint(20) unsigned NOT NULL,		
		`cg_id` bigint(20) unsigned NOT NULL,			
		 PRIMARY KEY (`post_id`),				 	
		 key `post_id`(`city_id`),		 	
		 key `cg_id`(`cg_id`)		 	
		)";



        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
        dbDelta($sql1);


        // Adding primary cities to database
        $a = fopen(plugins_url('/', __FILE__) . 'cities.txt', 'r');
        $base = 'craigslist.org/';

        while ($line = trim(fgets($a))):

            if ($this->not_in_table($line)) {
                $replace = array("\r", "\n", "\r\n", " ");
                $city = str_replace($replace, "", $line);

                $city = strtolower(preg_replace('~\s~', '', $city));

                if (($pos = stripos($city, '-')) !== false) {
                    $city = substr($city, 0, $pos + 1);
                }
                $city_url = 'http://' . $city . '.' . $base;
                $wpdb->query("insert into $this->table (city_name, city_url) values('$line', '$city_url')");
            }
        endwhile;
    }

// end of create_table


    function not_inserted_before($id, $city) {
        global $wpdb;
        $in = $wpdb->get_var("select post_id from $this->table_data where cg_id=$id and city_id=$city");
        if ($in == null)
            return true;
    }

    /*     * *********

      Proxy functions
      Only usable when simple html dom is included
     * 
     * *************** */

    //Getting Proxy List

    function getIP($obj, $html) {


        $text = str_replace("div", "span", $obj->xmltext);
        $text = explode("span", $text);

        $ip = array();

        foreach ($text as $value) {
            $value = trim($value);
            $value = trim($value, "<");
            $value = trim($value, ">");
            $value = trim($value, ".");

            if (empty($value))
                continue;

            if (strpos($value, "display:none")) {
                continue;
            }

            if (strpos($value, ">")) {
                $value = "<" . $value . ">";
            }

            $value = strip_tags($value);

            $value = trim($value, ".");

            if (empty($value))
                continue;

            $ip [] = $value;
        }

        if (is_array($ip)) {
            return implode(".", $ip);
        }
    }

    function get_proxy_list() {

        include 'simple_html_dom.php';

        $html = file_get_html('http://www.hidemyass.com/proxy-list/');

        $proxy_array = array();
        $counter = 0;
        foreach ($html->find('tr') as $element) {
            if (++$counter == 1)
                continue;
            $ip = $element->find('td', 1);
            $port = trim($element->find('td', 2)->xmltext);
            $ip = $this->getIP($ip, $html);
            // var_dump($element->xmltext);
            if (preg_match('~\d~', $ip) && preg_match('~\d~', $port))
                $proxy_array[$ip] = $port;
        }

        return $proxy_array;
    }

// get content through proxy


    function get_content_through_proxy($url, $proxy_array) {

        $ip = array_rand($proxy_array);

        $port = $proxy_array[$ip];




        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:12.0) Gecko/20100101 Firefox/12.0');
        curl_setopt($ch, CURLOPT_PROXY, $ip);
        curl_setopt($ch, CURLOPT_PROXYPORT, $port);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//curl_setopt($ch, CURLOPT_HEADER, 1);

        $res = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array($res, $http_status);
    }

//get content directly
// get content through proxy


    function get_content_direct($url) {


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:12.0) Gecko/20100101 Firefox/12.0');
//    curl_setopt($ch, CURLOPT_PROXY, $ip);
//    curl_setopt($ch, CURLOPT_PROXYPORT, $port);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//curl_setopt($ch, CURLOPT_HEADER, 1);

        $res = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return array($res, $http_status);
    }

    function reform_title($title) {
        $title = strip_tags($title);
        // Preserve escaped octets.
        $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
        // Remove percent signs that are not part of an octet.
        $title = str_replace('%', '', $title);
        // Restore octets.
        $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);
        //$title = strtolower($title);
        $title = preg_replace('/&.+?;/', '', $title); // kill entities
        //$title = str_replace('.', '-', $title);
        $title = preg_replace('/[^%a-zA-Z0-9\'"; $%^&*()<>_\-+=`~\]\\\|.,@#!\?\[:]/', '', $title);
        //$title = preg_replace('/\s+/', '-', $title);
        //$title = preg_replace('|-+|', '-', $title);
        $title = trim($title, '-');
        $title = trim($title, '.');
        return trim($title);
    }

    function deactivation_tasks() {

        wp_clear_scheduled_hook('wp_rental_cron');
    }

}
