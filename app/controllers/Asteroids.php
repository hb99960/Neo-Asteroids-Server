<?php
class Asteroids extends Controller
{ 
    public function index($a = '', $b = '', $c = '') : void
    {
        echo "\nAsteroid Controller";
    }

    public function getAsteroidData() : void
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: POST");
        header("Access-Control-Allow-Headers: Content-Type");

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            $startDate = $data['start_date'];
            $endDate = $data['end_date'];
            $apiKey = "iebFlxqX4JSXz0eYGS9LSO7R4Hmph7ZQverwmljr";

        if( $startDate && $endDate && $apiKey){
            $response = $this->fetchAsteroidData($startDate, $endDate, $apiKey);
            $requiredResponse = $this->getCustomData2($response);
            print_r(json_encode($requiredResponse));
        }
        else {
            echo json_encode(['error' => 'Missing required parameters']);
        }
    }

    public function getCustomData2($response) : array
    {
        $totalCount = $response['element_count'];
        $datesArray = [];
        $totalAsteroids = 0;
        $totalDiameter = 0;
        $totalDiameterCount = 0;
        $maxVelocity = 0;
        $maxVelocityAsteroid = null;
        $minDistance = PHP_FLOAT_MAX;
        $minDistanceAsteroid = null;
    
        foreach ($response['near_earth_objects'] as $date => $dateObjects) {
            $datesArray[] = ['date' => $date, 'num_asteroids' => count($dateObjects)];
            $totalAsteroids += count($dateObjects);
    
            foreach ($dateObjects as $object) {
                if (isset($object['estimated_diameter']['kilometers'])) {
                    $min = $object['estimated_diameter']['kilometers']['estimated_diameter_min'];
                    $max = $object['estimated_diameter']['kilometers']['estimated_diameter_max'];
                    $averageDiameter = ($min + $max) / 2;
                    $totalDiameter += $averageDiameter;
                    $totalDiameterCount++;
                } 
                if (isset($object['close_approach_data'])) {
                    foreach ($object['close_approach_data'] as $approach) {
                        if (isset($approach['relative_velocity']['kilometers_per_second'])) {
                            $velocity = $approach['relative_velocity']['kilometers_per_second'];
                            
                            if ($velocity > $maxVelocity) {
                                $maxVelocity = $velocity;
                                $maxVelocityAsteroid = $object['name'];
                            }
                        }
    
                        if (isset($approach['miss_distance']['kilometers'])) {
                            $distance = $approach['miss_distance']['kilometers'];
    
                            if ($distance < $minDistance) {
                                $minDistance = $distance;
                                $minDistanceAsteroid = $object['name'];
                            }
                        }
                    }
                }
            }
        }
       
        $overallAverageDiameter = $totalDiameterCount > 0 ? $totalDiameter / $totalDiameterCount : 0;
    
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

    public function fetchAsteroidData($startDate, $endDate, $apiKey) : array
    {
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