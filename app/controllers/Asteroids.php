<?php
class Asteroids extends Controller{
    
    public function index($a = '', $b = '', $c = ''){
        echo "\nAsteroid Controller";
        // $this->view('home');
    }

    public function getAsteroidData(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Headers: Content-Type");

        // echo "inside get Asteroid Data";

         // Get the raw POST data (for JSON)
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

        // if (isset($_POST['start_date'], $_POST['end_date'], $_POST['api_key'])) {
            // Retrieve the start and end dates from the POST request
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $apiKey = "iebFlxqX4JSXz0eYGS9LSO7R4Hmph7ZQverwmljr";

            // echo "\nStart Date: $startDate, End Date: $endDate, API Key: $apiKey";
        //        

        if( $startDate && $endDate && $apiKey){

            $response = $this->fetchAsteroidData($startDate, $endDate, $apiKey);
            // print_r($response);
            $requiredResponse = $this->getCustomData2($response);
            print_r(json_encode($requiredResponse));
        }
        else{
            echo json_encode(['error' => 'Missing required parameters']);
        }
    }

    public function getCustomData2($response){
        // echo "\nInside getCustom Data\n";
    
        $totalCount = $response['element_count'];
        // echo "Total Asteroids: " . $totalCount . "\n";
    
        $datesArray = [];
        $totalAsteroids = 0;
        $totalDiameter = 0;
        $totalDiameterCount = 0;
    
        $maxVelocity = 0;
        $maxVelocityAsteroid = null;
    
        $minDistance = PHP_FLOAT_MAX;
        $minDistanceAsteroid = null;
    
        foreach ($response['near_earth_objects'] as $date => $dateObjects) {
            // Add the date and number of objects to the dates array
            $datesArray[] = ['date' => $date, 'num_asteroids' => count($dateObjects)];
            // $datesArray[] = [$date => $dateObjects];
            // Increment the total number of asteroids
            $totalAsteroids += count($dateObjects);
    
            foreach ($dateObjects as $object) {
                // Calculate average diameter
                if (isset($object['estimated_diameter']['kilometers'])) {
                    $min = $object['estimated_diameter']['kilometers']['estimated_diameter_min'];
                    $max = $object['estimated_diameter']['kilometers']['estimated_diameter_max'];
                    $averageDiameter = ($min + $max) / 2;
    
                    // Update total diameter and count
                    $totalDiameter += $averageDiameter;
                    $totalDiameterCount++;
                }
    
                // Iterate over close_approach_data to find max velocity and closest distance
                if (isset($object['close_approach_data'])) {
                    foreach ($object['close_approach_data'] as $approach) {
                        if (isset($approach['relative_velocity']['kilometers_per_second'])) {
                            $velocity = $approach['relative_velocity']['kilometers_per_second'];
    
                            // Check for maximum velocity
                            if ($velocity > $maxVelocity) {
                                $maxVelocity = $velocity;
                                $maxVelocityAsteroid = $object['name'];
                            }
                        }
    
                        if (isset($approach['miss_distance']['kilometers'])) {
                            $distance = $approach['miss_distance']['kilometers'];
    
                            // Check for closest distance
                            if ($distance < $minDistance) {
                                $minDistance = $distance;
                                $minDistanceAsteroid = $object['name'];
                            }
                        }
                    }
                }
            }
        }
    
        // Calculate overall average diameter
        $overallAverageDiameter = $totalDiameterCount > 0 ? $totalDiameter / $totalDiameterCount : 0;
    
        // Print and return results
        // echo "Dates and number of asteroids:\n";
        // print_r($datesArray);
    
        // echo "Total asteroids in date range: " . $totalAsteroids . "\n";
        // echo "Average size of asteroids: " . $overallAverageDiameter . " km\n";
        // echo "Asteroid with maximum velocity: " . $maxVelocityAsteroid . " (" . $maxVelocity . " km/s)\n";
        // echo "Asteroid closest to Earth: " . $minDistanceAsteroid . " (" . $minDistance . " km)\n";
    
        // Return result as an array for further use
        return [
            'dates' => $datesArray,
            'total_asteroids' => $totalAsteroids,
            'average_diameter' => $overallAverageDiameter,
            'max_velocity_asteroid' => [
                'name' => $maxVelocityAsteroid,
                'velocity' => $maxVelocity
            ],
            'closest_asteroid' => [
                'name' => $minDistanceAsteroid,
                'distance' => $minDistance
            ]
        ];
    }
    

    public function getCustomData($response){
        echo "\nInside getCustom Data\n";
        $totalCount = $response['element_count'];
        echo $totalCount;

        foreach ($response['near_earth_objects'] as $date => $dateObjects) {
            // Add the date to the dates array
            $datesArray[] = $date;

            foreach ($dateObjects as $object) {
                if (isset($object['estimated_diameter']['kilometers'])) {
                    $min = $object['estimated_diameter']['kilometers']['estimated_diameter_min'];
                    $max = $object['estimated_diameter']['kilometers']['estimated_diameter_max'];
                    
                    // Calculate and print average diameter
                    $averageDiameter = ($min + $max) / 2;
                    echo "Average diameter for object: " . $averageDiameter . "\n";
                }

                // Iterate over close_approach_data as it is an array
                if (isset($object['close_approach_data'])) {
                    foreach ($object['close_approach_data'] as $approach) {
                        if (isset($approach['relative_velocity'])) {
                            $velocity = $approach['relative_velocity']['kilometers_per_second'];
                            echo "Velocity for object: " . $velocity . " km/s\n";
                        }
                        if (isset($approach['miss_distance'])) {
                            $distance = $approach['miss_distance']['kilometers'];
                            echo "Distance from earth: " . $distance . " km/s\n";
                        }
                    }
                }
                
            }
        }
        print_r($datesArray);
        print_r(count($datesArray));


    }

    public function fetchAsteroidData($startDate, $endDate, $apiKey){

        $url = "https://api.nasa.gov/neo/rest/v1/feed?start_date=$startDate&end_date=$endDate&api_key=$apiKey";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        $output = curl_exec($curl);
        curl_close($curl);
        return json_decode($output, true);
    }

}