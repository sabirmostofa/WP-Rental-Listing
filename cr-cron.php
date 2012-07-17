<?php

set_time_limit(3600);

require_once '../../../wp-load.php';

//get some proxy lists

global $wpRentalImport, $wpdb;

$all_cities = $wpdb->get_results("select city_url,id from $wpRentalImport->table");




//starting the loop
foreach ($all_cities as $city):
    $url = $city->city_url . 'apa/';
    $res = $wpRentalImport->get_content_direct($url);
    if ($res[1] == 200) {

        $dom = new DOMDocument();
        @$dom->loadHTML($res[0]);

        foreach ($dom->getElementsByTagName('p') as $p) {
            if ($p->getAttribute('class') == 'row') {
                $p_url = $p->getElementsByTagName('a')->item(0)->getAttribute('href');
                preg_match('~\d+~', $p_url, $matches);
                $p_id = $matches[0];


                //if the content isn't inserted previously continue

                if ($wpRentalImport->not_inserted_before($p_id, $city->id)):
                    $l_dom = new DOMDocument();
                    $p_title = $wpRentalImport->reform_title($p->getElementsByTagName('a')->item(0)->textContent);
                    $pre_content = $wpRentalImport->get_content_direct($p_url);
                    
                    //if response received
                    if ($pre_content[1] == 200) {
                        @$l_dom->loadHTML($pre_content[0]);
                        $title = $wpRentalImport->reform_title($l_dom->getElementsByTagName('h2')->item(0)->textContent);                        
                        $body = $l_dom->saveXML($l_dom->getElementById('userbody'));                        
                        echo $title,'<br/>', $body;
                        break;
                    }

                endif;
            }
        }
    }
    break;

endforeach;

//var_dump($all_cities);






