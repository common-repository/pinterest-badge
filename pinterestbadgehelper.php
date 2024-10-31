<?php
/**
*	Collect data from G+
*	Copyright (C) 2011  Arun
*	http://www.skipser.com
*
*/
//date_default_timezone_set('Asia/Calcutta');
class pinterestBadge
{
	// The base g+ URL
	public $pinterest_url = 'http://api.pinterest.com/v3/pidgets/users/';

	// set a plausible user agent
	public $user_agent = 'Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20100101 Firefox/5.0';

	// how many hours to cache for
	public $cache_time = '14400'; // 4*60*60
	public $tmpl_time = '86400';  // 24*60*60

	// cache file name
	public $cache_file = '';

	//regexp file name
	public $regexp_file = '';

	// constructor
	function __construct($id = '', $debug='', $fullrefresh='')
	{
		if (!empty($id))
		{
			// build our pinterest url
			$this->regexp_filever = "1.2";

			$this->url = $this->pinterest_url . $id;
			$this->pinurl = $this->pinterest_url . $id .'/pins';
			$this->regexpurl = "http://www.skipser.com/test/pinterest-badge/regexp_".$this->regexp_filever.".txt";
			$this->user_id = $id;
			$this->debug = $debug;
			$this->fullrefresh=$fullrefresh;
		}
	}

	// main handler function, call it from your script
	public function pinterestBadge() {
		if($this->debug === 'true') {
			echo ">>>>".$_SERVER['REQUEST_URI']."<br/>";
			echo ">>>> Regexp Version ".$this->regexp_filever;
		}
		if($this->fullrefresh === 'true') {
			$this->refreshRegexp();
			$this->getBadgeTmpls();
		}
		$valid_cache=false;
		if( $this->isValidCache()) {
			if($this->debug === 'true') {
				echo ">>>>Got valid cache<br/>";
			}
			
			$valid_cache=true;
			$html=$this->getDataInCache();
			if((is_null($html['pin1']) || empty($html['pin1'])) && (is_null($html['pin2']) || empty($html['pin2']))) {
				$valid_cache=false;
			}
			if($this->debug === 'true') {
				echo ">>>>Got cache data - ".$html['name']." : ".$html['count']."<br/>";
			}
			
			$this->getBadgeTmpls();
			//In case the user has just updated the version, regexps or template might need an instant refresh.
			if($this->regexp_filever != $this->regexpver) {
				if($this->debug === 'true') {
					echo ">>>>Got invalid regexp version : ".htmlentities($this->regexp_filever).", ".htmlentities($this->regexpver)."<br/>";
				}
				
				$valid_cache = false;
			}
		}
		if($valid_cache == false) {
			if($this->debug === 'true') {
				echo ">>>>Got invalid cache<br/>";
			}
			
			$this->getBadgeTmpls();
			//In case the user has just updated the version, regexps or template might need an instant refresh.
			if($this->regexp_filever != $this->regexpver) {
				if($this->debug === 'true') {
					echo ">>>>Refreshing regexps as version is old.<br/>";
				}
				
				$this->refreshRegexp();
				$this->getBadgeTmpls();
			}

			//Just update the cache once so we don't have a lot of simultaneous cache updates.
			$html=$this->getDataInCache();
			if($this->debug === 'true') {
				echo ">>>>Corrupted cache data - ".$html['name']." : ".$html['count']."<br/>";
			}
			
			$this->updateCache($html);
			if($this->debug === 'true') {
				echo ">>>>Fixed cache data - ".$html['name']." : ".$html['count']."<br/>";
			}
			
			
			$html=$this->getDataForCache();
			if($this->debug === 'true') {
				echo ">>>>Got data - ".$html['name']." : ".$html['count']."<br/>";
			}
			
			if((is_null($html['pin1']) || empty($html['pin1'])) && (is_null($html['pin2']) || empty($html['pin2']))) {
				if($this->debug === 'true') {
					echo ">>>>Got corrupted cache after first try<br/>";
				}
				
				$this->refreshRegexp();
				$html=$this->parseHtml();
				if((is_null($html['pin1']) || empty($html['pin1'])) && (is_null($html['pin2']) || empty($html['pin2']))) {
					if($this->debug === 'true') {
						echo ">>>>Got corrupted cache after second try<br/>";
					}
					
					$html=$this->getDataInCache();
					$this->updateCache($html);
				}
				else {
					if($this->debug === 'true') {
						echo ">>>>Got good cache after second try - ".$html['name']." : ".$html['count']."<br/>";
					}
					
					$this->updateCache($html);
				}
			}
			else {
				if($this->debug === 'true') {
					echo ">>>>Got good cache after first try - ".$html['name']." : ".$html['count']."<br/>";
				}
				
				$this->updateCache($html);
			}
		}
		return $html;
	}

	//Checks if our cache is valid. Returns true or false.
	protected function isValidCache() {
		$file = $this->cache_file;
		$regexpfile = $this->regexp_file;
		$cache_time =  $this->cache_time;
		$tmpl_time = $this->tmpl_time;
		$got_init=false;

		if(! file_exists($file)) {
			$this->updateCache(array('id' => '', 'name' => '', 'count' => '', 'url' => '', 'pin1' => '', 'pinlink1' => '', 'pin2' => '', 'pinlink2' => '', 'pin3' => '', 'pinlink3' => '', 'pin4' => '', 'pinlink4' => '', 'pin5' => '', 'pinlink5' => '', 'pin6' => '', 'pinlnk6' => '', 'pin7' => '', 'pinlink7' => '', 'pin8' => '', 'pinlink8' => '', 'pin9' => '', 'pinlink9' => ''));
			$got_init=true;
		}

		if(! file_exists($regexpfile)) {
			$this->refreshRegexp();
		}
		else if (time() - $tmpl_time > filemtime($regexpfile)) {
			$this->refreshRegexp();
		}

		if($got_init == true) {
			return false;
		}

		// if we have a cache file and it's within our expiry time
		if (time() - $cache_time < filemtime($file)) {
			if($this->fullrefresh === 'true') {
				return false;
			}
			return true;
		}
		else {
			return false;
		}
	}

	//Nullifies content in cache.
	protected function nullifyCache() {
		$file = $this->cache_file;
		$this->updateCache(array('id' => '', 'name' => '', 'count' => '', 'url' => '', 'pin1' => '', 'pinlink1' => '', 'pin2' => '', 'pinlink2' => '', 'pin3' => '', 'pinlink3' => '', 'pin4' => '', 'pinlink4' => '', 'pin5' => '', 'pinlink5' => '', 'pin6' => '', 'pinlnk6' => '', 'pin7' => '', 'pinlink7' => '', 'pin8' => '', 'pinlink8' => '', 'pin9' => '', 'pinlink9' => ''));
	}

	//Reads cache and returns array.
	protected function getDataInCache() {
		$file = $this->cache_file;

		//open cached file
		$handle = fopen($file, "r");
		//read it
		$data = fgets($handle);
		//close it
		fclose($handle);

		// json decode, put into array and return
		return get_object_vars(json_decode($data));
	}

	//Updates cache with passed array.
	protected function updateCache($html) {
		$file = $this->cache_file;

		// json encode the data
		$json = json_encode($html);

		// open the file
		$handle = fopen($file, 'w');

		// write data to file
		fwrite($handle, $json);

		// close file
		fclose($handle);
	}

	//Use the current regexp file to retrieve data for cache from G+
	protected function getDataForCache() {
		$this->html = '';
		/*
		*	if safe mode or open_basedir is set, skip to using file_get_contents
		*	(fixes "CURLOPT_FOLLOWLOCATION cannot be activated" curl_setopt error)
		*/
		if(ini_get('safe_mode') || ini_get('open_basedir')) {
			// do nothing (will pass on to getPafeFile/get_file_contents as isset($curlHtml) will fail)
			if($this->debug === 'true') {
				echo ">>>>Curl not safe, check failed<br/>";
			}
			
		}
		else {
			// load the page
			if($this->debug === 'true') {
				echo ">>>>Using curl for URL - ".$this->url."<br/>";
			}
			
			$this->html = $this->getPageCurl($this->pinurl);

			// parse the returned html for the data we want
			$curlHtml = $this->parseHtml();
		}

		// see if curl managed to get data
		// if not, try with get_file_contents
		if (isset($curlHtml) && !empty($curlHtml['name']) && !empty($curlHtml['count'])) {
			return $curlHtml;
		}
		else {
			if($this->debug === 'true') {
				echo ">>>>Using getPageFile for URL - ".$this->url."<br/>";
			}
			
			// try loading with file_get_contents instead
			$this->html = $this->getPageFile($this->pinurl);

			// parse
			$data = $this->parseHtml();

			// return
			return $data;
		}
	}

	// use curl to load the page
	protected function getPageCurl($url) {
		// initiate curl with our url
		$this->curl = curl_init($url);

		// set curl options
		curl_setopt($this->curl, CURLOPT_HEADER, 0);
		curl_setopt($this->curl, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);

		// execute the call to pinterest
		$html = curl_exec($this->curl);

		curl_close($this->curl);

		if($this->debug === 'true') {
			echo ">>>>Got html file -<br/>";
			echo htmlentities($html);
			echo "<br/>";
		}

		return $html;
	}

	// use file_get_contents to load the page
	protected function getPageFile($url) {
		// empty the html property (although it's probably empty anyway if we're here)
		$html = '';

		// get the data
		$html = file_get_contents($url);
		if($this->debug === 'true') {
			echo ">>>>Got html file -<br/>";
			echo htmlentities($html);
			echo "<br/>";
		}

		return $html;
	}

	protected function parseHtml() {
		if($this->debug === 'true') {
			echo ">>>>Got html fileeee -<br/>";
			//echo htmlentities($this->html);
			echo "<br/>";
		}
		$results = json_decode($this->html, true);

		$name = $results['data']['user']['full_name'];
		if($this->debug === 'true') {
			echo ">>>>Got name -".$name."<br/>";
		}

		$followers = $results['data']['user']['follower_count'];
		if($this->debug === 'true') {
			echo ">>>>Got count -".$followers."<br/>";
		}

		$pindata = $results['data']['pins'];
		//$aa = ((Array)current(array_values( $pindata[0]['images'])))['url'];
		//$aa = $pindata[0]['images'][current(array_keys( $pindata[0]['images']))]['url'];
		//var_dump($aa);
		//echo ">>>>UUU ".(current(array_keys( $pindata[0]['images'])));
		//echo "<br/>";

		// put the data in an array
		$return = array('id' => $this->user_id, 'name' => $name, 'count' => $followers, 'url' => $this->url, 
			'pin1' => $pindata[0]['images'][current(array_keys( $pindata[0]['images']))]['url'], 'pinlink1' => 'http://www.pinterest.com/pin/'.$pindata[0]['id'], 
			'pin2' => $pindata[1]['images'][current(array_keys( $pindata[1]['images']))]['url'], 'pinlink2' => 'http://www.pinterest.com/pin/'.$pindata[1]['id'], 
			'pin3' => $pindata[2]['images'][current(array_keys( $pindata[2]['images']))]['url'], 'pinlink3' => 'http://www.pinterest.com/pin/'.$pindata[2]['id'], 
			'pin4' => $pindata[3]['images'][current(array_keys( $pindata[3]['images']))]['url'], 'pinlink4' => 'http://www.pinterest.com/pin/'.$pindata[3]['id'], 
			'pin5' => $pindata[4]['images'][current(array_keys( $pindata[4]['images']))]['url'], 'pinlink5' => 'http://www.pinterest.com/pin/'.$pindata[4]['id'], 
			'pin6' => $pindata[5]['images'][current(array_keys( $pindata[5]['images']))]['url'], 'pinlink6' => 'http://www.pinterest.com/pin/'.$pindata[5]['id'], 
			'pin7' => $pindata[6]['images'][current(array_keys( $pindata[6]['images']))]['url'], 'pinlink7' => 'http://www.pinterest.com/pin/'.$pindata[6]['id'], 
			'pin8' => $pindata[7]['images'][current(array_keys( $pindata[7]['images']))]['url'], 'pinlink8' => 'http://www.pinterest.com/pin/'.$pindata[7]['id'], 
			'pin9' => $pindata[8]['images'][current(array_keys( $pindata[8]['images']))]['url'], 'pinlink9' => 'http://www.pinterest.com/pin/'.$pindata[8]['id']);
		if($this->debug === 'true') {
			var_dump($return);
			echo "<br/>";
		}
		return $return;
	}

	//Refreshes content of regexp file from skipser.
	//This is to ensure any modifications are applied.
	protected function refreshRegexp() {
		$file = $this->regexp_file;
		$regexp = '';
		$gotvaliddata=false;
		if(ini_get('safe_mode') || ini_get('open_basedir')) {
			// do nothing (will pass on to getPafeFile/get_file_contents as isset($curlHtml) will fail)
		}
		else {
			// load the page
			$regexp=$this->getPageCurl($this->regexpurl);
		}

		preg_match('/Pinterest badge field regexps/s', $regexp, $matches);
		if (isset($matches) && !empty($matches)) {
			//Curl didn't manage to get the data. Try with get_file_contents.
		}
		else {
			// try loading with file_get_contents instead
			$regexp = $this->getPageFile($this->regexpurl);
		}

		// open the file
		$handle = fopen($file, 'w');
		if($this->debug === 'true') {
			if ($handle === false) {
				echo ">>>>Could not open regexp file ".$file." for writing<br/>";
			}
		}

		// write data to file
		fwrite($handle, $regexp);

		// close file
		fclose($handle);
	}

	protected function getBadgeTmpls() {
		$file = $this->regexp_file;
		$this->fullbadgetmpl='';

		//read it
		$handle = fopen($file, "r");
		if($this->debug === 'true') {
			if ($handle === false) {
				echo ">>>>Could not open regexp file ".$file." for reading<br/>";
			}
		}

		//Get the regexps for each data.
		$index = 0;
		while (($line = fgets($handle)) !== false) {
			if($index == 0) {
				$this->regexpver = preg_replace('/Pinterest badge field regexps - /', '', trim($line));
				if($this->debug === 'true') {
					echo ">>>>Got regexpver - ".htmlentities($this->regexpver)."<br/>";
				}
			}
			elseif($index == 1) {
				$this->fullbadgetmpl = trim($line);
				if($this->debug === 'true') {
					echo ">>>>Got fullbadgetmpl - ".htmlentities($this->fullbadgetmpl)."<br/>";
				}
			}
			$index++;
		}
		fclose($handle);

	}
	function int_divide($x, $y) {
	    if ($x == 0) return 0;
	    if ($y == 0) return FALSE;
	    $result = $x/$y;
	    $pos = strpos($result, '.');
	    if (!$pos) {
	        return $result;
	    } else {
	        return (int) substr($result, 0, $pos);
	    }
	}
}
?>