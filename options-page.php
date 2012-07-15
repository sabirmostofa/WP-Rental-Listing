<?php
global $wpdb;



//if setting submited
if (isset($_POST['main-submit'])):
    $_POST = array_map(create_function('$a', 'return trim($a);'), $_POST);
    extract($_POST);
    update_option('rental-settings-var', array(
        'max_post' => $max_post_per_day,
    ));

endif;



// If new city submitted
if (isset($_POST['city-submit'])):
    $_POST = array_map(create_function('$a', 'return trim($a);'), $_POST);
    extract($_POST);
    if (strlen($city_name) !== 0 && strlen($city_url) !== 0)        
        if( $this -> not_in_table($city_name) )
        $res = $wpdb->query("insert into $this->table (city_name, city_url) values('$city_name', '$city_url')");
   

endif;

if (get_option('rental-settings-var'))
    extract(get_option('rental-settings-var'));
$max_post = (isset($max_post)) ? $max_post : 10;



?>

<div class="wrap">
    <form action ='' method='post'>
        <h4>General Settings</h4>
        Maximum renal listing  number to Import per city per day(Default: 10)
        <br/>
        <input style="width:40%" type='text' name='max_post_per_day' value="<?php echo $max_post ?>"/>
        <br/> 

        <input class='button-primary' type='submit' name="main-submit" value='Submit'/> 
    </form>

    <!-- Form to add a new city and URL -->

    <br/>
    <br/>
    <h4>Add a new city</h4> 
    <form action="" method ="post">
        City Name:
        <input style="width:40%" type='text' name='city_name' value=""/>
        Craigslist Url:
        <input style="width:40%" type='text' name='city_url' value=""/>
        <input class='button-primary' type='submit' name="city-submit" value='Add city'/> 

    </form>

    <br/>
    <br/>

    <?php
    $all_cities = $wpdb->get_results(
            "SELECT id, city_name, city_url 
	FROM $this->table	
	"
    );
//var_dump($all_cities);
    ?>
    <table class="widefat" >
        <thead>
            <tr>

                <th> Remove</th>
                <th> City Name</th>
                <th>City URL </th>
            </tr>
        </thead>
        <tbody>

            <?php
            $drop_image = $this->image_dir . 'b_drop.png';
            foreach ($all_cities as $city):
                echo "<tr><td><a href='#'> <img src='$drop_image' class='$city->id'/><a></td> <td> $city->city_name</td><td><a href='$city->city_url'>$city->city_url</a></td></tr>";
                ?>



                <?php
            endforeach;
                  ?>
        </tbody>

</table>


</div>

<div style="clear:both;width:200px;heigth:20px"></div>
