<?php

set_time_limit(1700);

require_once '../../../wp-load.php';
$url = 'http://www.warriorforum.com/warrior-special-offers-forum/';
$content = file_get_contents($url);
$doc = new DOMDocument();
@$doc->loadHTML($content);

/*
$list = get_option('wso-imported-ids');
array_pop($list);
array_pop($list);
array_pop($list);

update_option('wso-imported-ids', $list);
*/
foreach ($doc->getElementsByTagName('a') as $single):
    $id = $single->getAttribute('id');
    if (stripos($id, 'thread_title') !== false):
        if (preg_match('/\d+/', $id, $matches))
            $id_int = $matches[0];
        $list = get_option('wso-imported-ids');
        if (!is_array($list))
            $list = array();
        if (!in_array($id_int, array(66, 313426, 142687, 122868, 25379, 4740)) && !in_array($id_int, $list)):
            $title = html_entity_decode($single->nodeValue);
            $post_content = '<p class="wso-extra">Above you will see the place where you can actually grade this particular WSO. If you want to see the WSO itself and validate that this is the correct WSO to grade, you can click the direct link to the WSO below. Grades are based on a typical A through F scale, so give this WSO whatever you think it deserves! Also, if you have minute, drop us a comment below about the WSO so other potential buyers can see if it is worth purchasing.</p>';
           
            $post_image_target = $single->getAttribute('href');
            $post_image_src = "http://images.shrinktheweb.com/xino.php?stwembed=1&stwaccesskeyid=9690a743e365c89&stwsize=xlg&stwinside=1&stwurl=$post_image_target";
                   
            $image_div = <<<TY
         <div class="wsos-post-scr" style="margin:10px auto">
         <a target="_blank"  href="$post_image_target"><img src="$post_image_src"/></a>
         </div>
TY;
            $post_content .= $image_div;
            $text_link = '<b>Link:</b><a target="_blank" href="' . $single->getAttribute('href') . '">' . $single->getAttribute('href') . '</a>';
            $post_content .= $text_link;
            
            $extra_text = '<p class="wso-ins2">Are you a WSO seller who wants to help potential purchasers to know they are buying a quality product that you stand behind? If so, grab the “report card” code below and add it to your WSO thread in the same way that you would grab an image! This will be a great way for you to let Warrior know you are proud of your product!</p>';
            
            $post_content.= $extra_text;
            $new_post = array();
            $new_post['post_title'] = $title;
            $new_post['post_author'] = 69;
            $new_post['post_content'] = $post_content;
            $new_post['post_status'] = 'publish';
            $new_post['post_category'] = array(1);
            $new_post_id = wp_insert_post($new_post);
            if ($new_post_id) {
                $list[] = $id_int;
                update_option('wso-imported-ids', $list);
            }

        endif;

    endif;
endforeach;
