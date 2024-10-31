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
	public $pinterest_url = 'http://pinterest.com/';

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
	function __construct($id = '')
	{
		if (!empty($id))
		{
			// build our pinterest url
			$this->regexp_filever = "1.0";

			$this->url = $this->pinterest_url . $id;
			$this->pinurl = $this->pinterest_url . $id .'/pins';
			$this->regexpurl = "http://www.skipser.com/test/pinterest-badge/regexp_".$this->regexp_filever.".txt";
			$this->user_id = $id;
		}
	}

	// main handler function, call it from your script
	public function pinterestBadge() {
		$valid_cache=false;
		if( $this->isValidCache()) {
			echo ">>>>Got valid cache<br/>";
			$valid_cache=true;
			$html=$this->getDataInCache();
			if((is_null($html['name']) || empty($html['name'])) && (is_null($html['count']) || empty($html['count'])) && (is_null($html['url']) || empty($html['url']))) {
				$valid_cache=false;
			}
			echo ">>>>Got cache data - ".$html['name']." : ".$html['count']."<br/>";
			$this->getBadgeTmpls();
			//In case the user has just updated the version, regexps or template might need an instant refresh.
			if($this->regexp_filever != $this->regexpver) {
				echo ">>>>Got invalid regexp version : ".htmlentities($this->regexp_filever).", ".htmlentities($this->regexpver)."<br/>";
				$valid_cache = false;
			}
		}
		if($valid_cache == false) {
			echo ">>>>Got invalid cache<br/>";
			$this->getBadgeTmpls();
			//In case the user has just updated the version, regexps or template might need an instant refresh.
			if($this->regexp_filever != $this->regexpver) {
				echo ">>>>Refreshing regexps as version is old.<br/>";
				$this->refreshRegexp();
				$this->getBadgeTmpls();
			}

			//Just update the cache once so we don't have a lot of simultaneous cache updates.
			$html=$this->getDataInCache();
			echo ">>>>Corrupted cache data - ".$html['name']." : ".$html['count']."<br/>";
			$this->updateCache($html);
			echo ">>>>Fixed cache data - ".$html['name']." : ".$html['count']."<br/>";
			
			$html=$this->getDataForCache();
			echo ">>>>Got data - ".$html['name']." : ".$html['count']."<br/>";
			if((is_null($html['name']) || empty($html['name'])) && (is_null($html['count']) || empty($html['count'])) && (is_null($html['url']) || empty($html['url']))) {
				echo ">>>>Got corrupted cache after first try<br/>";
				$this->refreshRegexp();
				$html=$this->parseHtml();
				if((is_null($html['name']) || empty($html['name'])) && (is_null($html['count']) || empty($html['count'])) && (is_null($html['url']) || empty($html['url']))) {
					echo ">>>>Got corrupted cache after second try<br/>";
					$html=$this->getDataInCache();
					$this->updateCache($html);
				}
				else {
					echo ">>>>Got good cache after second try - ".$html['name']." : ".$html['count']."<br/>";
					$this->updateCache($html);
				}
			}
			else {
				echo ">>>>Got good cache after first try - ".$html['name']." : ".$html['count']."<br/>";
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
			echo ">>>>Curl not safe, check failed<br/>";
		}
		else {
			// load the page
			echo ">>>>Using curl for URL - ".$this->url."<br/>";
			$this->html = $this->getPageCurl($this->pinurl);

			// parse the returned html for the data we want
			$curlHtml = $this->parseHtml();
			print_r ($curlHtml);
			echo "<br/>";
		}

		// see if curl managed to get data
		// if not, try with get_file_contents
		if (isset($curlHtml) && !empty($curlHtml['name']) && !empty($curlHtml['count'])) {
			return $curlHtml;
		}
		else {
			echo ">>>>Using getPageFile for URL - ".$this->url."<br/>";
			// try loading with file_get_contents instead
			$this->html = $this->getPageFile($this->pinurl);

			// parse
			$data = $this->parseHtml();
			print_r ($data);
			echo "<br/>";

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
		
		echo ">>>>Got html file -<br/>";
		echo htmlentities($html);
		echo "<br/>";

		return $html;
	}

	// use file_get_contents to load the page
	protected function getPageFile($url) {
		// empty the html property (although it's probably empty anyway if we're here)
		$html = '';

		// get the data
		$html = file_get_contents($url);
		echo ">>>>Got html file -<br/>";
		echo htmlentities($html);
		echo "<br/>";

		return $html;
	}

	// parses through the returned html
	protected function parseHtml() {

		// parse the html for the user's name
		preg_match($this->nameregexp, $this->html, $matches);
		if (isset($matches) && !empty($matches)) {
			$name = $matches[$this->nameregexp_group];
		}
		else {
			$name = '';
		}

		// parse the html to look for the h4 'have X in circles' element
		preg_match($this->followersregexp, $this->html, $matches);
		if (isset($matches) && !empty($matches)) {
			$followers = $matches[$this->followersregexp_group];
		}
		else {
			$followers = 0;
		}

		$pin1='';$pin2='';$pin3='';$pin4='';$pin5='';$pin6='';$pin7='';$pin8='';$pin9='';
		$pinlink1='';$pinlink2='';$pinlink3='';$pinlink4='';$pinlink5='';$pinlink6='';$pinlink7='';$pinlink8='';$pinlink9='';
		preg_match($this->pinsregexp, $this->html, $matches);
		if (isset($matches) && !empty($matches)) {
			echo ">>>>Got matches -<br/><! --";
			foreach ($matches as $value) {
				echo "Value: ".htmlentities($value)."<br />\n";
			}
			echo ">>>> -->That's all -<br/>";
			if(array_key_exists($this->pinsregexp_group[0], $matches)) {
				$pinlink1=$this->pinterest_url.$matches[$this->pinsregexp_group[0]];
				$pin1=preg_replace($this->imgmatchregexp, $this->imgsubstregexp,$matches[$this->pinsregexp_group[1]]);
			}
			if(array_key_exists($this->pinsregexp_group[2], $matches)) {
				$pinlink2=$this->pinterest_url.$matches[$this->pinsregexp_group[2]];
				$pin2=preg_replace($this->imgmatchregexp, $this->imgsubstregexp,$matches[$this->pinsregexp_group[3]]);
			}
			if(array_key_exists($this->pinsregexp_group[4], $matches)) {
				$pinlink3=$this->pinterest_url.$matches[$this->pinsregexp_group[4]];
				$pin3=preg_replace($this->imgmatchregexp, $this->imgsubstregexp,$matches[$this->pinsregexp_group[5]]);
			}
			if(array_key_exists($this->pinsregexp_group[6], $matches)) {
				$pinlink4=$this->pinterest_url.$matches[$this->pinsregexp_group[6]];
				$pin4=preg_replace($this->imgmatchregexp, $this->imgsubstregexp,$matches[$this->pinsregexp_group[7]]);
			}
			if(array_key_exists($this->pinsregexp_group[8], $matches)) {
				$pinlink5=$this->pinterest_url.$matches[$this->pinsregexp_group[8]];
				$pin5=preg_replace($this->imgmatchregexp, $this->imgsubstregexp,$matches[$this->pinsregexp_group[9]]);
			}
			if(array_key_exists($this->pinsregexp_group[10], $matches)) {
				$pinlink6=$this->pinterest_url.$matches[$this->pinsregexp_group[10]];
				$pin6=preg_replace($this->imgmatchregexp, $this->imgsubstregexp,$matches[$this->pinsregexp_group[11]]);
			}
			if(array_key_exists($this->pinsregexp_group[12], $matches)) {
				$pinlink7=$this->pinterest_url.$matches[$this->pinsregexp_group[12]];
				$pin7=preg_replace($this->imgmatchregexp, $this->imgsubstregexp,$matches[$this->pinsregexp_group[13]]);
			}
			if(array_key_exists($this->pinsregexp_group[14], $matches)) {
				$pinlink8=$this->pinterest_url.$matches[$this->pinsregexp_group[14]];
				$pin8=preg_replace($this->imgmatchregexp, $this->imgsubstregexp,$matches[$this->pinsregexp_group[15]]);
			}
			if(array_key_exists($this->pinsregexp_group[16], $matches)) {
				$pinlink9=$this->pinterest_url.$matches[$this->pinsregexp_group[16]];
				$pin9=preg_replace($this->imgmatchregexp, $this->imgsubstregexp,$matches[$this->pinsregexp_group[17]]);
			}
		}

			// put the data in an array
		$return = array('id' => $this->user_id, 'name' => $name, 'count' => $followers, 'url' => $this->url, 'pin1' => $pin1, 'pinlink1' => $pinlink1, 'pin2' => $pin2, 'pinlink2' => $pinlink2, 'pin3' => $pin3, 'pinlink3' => $pinlink3, 'pin4' => $pin4, 'pinlink4' => $pinlink4, 'pin5' => $pin5, 'pinlink5' => $pinlink5, 'pin6' => $pin6, 'pinlink6' => $pinlink6, 'pin7' => $pin7, 'pinlink7' => $pinlink7, 'pin8' => $pin8, 'pinlink8' => $pinlink8, 'pin9' => $pin9, 'pinlink9' => $pinlink9);

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

		// write data to file
		fwrite($handle, $regexp);

		// close file
		fclose($handle);
	}

	protected function getBadgeTmpls() {
		$file = $this->regexp_file;
		$this->nameregex = '';
		$this->followersregexp = '';
		$this->pinsregexp = '';
		$this->fullbadgetmpl='';

		//read it
		$handle = fopen($file, "r");

		//Get the regexps for each data.
		$index = 0;
		while (($line = fgets($handle)) !== false) {
			if($index == 0) {
				$this->regexpver = preg_replace('/Pinterest badge field regexps - /', '', trim($line));
			}
		    elseif($index == 1) {
				$this->nameregexp = trim($line);
			}
			elseif($index == 2) {
				$this->followersregexp = trim($line);
			}
			elseif($index == 3) {
				$this->pinsregexp = trim($line);
			}
			elseif($index == 4) {
				$this->fullbadgetmpl = trim($line);
			}
			elseif($index == 5) {
				$this->nameregexp_group = trim($line);
			}
			elseif($index == 6) {
				$this->followersregexp_group = trim($line);
			}
			elseif($index == 7) {
				$this->pinsregexp_group = explode(',', trim($line));
			}
			elseif($index == 8) {
				$this->imgmatchregexp = explode(',', trim($line));
			}
			elseif($index == 9) {
				$this->imgsubstregexp = explode(',', trim($line));
			}
			$index++;
		}
		echo ">>>>Got regexps - ".htmlentities($this->nameregexp)." - ".htmlentities($this->followersregexp)." - ".htmlentities($this->pinsregexp)." = ".htmlentities($this->fullbadgetmpl)."<br/>";
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