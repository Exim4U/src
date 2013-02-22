<?PHP
/*
PHPLiveX, PHP / AJAX Library & Framework
Version 2.6, Released In 05.02.2010
Copyright (C) 2006-2010 Arda Beyazoglu
http://www.phplivex.com, arda@beyazoglu.com

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 3 of the License, or any later version. See the GNU
Lesser General Public License for more details.
*/

class PHPLiveX {

	/**
	 * charset encoding of response
	 *
	 * @var String
	 */
	private $Encoding = "UTF-8";
	
	/**
	 * Array of ajaxified functions
	 *
	 * @var Array
	 */
	private $Functions = array();
	
	/**
	 * Array of ajaxified class methods
	 *
	 * @var Array
	 */
	private $ClassMethods = array();
	
	/**
	 * Array of ajaxified class variables
	 *
	 * @var Array
	 */
	private $ClassVariables = array();
	
	/**
	 * Array of ajaxified object methods
	 *
	 * @var Array
	 */
	private $ObjectMethods = array();
	
	/**
	 * Array of ajaxified object properties
	 *
	 * @var Array
	 */
	private $ObjectProperties = array();
	
	/**
	 * Indicates whether run method was called before
	 *
	 * @var Boolean
	 */
	private $Ran = false;
	
	
	/**
	 * Constructs the class and ajaxifies specified functions optionally
	 *
	 * @param Array|String $functions: String function name or array of them to ajaxify. Optional
	 * @param Array $config: Configuration values (encoding => UTF-8)
	 */
	public function __construct($functions = array(), $config = array()){
		if(!empty($functions)){
			if(is_string($functions)) $functions = array($functions);
			$this->Ajaxify($functions);
		}
		
		if(array_key_exists("encoding", $config)) $this->Encoding = strtoupper($config["encoding"]);
	}

	/**
	 * Prepares specified functions for ajax requests
	 *
	 * @param Array $functions: Array of function names to ajaxify
	 */
	public function Ajaxify($functions = array()){
		if(!empty($functions)) $this->Execute();
		if(is_string($functions)) $functions = array($functions);
		foreach($functions as $key => $val){
			$val = stripslashes(trim($val));
			$all_func = get_defined_functions();
			if(function_exists($val) && in_array(strtolower($val), $all_func["user"])){
				if(!in_array($val, $this->Functions)){
					$this->Functions[] = $val;
				}
			}else{
				throw new Exception("'$val' function doesn't exist!");
			}
		}
		reset($this->Functions);
	}

	/**
	 * Prepares all user defined functions for ajax requests
	 *
	 */
	public function AjaxifyAll(){
		$functions = get_defined_functions();
		$this->Ajaxify($functions["user"]);
	}

	/**
	 * Prepares specified class methods for ajax requests
	 *
	 * @param Array $methods: Array of methods ("myClass" => "myMethod") to ajaxify
	 * @param Boolean ajaxify_vars: Indicates whether the class variables are ajaxified
	 */
	public function AjaxifyClassMethods($methods = array(), $ajaxify_vars = false){
		if(!empty($methods)) $this->Execute();
		foreach($methods as $class => $method){
			$class = stripslashes(trim($class));
			
			if(!array_key_exists($class, $this->ClassMethods)){
				if(!is_array($method)) $method = array($method);
				$this->ClassMethods[$class] = $method;
				if($ajaxify_vars) $this->ClassProperties[$class] = get_class_vars($class);
			}
		}
		reset($this->ClassMethods);
	}

	/**
	 * Prepares specified classes for ajax requests
	 *
	 * @param Array $classes: Class names
	 * @param Boolean ajaxify_vars: Indicates whether the class variables are ajaxified
	 */
	public function AjaxifyClasses($classes = array(), $ajaxify_vars = false){
		if(!empty($classes)) $this->Execute();
		foreach($classes as $class){
			$class = stripslashes(trim($class));
			
			if(!array_key_exists($class, $this->ClassMethods)){
				$methods = get_class_methods($class);
				if($methods[0] == "__construct") $methods = array_slice($methods, 1);
				$this->ClassMethods[$class] = $methods;
				if($ajaxify_vars) $this->ClassVariables[$class] = get_class_vars($class);
			}
		}
		reset($this->ClassMethods);
	}
	
	/**
	 * Prepares specified object methods for ajax requests
	 *
	 * @param Array $methods: Array of methods ("myObject" => "myMethod") to ajaxify
	 * @param Boolean ajaxify_properties: Indicates whether the object properties are ajaxified
	 */
	public function AjaxifyObjectMethods($methods = array(), $ajaxify_properties = false){
		if(!empty($methods)) $this->Execute();
		foreach($methods as $object => $method){
			$object = stripslashes(trim($object));
			global $$object;
			
			if(!array_key_exists($object, $this->ObjectMethods)){
				if(!is_array($method)) $method = array($method);
				$this->ObjectMethods[$object] = $method;
				if($ajaxify_properties) $this->ObjectProperties[$object] = get_object_vars(get_class($$object));
			}
		}
		reset($this->ObjectMethods);
	}

	/**
	 * Prepares specified objects for ajax requests
	 *
	 * @param Array $objects: Object names
	 * @param Boolean ajaxify_properties: Indicates whether the object properties are ajaxified
	 */
	public function AjaxifyObjects($objects = array(), $ajaxify_properties = false){
		if(!empty($objects)) $this->Execute();
		foreach($objects as $object){
			$object = stripslashes(trim($object));
			global $$object;
			
			if(!array_key_exists($object, $this->ObjectMethods)){
				$methods = get_class_methods($$object);
				if($methods[0] == "__construct") $methods = array_slice($methods, 1);
				$this->ObjectMethods[$object] = $methods;
				if($ajaxify_properties) $this->ObjectProperties[$object] = get_object_vars($$object);
			}
		}
		reset($this->ObjectMethods);
	}
	
	/**
	 * Converts a utf-8 string to requested encoding
	 *
	 * @param String $string
	 * @param String $encoding
	 * @return String decoded string
	 */
	public static function Decode($string, $encoding){
		return iconv("UTF-8", $encoding."//IGNORE", urldecode($string));
	}

	/**
	 * Calls the function specified by the incoming ajax request
	 *
	 */
	private function Execute(){
		if(!empty($_REQUEST["plxf"])){
			$function = $_REQUEST["plxf"];
			$args = (!empty($_REQUEST["plxa"])) ? $_REQUEST["plxa"] : array();
			
			foreach ($args as $key => $val){
				$val = ($this->Encoding == "UTF-8") ? urldecode($val) : self::Decode($val, $this->Encoding);
				if(preg_match('/<plxobj[^>]*>(.|\n|\t|\r)*?<\/plxobj>/', stripslashes($val), $matches)){
					if(function_exists("json_decode")){
						$jsobj = substr($matches[0], 8, -9);
						$val = json_decode($jsobj);
					}else{
						throw new Exception("'json_decode' function could not be found! You need json functions for object manipulation!");
					}
				}
				$args[$key] = $val;
			}
			
			if(strpos($function, "::") !== false){
				$parts = explode("::", $function);
				if(!class_exists($parts[0])) return;
				$response = call_user_func_array(array($parts[0], $parts[1]), $args);
			}else if(strpos($function, "->") !== false){
				$parts = explode("->", $function);
				global $$parts[0];
				$object = @$$parts[0];
				if(!isset($$parts[0])) return;
				$response = call_user_func_array(array($object, $parts[1]), $args);
			}else{
				$functions = get_defined_functions();
				if(!function_exists($function)) return;
				$response = (in_array(strtolower($function), $functions["user"])) ? call_user_func_array($function, $args) : "";
			}
			
			if(is_bool($response)) $response = (int) $response;
			else if(function_exists("json_encode") && (is_array($response) || is_object($response))) $response = json_encode($response);
			echo "<phplivex>" . $response . "</phplivex>";
			exit();
		}
	}

	/**
	 * Creates the javascript reflections of the ajaxified php functions
	 *
	 * @param String $function
	 * @return String JS code
	 */
	private function CreateFunction($function){
		return "function " . $function . "(){ return new PHPLiveX().Callback(\"{$function}\", {$function}.arguments); }\r\n";
	}
	
	/**
	 * Creates the javascript reflections of the ajaxified php classes
	 *
	 * @return String JS code
	 */
	private function CreateClasses(){
		$js = "";
		foreach($this->ClassMethods as $class => $methods){
			$js .= "var {$class} = {\r\n ";
			if(!empty($this->ClassVariables[$class])){
				foreach($this->ClassVariables[$class] as $variable => $value){
					if(is_string($value)) $value = "\"$value\"";
					else if((is_array($value) || is_object($value)) && function_exists("json_encode")) $value = json_encode($value);
					else if(is_bool($value)){
						$value = ($value == false) ? "false" : "true";
					}
					if(empty($value)) $value = "null";
					$js .= "\"{$variable}\": $value,\r\n";
				}
			}
			foreach($methods as $method) $js .= "\"{$method}\": function(){ return new PHPLiveX().Callback({\"cls\": \"{$class}\", \"method\": \"{$method}\"}, {$class}.{$method}.arguments); },\r\n";
			$js = substr($js, 0, strlen($js) - 3) . "\r\n};\r\n";
		}
		return $js . "\r\n";
	}
	
	/**
	 * Creates the javascript reflections of the ajaxified php objects
	 *
	 * @return String JS code
	 */
	private function CreateObjects(){
		$js = "";
		foreach($this->ObjectMethods as $object => $methods){
			$js .= "var {$object} = {\r\n ";
			if(!empty($this->ObjectProperties[$object])){
				foreach($this->ObjectProperties[$object] as $property => $value){
					if(is_string($value)) $value = "\"$value\"";
					else if((is_array($value) || is_object($value)) && function_exists("json_encode")) $value = json_encode($value);
					else if(is_bool($value)){
						$value = ($value == false) ? "false" : "true";
					}
					if(empty($value)) $value = "null";
					$js .= "\"{$property}\": $value,\r\n";
				}
			}
			foreach($methods as $method) $js .= "\"{$method}\": function(){ return new PHPLiveX().Callback({\"obj\": \"{$object}\", \"method\": \"{$method}\"}, {$object}.{$method}.arguments); },\r\n";
			$js = substr($js, 0, strlen($js) - 3) . "\r\n};\r\n";
		}
		return $js . "\r\n";
	}

	/**
	 * Include javascript source file and creates neccesary javascript codes
	 *
	 * @param String $filepath: The path of phplivex javascript source file. False to prevent this include
	 * @param Array $plugins: The paths of phplivex plugin files
	 * @param Boolean $return: True to return javascript. Default is false.
	 */
	public function Run($filepath = "phplivex.js", $plugins = array(), $return = false){
		$js = ($filepath !== false || $this->Ran) ? "<script type=\"text/javascript\" src=\"{$filepath}\"></script>\r\n" : "";
		$js .= "<script type=\"text/javascript\">\r\n";
		foreach($this->Functions as $function) $js .= $this->CreateFunction($function);
		if(count($this->ClassMethods) != 0) $js .= $this->CreateClasses();
		if(count($this->ObjectMethods) != 0) $js .= $this->CreateObjects();
		$js .= "</script>\r\n";
		foreach ($plugins as $path) $js .= "<script type=\"text/javascript\" src=\"{$path}\"></script>\r\n";
		echo $js;
		
		$this->Ran = true;
	}
	
	/**
	 * Gets information about current upload process
	 *
	 * @param String $id: The id attribute of the upload field
	 * @param String $uid: The unique id of the upload
	 * @param String $tmp_dir: The temporary directory path for upload. Default value is "tmp"
	 */
	public static function ProgressUpload($id, $uid, $tmp_dir){
		$temp_files = array(
			"info" => $tmp_dir . "/" . $uid . "_flength",
			"data" => $tmp_dir . "/" . $uid . "_postdata",
			"error" => $tmp_dir . "/" . $uid . "_err",
			"qstring" => $tmp_dir . "/" . $uid . "_qstring",
			"signal" => $tmp_dir . "/" . $uid . "_signal"
		);
		
		if(file_exists($temp_files["error"])) {
			$error = file_get_contents($temp_files["error"]);
			return array("error" => $error, "id" => $id);
		}
		
		$uploaded = 0; $total = 0; $percent = 0;
		$started = true;
		
		try {
		
		if($total = file_get_contents($temp_files["info"])){
			$uploaded = filesize($temp_files["data"]);
			$percent = intval(($uploaded / $total) * 100);
			if($percent > 100) $percent = 100;
		}else{
			$started = false;
		}
		
		} catch (Exception $e) {
			return $e->getMessage();
		}
		
		$result = array("total" => $total, "uploaded" => $uploaded, "percent" => $percent, "id" => $id);
		
		if($percent == 100){
			$qstring = file_get_contents($temp_files["qstring"]);
			parse_str($qstring, $q);
			
			foreach ($temp_files as $path){
				if(file_exists($path)) unlink($path);
			}
			
			$result = array_merge($result, array(
				"file_name" => $q["file"]["name"][0],
				"file_tmp_name" => $q["file"]["tmp_name"][0]
			));
		}
		
		return $result;
	}
}

?>