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
    /**
     * CURL Options
     * @see curl_setopt_array
     * @var array
     */
    public $options;
    /**
     * You can use http_build_query function to post as url encoded parameters
     * @see CURLOPT_POSTFIELDS
     * @var mixed
     */
    public $postData;
    /**
     * Url
     * @see CURLOPT_URL
     * @var string
     */
    public $url;
    /**
     * Content
     * @see curl_exec
     * @var string
     */
    public $content;
    /**
     * @see curl_getinfo
     * @var array
     */
    public $info;
    /**
     * Header recieved with self::headerCallback() function
     * @see CURLOPT_HEADERFUNCTION 
     * @var string
     */
    public $header;
    /**
     * Error code
     * @see curl_errno
     * @var unknown
     */
    public $errorCode;
    /**
     * Error message
     * @see curl_error
     * @var string
     */
    public $errorMessage;

    
    public function __construct($url, $options=array(), $postData=array())
    {
        foreach ($this->defaultOpts() as $k => $v){
            if(!isset($options[$k]))
                $options[$k] = $v;
        }
        
        if(!empty($postData)){
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $postData;
        }
        
        $this->options = $options;
        $this->postData = $postData;
    }
    
    public function headerCallback($ch, $headerLine)
    {
        $this->header .= $headerLine;
        return strlen($headerLine);
    }
    
    public function getCookies()
    {
        if(preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $this->header, $matches)){
            return implode('; ', $matches[1]);
        }
        return null;
    }
    
    public function isHttpOK()
    {
        return (strncmp($this->info['http_code'],'2',1) == 0);
    }

    public function execute()
    {
        $ch = curl_init();
        
        // !important see headerCallback()
        $options[CURLOPT_HEADER] = false;
        
        $options[CURLOPT_URL] = $this->url;
    
        curl_setopt_array($ch, $this->options);
        
        $this->content = curl_exec($ch);

        $this->errorCode = curl_errno($ch);
    
        if($this->errorCode)
            $this->errorMessage = curl_error($ch);
        else
            $this->info = curl_getinfo($ch);
        
        curl_close($ch);
            
        if($this->errorCode)
            return false;
        else if(!$this->isHttpOK()){
            $this->errorMessage = $this->content;
            return false;
        }
        else
            return true; 
    }
    
    /**
     * 
     * @param Curl[] $urls
     */
    public static function multiExec($urls)
    {
        $nodes = array();
        /* @var $url Curl */
        foreach ($urls as $url){
            $ch = curl_init();
            $nodes[] = ['ch'=>$ch, 'url'=>$url];
            curl_setopt_array($ch, $url->options);
        }
        $mh = curl_multi_init();
        foreach ($nodes as $node){
            curl_multi_add_handle($mh, $node['ch']);
        }
        //execute the handles
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh);
        } while($running > 0);
        
        foreach ($nodes as $node){
            /* @var $url Curl */
            $url = $node['url'];

            $ch = $node['ch'];

            $url->errorCode = curl_errno($ch);
            if(!empty($url->errorCode))
                $url->errorMessage = curl_error($ch);
            else
                $url->info = curl_getinfo($ch);

            $url->content = curl_multi_getcontent($ch);
        }
        //close the handles
        foreach ($nodes as $node){
            curl_multi_remove_handle($mh, $node['ch']);
        }
        curl_multi_close($mh);
    }
    
}
