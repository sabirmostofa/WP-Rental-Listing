<?php

set_time_limit(3600);

require_once '../../../wp-load.php';

//get some proxy lists

global $wpRentalImport, $wpdb;

$all_cities = $wpdb->get_results("select city_url,id from $wpRentalImport->table");
extract(get_option('rental-settings-var') ? get_option('rental-settings-var') : array());
echo $max_post = isset($max_post) ? $max_post : 10;





//starting the loop
foreach ($all_cities as $city):
    $insert_count = 0;
    $url = $city->city_url . 'apa/';
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
                            echo "insert into $wpRentalImport->table_data (city_id,post_id,cg_id) values($city->id,$res,$cg_id)",'<br/>';

                            $h = $wpdb->query("insert into $wpRentalImport->table_data (city_id,post_id,cg_id) values($city->id,$res,$cg_id)");
                            var_dump($h);
                            if (++$insert_count >= $max_post)
                                break;
                        }
                    }

                endif;
            }
        }
    }
    break;

endforeach;

//var_dump($all_cities);






