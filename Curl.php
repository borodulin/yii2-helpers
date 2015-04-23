<?php
/**
 * @link https://github.com/borodulin/yii2-helpers
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-helpers/blob/master/LICENSE
 */

namespace conquer\helpers;

/**
 * 
 * @author Andrey Borodulin
 */
class Curl
{

	private function defaultOpts(){
		return array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_AUTOREFERER => true,
				CURLOPT_HEADER => false,
				CURLOPT_HEADERFUNCTION => array($this, 'headerCallback'),
				CURLOPT_MAXREDIRS => 5,
				CURLOPT_CONNECTTIMEOUT => 30,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_TIMEOUT => 30,
		);
	}
	
	
	public $content;

	public $info;
	
	public $header;
	
	public $cookies;
	
	public $errorCode;

	public $errorMessage;

	public $httpCode;

	public $totalTime;

	public function headerCallback($ch, $headerLine)
	{
		$this->header .= $headerLine;
		return strlen($headerLine);
	}
	
	public function isHttpOK()
	{
		return (strncmp($this->httpCode,'2',1)==0);
	}

	public function execute($url, $options=array(), $postData=array())
	{
		$ch = curl_init();
		
		foreach ($this->defaultOpts() as $k=>$v){
			if(!isset($options[$k]))
				$options[$k]=$v;
		}
		
		// !important see headerCallback()
		$options[CURLOPT_HEADER]=false;
		
		$options[CURLOPT_URL]=$url;
	
		if(!empty($postData)){
			$options[CURLOPT_POST]=true;
			$options[CURLOPT_POSTFIELDS]=$postData;
		}
		
		foreach ($options as $key=>$value){
			curl_setopt($ch, $key, $value);
		}
		$start = microtime(true);

		$this->content=$response=curl_exec($ch);

		if(preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $this->header, $matches)){
			$this->cookies=implode('; ', $matches[1]);
		}
		
		$this->totalTime=microtime(true)-$start;
	
		$this->errorCode=curl_errno($ch);
	
		if($this->errorCode)
		{
			$this->errorMessage = curl_error($ch);
		}
		else
			$this->info = curl_getinfo($ch);
	
		$this->httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);
			
		if($this->errorCode)
			return false;
		else if(!$this->isHttpOK()){
			$this->errorMessage=$this->content;
			return false;
		}
		else
			return true; 
	}
}
