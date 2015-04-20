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
class XPath
{
	private $_doc;
	private $_xpath;
	
	
	public function __construct($content, $html=false)
	{
		$this->_doc=new \DOMDocument();
		if($html)
			@$this->_doc->loadHTML($content);
		else{
			@$this->_doc->loadXML($xml);
		}
		$this->_xpath = new \DOMXpath($this->_doc);
	}
	
	public static function isAssociative($array)
	{
		return !empty($array)&&(array_keys($array) !== range(0, count($array) - 1));
	}
	
	/**
	 * 
	 * @param DOMNodeList $elements
	 * @return multitype:multitype:NULL
	 */
	public static function xmlToArray($elements)
	{
		if($elements instanceof \DOMNodeList){
			if($elements->length==0)
				return null;
			elseif($elements->length==1)
				return self::xmlToArray($elements->item(0));
			else
			{
				$result=array();
				foreach ($elements as $element){
					$result[]=self::xmlToArray($element);
				}
				return $result;
			}
		}
		elseif($elements instanceof \DOMNode){
			if($elements->hasChildNodes()){
				$result=array();
				foreach ($elements->childNodes as $element){
					if($element->nodeType!=3){
						if(isset($result[$element->nodeName])){
							if(is_array($result[$element->nodeName])&& !self::isAssociative($result[$element->nodeName]))
								$result[$element->nodeName][]=self::xmlToArray($element);
							else{
								$v=$result[$element->nodeName];
								$result[$element->nodeName]=array();
								$result[$element->nodeName][]=$v;
								$result[$element->nodeName][]=self::xmlToArray($element);
							}
						}
						else
							$result[$element->nodeName]=self::xmlToArray($element);
						
					}
				}
				if(count($result)==0)
					return $elements->nodeValue;
				else
					return $result;
			}
			else{
				return $elements->nodeValue;
			}
		}
	} 
	
	/**
	 * 
	 * @param array() $paths
	 * @return array()
	 */
	public function queryAll($paths, $contextNode=null, $assoc=true)
	{
		$result=array();
		foreach ($paths as $name=>$path){
			$elements=$this->_xpath->query($path, $contextNode);
			if($assoc)
				$result[$name]=self::xmlToArray($elements);
			else
				$result[$name]=$elements;
		}
		return $result;
	}
	
	public function query($path, $contextNode=null, $assoc=true)
	{
		$elements=$this->_xpath->query($path,$contextNode);
		if($elements->length>0){
			if($assoc)
				return self::xmlToArray($elements);
			else
				return $elements;
		}
		return null;
	}

	
	public function queryOne($path, $contextNode=null, $assoc=true)
	{
		$elements=$this->_xpath->query($path,$contextNode);
		if($elements->length>0){
			$el=$elements->item(0);
			if($assoc)
				return self::xmlToArray($el);
			else
				return $el;
		}
		return null;
	}
	
	public function getNodePos($node)
	{
		$prevSibling=$node->previousSibling;
		$pos=1;
		while(!empty($prevSibling)){
			$prevSibling=$prevSibling->previousSibling;
			$pos++;
		}
		return $pos;
	} 
	
	public function findPos($path,$contextNode=null)
	{
		try {
			$elements=$this->_xpath->query($path,$contextNode);
			if($elements->length>0){
				if($elements->length==1)
					return $this->getNodePos($elements->item(0));
				else{
					$result=array();
					foreach ($elements as $element){
						$result[]=$this->getNodePos($element);
					}
					return $result;
				}
			}
			return null;
		} catch (Exception $e) {
			throw new CException($e->getMessage().' : '.$path);
		}
	}
	
	public function findPosAll($paths,$contextNode=null)
	{
		$result=array();
		foreach ($paths as $key=>$path){
			$result[$key]=$this->findPos($path, $contextNode);
		}
		return $result; 
	}
	
	
	public function evalute($path, $contaxtNode=null, $text=true)
	{
		$entries=$this->_xpath->evaluate($path, $contaxtNode);
		if(is_a($entries, 'DOMNodeList'))
			if($entries->length>0)
				return $entries->item(0)->nodeValue; 
			else
				return null;
		if($entries)
			return $entries;
		else
			return null;
	}
	
	/**
	 * @return DOMXpath
	 */
	public function getXPath()
	{
		return $this->_xpath;
	}

	public static function clearTextConcat($value)
	{
		if(is_string($value))
			return trim(preg_replace('/\s+/s', ' ', $value));
		if(is_array($value)){
			$result=array();
			foreach ($value as $val){
				$result[]=self::clearTextConcat($val);
			}
			return implode(' ', array_filter($result, 'strlen'));
		}
		return null;
	}
	
	public static function clearText(&$value)
	{
		if(is_string($value))
			$value=trim(preg_replace('/\s+/s', ' ', $value));
		if(is_array($value)){
			foreach ($value as &$val){
				self::clearText($val);
			}
		}
	}
	
	/**
	 * 
	 * @return DOMDocument
	 */
	public function getDoc()
	{
		return $this->_doc;
	}
	
	public function registerNamespace($prefix, $namespaceURI)
	{
		$this->_xpath->registerNamespace($prefix, $namespaceURI);
	}
}
