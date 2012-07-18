<?php

set_time_limit(600);



//loading files
if(!function_exists('add_action'))
require_once '../../../wp-load.php';


if(!function_exists('wp_create_category'))    
require_once '../../../wp-admin/includes/taxonomy.php';



//echo time();
//var_dump(wp_get_schedule('wp_rental_cron'));

//

global $wpRentalImport, $wpdb;

$all_cities = $wpdb->get_results("select city_url,id,city_name from $wpRentalImport->table");
extract(get_option('rental-settings-var') ? get_option('rental-settings-var') : array());
$max_post = isset($max_post) ? $max_post : 10;





//starting the loop
foreach ($all_cities as $city):
    $insert_count = 0;
    $url = stripos($city->city_url, 'newyork')? $city->city_url . 'aap/' : $city->city_url . 'apa/';
    $res = $wpRentalImport->get_content_direct($url);
    if ($res[1] == 200) {

        $dom = new DOMDocument();
        @$dom->loadHTML($res[0]);

        foreach ($dom->getElementsByTagName('p') as $p) {
            if ($p->getAttribute('class') == 'row') {
                $p_url = $p->getElementsByTagName('a')->item(0)->getAttribute('href');
                preg_match('~\d+~', $p_url, $matches);
                $cg_id = $matches[0];


                //if the content isn't inserted previously continue

                if ($wpRentalImport->not_inserted_before($cg_id, $city->id)):

                    $l_dom = new DOMDocument();
                    $p_title = $wpRentalImport->reform_title($p->getElementsByTagName('a')->item(0)->textContent);
                    $pre_content = $wpRentalImport->get_content_direct($p_url);

                    //if response received
                    if ($pre_content[1] == 200) {
                        @$l_dom->loadHTML($pre_content[0]);
                        $title = $wpRentalImport->reform_title($l_dom->getElementsByTagName('h2')->item(0)->textContent);
                        $body = $l_dom->saveXML($l_dom->getElementById('userbody'));



                        $city_slug = sanitize_title_with_dashes($city->city_name);
                        if (get_category_by_slug($city_slug) == false)
                            $cat = wp_create_category($city->city_name);
                        $cat_ob = get_category_by_slug($city_slug);
                        $cat_id = $cat_ob->cat_ID;
                        //exit;
                        $my_post = array(
                            'post_title' => $title,
                            'post_content' => $body,
                            'post_type' => 'rentallisting',
                            'post_status' => 'publish',
                            'post_author' => 1
                        );

                        // Insert the post into the database
                        $res = wp_insert_post($my_post);
                        if ($res) {

                            //echo "insert into $wpRentalImport->table_data (city_id,post_id,cg_id) values($city->id,$res,$cg_id)",'<br/>';

                            wp_set_post_categories($res, array($cat_id));



                            $h = $wpdb->query("insert into $wpRentalImport->table_data (city_id,post_id,cg_id) values($city->id,$res,$cg_id)");
                            //var_dump($h);
                            if (++$insert_count >= $max_post)
                                break;
                        }
                    }

                endif;
            }
        }
    }
    //break;

endforeach;

//var_dump($all_cities);






