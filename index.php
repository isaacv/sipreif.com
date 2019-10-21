<?php
//die('dds');
require_once __DIR__.'/vendor/autoload.php'; 
//die("d");
use Carbon\Carbon;  
use Symfony\Component\HttpFoundation\JsonResponse as Response;
$app = new \Silex\Application(); 
//die("cisb");
// ... definitions
$app['debug'] = true;
error_reporting(E_ALL);
//print_r(__DIR__);

//echo phpinfo();

//die('dds');
$app->register(
    new BitolaCo\Silex\CapsuleServiceProvider(),
    array( 
         'capsule.connection' => array(
            'driver'   => 'sqlite',
     			  'database' => __DIR__.'/db/project1.db',
      		  'prefix'   => '',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'logging' => true, // Toggle query logging on this connection.
        )
    )
);

$app['capsule'];
//die("scdsb");
//Model
class Boscosas extends Illuminate\Database\Eloquent\Model 
{
    protected $table = "varBoscosas";
}

class SensorStatus extends Illuminate\Database\Eloquent\Model 
{

    protected $table = "sensor_status";
    public $timestamps = false;

}

//die('lol');

// Routes
// Diagrama de barras
$app->get('/boscosas-bar', function() use($app) { 

	$orderedByDay = Boscosas::select('unix', 'id', 'temperature', 'humidity')
	    ->get()
	    ->groupBy(function($date) {
	    return Carbon::parse(date('m/d/Y h:i:s', $date->unix))->format('m/d/y h'); 
  	});
  
	$h= [];
	$hA = [];
	$t = [];
	$tA = [];
	$divider = [];
	foreach ($orderedByDay as $key => $set) {
		$divider[] = $key;
		foreach ($set as $index => $data) {
			$h[ (int)$key][] =  (float)$data->humidity;
			$t[ (int)$key][] =  (float)$data->temperature;
		}
		$hA[] = array_sum($h[(int)$key])/count($h[(int)$key]);
		$tA[] = array_sum($t[(int)$key])/count($t[(int)$key]);
	}

	return  new Response(['divider'=> $divider,'temp' => $tA, 'humidity' => $hA], 201);
});


$app->get('/check-sensors', function() use($app) { 
	$interval = 15;
	$updateStatus = function ($sensorId, $st) {
		SensorStatus::where("sensor_id", $sensorId)->update(["status" => $st]);
		return $st;
	};
	
	$updatePanic = function ($sensorId, $panic) {
		SensorStatus::where("sensor_id", $sensorId)->update(["panic" => $panic]);
		return $panic;
	};

	$A = Boscosas::where("id", "111")->orderBy("unix", "desc")->first();
	$B = Boscosas::where("id", "211")->orderBy("unix", "desc")->first();
	$C = Boscosas::where("id", "311")->orderBy("unix", "desc")->first();
	$D = Boscosas::where("id", "411")->orderBy("unix", "desc")->first();

	$APanic = Boscosas::where("id", "111")->where('Panic', 1)->orderBy("unix", "desc")->first();
	$BPanic = Boscosas::where("id", "211")->where('Panic', 1)->orderBy("unix", "desc")->first();
	$CPanic = Boscosas::where("id", "311")->where('Panic', 1)->orderBy("unix", "desc")->first();
	$DPanic = Boscosas::where("id", "411")->where('Panic', 1)->orderBy("unix", "desc")->first();
    
	$getDiff = function ($unixtime) {
		$datetime1 = strtotime(Carbon::now()->format('m/d/y h:i'));
		$datetime2 = strtotime(Carbon::parse(date('m/d/Y h:i:s', $unixtime))->format('m/d/y h:i'));
		$interval  = abs($datetime2 - $datetime1);
		return round($interval / 60);
	}; 

	$a = $getDiff($A->unix) > $interval ? $updateStatus("111", 0) : $updateStatus("111", 1);
	$b = $getDiff($B->unix) > $interval ? $updateStatus("211", 0) : $updateStatus("211", 1);
	$c = $getDiff($C->unix) > $interval ? $updateStatus("311", 0) : $updateStatus("311", 1);
	$d = $getDiff($D->unix) > $interval ? $updateStatus("411", 0) : $updateStatus("411", 1);

	$getDiff($APanic->unix) < $interval ? $updatePanic("111", 1):$updatePanic("111", 0) ;
	$getDiff($BPanic->unix) < $interval ? $updatePanic("211", 1):$updatePanic("211", 0) ;
	$getDiff($CPanic->unix) < $interval ? $updatePanic("311", 1):$updatePanic("311", 0) ;
	$getDiff($DPanic->unix) < $interval ? $updatePanic("411", 1):$updatePanic("411", 0) ;	
   	
	return  new Response(["statuses" => [
		"A" => $a,
		"B" => $b,
		"C" => $c,
		"D" => $d,
		"unix" => strtotime(Carbon::now()->format('m/d/y h:i'))
	]], 201);
});

//die('lol3');

// Sensores para el mapa
$app->get('/sensors', function() use($app) { 

	$A = Boscosas::where("id", "111")->orderBy("unix", "desc")->first();
	$B = Boscosas::where("id", "211")->orderBy("unix", "desc")->first();
	$C = Boscosas::where("id", "311")->orderBy("unix", "desc")->first();
	$D = Boscosas::where("id", "411")->orderBy("unix", "desc")->first();
	$getStatus = function ($sensorId) {
		return SensorStatus::where("sensor_id", $sensorId)->first()->status;
	};

	$status = function($sensor){
		return $sensor->temperature >= 78? 1 : 0; 
	};

	$hum = function($sensor){
		return (float)$sensor->humidity; 
	};

	$temp = function($sensor){
		return (float)$sensor->temperature;
	};

	$smokeVar = function($sensor){
		return (float)$sensor->smokeFlag;
	};

	$tempFlag = function($sensor){
		return (float)$sensor->tempFlag;
	};

	$smokeFlag = function($sensor){
		return (float)$sensor->smokeFlag;
	};

	$panic = function($sensor){
		return (float)SensorStatus::where("sensor_id", $sensor)->first()->panic;
	};

	$date = function($sensor){
		return (Carbon::parse(date('m/d/Y h:i:s', $sensor->unix))->format('m/d/y h:i'));
	};

	$prob = function($sensor){
		//var prob = Temperature/85.0;
		return (float)$sensor->prob;
	};

	return new Response(
		[
			'sensorA'=> [ 
				'lat' => 19.175992, 
				'lng' => -71.0521372, 
				'status' => $status($A), 
				'temp' => $temp($A), 
				'hum' => $hum($A),
				'smokeVar' => $smokeVar($A),
				'tempFlag' => $tempFlag($A),
				'smokeFlag' => $smokeFlag($A),
				"sensorOn" => $getStatus("111"),
				'prob' => $prob($A),
				"date" => $date($A),
				"prob" => number_format(($temp($A)/145) * 100, 2),
				"panic" => $panic("111")
			], 
			'sensorB'=> [ 
				'lat' => 19.175892, 
				'lng' => -71.0521972, 
				'status' => $status($B), 
				'temp' => $temp($B),
				'smokeVar' => $smokeVar($B),
				'tempFlag' => $tempFlag($B),
				'smokeFlag' => $smokeFlag($B),
				"sensorOn" => $getStatus("211"), 
				'hum' => $hum($B),
				"date" => $date($B),
				"prob" => number_format(($temp($B)/145) * 100, 2),
				"panic" => $panic("211")
			], 
			'sensorC'=> [ 
				'lat' => 19.175892, 
				'lng' => -71.0520872, 
				'status' => $status($C), 
				'temp' => $temp($C),
				'smokeVar' => $smokeVar($C),
				'tempFlag' => $tempFlag($C),
				'smokeFlag' => $smokeFlag($C),
				"sensorOn" => $getStatus("311"),
				'hum' => $hum($C),
				"date" => $date($C),
				"prob" => number_format(($temp($C)/145) * 100, 2),
				"panic" => $panic("311")
			],
			'sensorC'=> [ 
				'lat' => 19.175892, 
				'lng' => -71.0520872, 
				'status' => $status($D), 
				'temp' => $temp($D),
				'smokeVar' => $smokeVar($D),
				'tempFlag' => $tempFlag($D),
				'smokeFlag' => $smokeFlag($D),
				"sensorOn" => $getStatus("411"),
				'hum' => $hum($D),
				"date" => $date($D),
				"prob" => number_format(($temp($D)/145) * 100, 2),
				"panic" => $panic("411")
			], 
			"prob" => number_format((($temp($A) + $temp($B) + $temp($C) + $temp($D) )/4), 2)
		], 
	201);
});

//die('lol4');

// Lectura Individual de Sensores
$app->get('/individual-sensors', function() use($app) { 
	$A = Boscosas::where("id", "111")->orderBy("unix", "desc")->first();
	$B = Boscosas::where("id", "211")->orderBy("unix", "desc")->first();
	$C = Boscosas::where("id", "311")->orderBy("unix", "desc")->first();
	$D = Boscosas::where("id", "411")->orderBy("unix", "desc")->first();
	
	$hum = function($sensor){
		return (float)$sensor->humidity; 
	};

	$temp = function($sensor){
		return (float)$sensor->temperature;
	};

	$smokeVar = function($sensor){
		return $sensor->smokeVar;
	}

	$tempFlag = function($sensor){
		return $sensor->tempFlag;
	}

	$smokeFlag = function($sensor){
		return $sensor->smokeFlag;
	}

	$date = function($sensor){
		return (Carbon::parse(date('m/d/Y h:i:s', $sensor->unix))->format('m/d/y h:i:s'));
	};

	return new Response(
		[
			'sensorA'=> [
				'temp' => $temp($A), 
				'hum' => $hum($A),
				'smokeVar' => $smokeVar($A),
				'tempFlag' => $tempFlag($A),
				'smokeFlag' => $smokeFlag($A),
				"date" => $date($A)
			], 
			'sensorB'=> [
				'temp' => $temp($B),
				'hum' => $hum($B),
				'smokeVar' => $smokeVar($B),
				'tempFlag' => $tempFlag($B),
				'smokeFlag' => $smokeFlag($B),
				"date" => $date($B)
			], 
			'sensorC'=> [
				'temp' => $temp($C),
				'hum' => $hum($C),
				'smokeVar' => $smokeVar($C),
				'tempFlag' => $tempFlag($C),
				'smokeFlag' => $smokeFlag($C),
				"date" => $date($C)
			], 
			'sensorD'=> [
				'temp' => $temp($D),
				'hum' => $hum($D),
				'smokeVar' => $smokeVar($D),
				'tempFlag' => $tempFlag($D),
				'smokeFlag' => $smokeFlag($D),
				"date" => $date($D)
			]
		], 
	201);
});

// Lectura Individual de Sensores
$app->get('/linechartjs', function() use($app) { 

	$A = Boscosas::select('unix','temperature','humidity','smokeFlag')->take(10)->where("id", "111")->orderBy("unix", "desc")->get();
	$B = Boscosas::select('unix','temperature','humidity','smokeFlag')->take(10)->where("id", "211")->orderBy("unix", "desc")->get();
	$C = Boscosas::select('unix','temperature','humidity','smokeFlag')->take(10)->where("id", "311")->orderBy("unix", "desc")->get();
	$D = Boscosas::select('unix','temperature','humidity','smokeFlag')->take(10)->where("id", "411")->orderBy("unix", "desc")->get();

	$tempA = [];
	$humA = [];
	$smokeA = [];
	$dateA = [];
	
	$tempB = [];
	$humB = [];
	$smokeB = [];

	$tempC = [];
	$humC = [];
	$smokeC = [];

	$tempD = [];
	$humD = [];
	$smokeD = [];	

	foreach ($A as $key => $value){
		$tempA[] = [$value->Temperature];
		$humA[] = [$value->Humidity];
		$smokeA[] = [$value->smokeFlag];
		$dateA[] = [Carbon::parse(date('m/d/Y h:i:s', $value->unix))->format('m-d-y h:i:s')];
		
	}

	foreach ($B as $key => $value){
		$tempB[] = [$value->Temperature];
		$humB[] = [$value->Humidity];
		$smokeB[] = [$value->smokeFlag];
	}

	foreach ($C as $key => $value){
		$tempC[] = [$value->Temperature];
		$humC[] = [$value->Humidity];
		$smokeC[] = [$value->smokeFlag];
	}

	foreach ($D as $key => $value){
		$tempD] = [$value->Temperature];
		$humD[] = [$value->Humidity];
		$smokeD[] = [$value->smokeFlag];
	}

	sort($dateA);

	return new Response(
		[
			'sensorA'=> [ 
				'lat' => 19.175992, 
				'lng' => -71.0521372, 
				'temp' => $tempA,
				'hum' => $humA,
				'smoke' => $smokeA,
				'date' => $dateA
			], 
			'sensorB'=> [ 
				'lat' => 19.175892, 
				'lng' => -71.0521972, 
				'temp' => $tempB,
				'hum' => $humB,
				'smoke' => $smokeB,
			],
			'sensorC'=> [ 
				'lat' => 19.175892, 
				'lng' => -71.0520872,
				'temp' => $tempC,
				'hum' => $humC,
				'smoke' => $smokeC,
			],
			'sensorD'=> [ 
				'lat' => 19.175892, 
				'lng' => -71.0520872,
				'temp' => $tempD,
				'hum' => $humD,
				'smoke' => $smokeD,
			]
		], 
	201);
});


// Diagrama de lineas
$app->get('/boscosas-line', function() use($app) { 

	$orderedByDay = Boscosas::select('unix', 'id', 'temperature', 'humidity')
	    ->take(20)
	    ->get()
	    ->groupBy(function($date) {
	    return Carbon::parse(date('m/d/Y h:i:s', $date->unix))->format('d'); 
  	});

	$dateL = [];
	$tempL = [];
	$humL = [];
	foreach ($orderedByDay as $key => $value) {
	 	foreach ($value as $key2 => $val) {
	 	  $dateL[] = [Carbon::parse(date('m/d/Y h:i:s', $val->unix))->format('m-d-y h:i')];
	 	  $tempL[] = [$val->Temperature];
	 	  $humL[] = [$val->Humidity];	
	 	}
	 	
	}

  	return new Response(['date'=> $dateL,'temp' => $tempL,'humidity' => $humL], 201);
});


// Diagrama de lineas2
$app->get('/boscosas-line2', function() use($app) { 

	$orderedByDay = Boscosas::select('unix', 'id', 'temperature', 'humidity')
	    ->take(20)
	    ->get()
	    ->groupBy(function($date) {
	    return Carbon::parse(date('m/d/Y h:i:s', $date->unix))->format('d'); 
  	});

	$dateL = [];
	$tempL = [];
	$humL = [];
	foreach ($orderedByID as $key => $value) {
	 	foreach ($value as $key2 => $val) {
	 	  
	 	  $dateL[] = [Carbon::parse(date('m/d/Y h:i:s', $val->unix))->format('m-d-y h:i')];
	 	  $tempL[] = [$val->Temperature];
	 	  $humL[] = [$val->Humidity];	
	 	}
	 	
	}

  	return new Response(['date'=> $dateL,'temp' => $tempL,'humidity' => $humL], 201);
});

// Ruta tabla
$app->get('/boscosas-tabla', function() use($app) { 

	$orderedByDay = Boscosas::select('unix', 'id', 'temperature', 'humidity', 'smokeFlag', 'Latitude', 'Longitude')
	    ->get()
	    ->groupBy(function($date) {

	    return Carbon::parse(DateTime::createFromFormat('d/m/y h:i:s', trim($date->unix)))->format('d/m/y h:i:s'); 
  	});
   
	 $data = [];
	 foreach ($orderedByDay as $key => $value) {
	 	foreach ($value as $key2 => $val) {
	 	  $data[] = [$val->id,$val->temperature, $val->humidity, $val->smokeFlag, $val->unix, "N/A", "OK"];	
	 	}
	 	
	 }

  	return  new Response(['data'=> $data], 201);
});



$app->get('/all', function() use($app) { 
		$all  = Boscosas::all();
		return  new Response($all, 201);
});



// Boscosas Insert
$app->get('/boscosas-insert', function() use($app) { 
	
	$id= $_GET['id'];
	$temp= $_GET['temp'];
	$hum= $_GET['hum'];
	$smokeVar= $_GET['smokeVar'];
	$tempFlag= $_GET['tempFlag'];
	$smokeFlag= $_GET['smokeFlag'];
	
	Boscosas::insert([
		'unix'=> time(),
		'ID' => $id,
		'Temperature' => $temp,
		'Humidity' => $hum,
		'smokeVar' => $smokeVar,
		'tempFlag' => $tempFlag,
		'smokeFlag' => $smokeFlag,
		'Latitude' => '19.175892',
		'Longitude' => '-71.0520872'
	]);
	
	return new Response([$id, $temp, $hum, $smokeVar, $tempFlag, $smokeFlag]);
});


$app->run();
