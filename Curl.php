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
   use CurlTrait;

    /**
     * @param string $url
     * @param array $options
     * @param mixed $postData
     */
    public function __construct($url, $options = [], $postData = [])
    {
        $this->setOptions($options);
        if(!empty($postData))
            $this->setPostData($postData);
        $this->setUrl($url);
    }

    /**
     * Executes the single curl
     * @param string $url
     * @param mixed $postData
     * @return boolean
     */
    public function execute($url = null, $postData = null)
    {
        if(!is_null($url))
            $this->url = $this->url;
        
        if(!is_null($postData))
            $this->setPostData($postData);
    
        $this->curl_execute();
            
        if($this->getErrorCode())
            return false;
        else if(!$this->isHttpOK()){
            $this->errorMessage = $this->getContent();
            return false;
        }
        else
            return true; 
    }
    
    /**
     * Executes parallels curls
     * @param Curl[] $urls
     */
    public static function multiExec($urls)
    {
        static::curl_multi_exec($urls);
    }
    
}
