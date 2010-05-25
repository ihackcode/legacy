<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

class Legacy_HeaderScript
{
	public $mMainLibrary = 'google';
	public $mMainVersion = "1";
	public $mUIVersion = "1";
	public $mMainUrl = "";	//url of jQuery Main library file
	public $mUIUrl = "";	//url of jQuery UI library file

	protected $_mLibrary = array();
	protected $_mScript = array();
	protected $_mMeta = array('keywords'=>'','description'=>'','robots'=>'','rating'=>'','author'=>'','copyright'=>'',);
	protected $_mOnloadScript = array();
	protected $_mStylesheet = array();

	public $mUsePrototype = false;	//use prototype.js ?
	public $mFuncNamePrefix = "";	//jQuery $() function's name prefix for compatibility with prototype.js

	/**
	 * __construct
	 * 
	 * @param	void
	 * 
	 * @return	void
	**/
	public function __construct()
	{
		$root = XCube_Root::getSingleton();
		$this->mMainLibrary = $root->getSiteConfig('jQuery', 'library');
	
		if($this->mMainLibrary=="google"){
			$this->mMainVersion = $root->getSiteConfig('jQuery', 'MainVersion');
			$this->mUIVersion = $root->getSiteConfig('jQuery', 'UIVersion');
		}
		elseif($this->mMainLibrary=="local"){
			$this->mMainUrl = $root->getSiteConfig('jQuery', 'MainUrl');
			$this->mUIUrl = $root->getSiteConfig('jQuery', 'UIUrl');
		}
	
		//use compatibility mode with prototype.js ?
		if($root->getSiteConfig('jQuery', 'usePrototype')==1){
			$this->mUsePrototype = true;
			$this->mPrototypeUrl = $root->getSiteConfig('jQuery', 'prototypeUrl');
			$this->mFuncNamePrefix = $root->getSiteConfig('jQuery', 'funcNamePrefix');
		}
	
		$this->_setupDefaultStylesheet();
	}

	/**
	 * _setupDefaultCss
	 * 
	 * @param	void
	 * 
	 * @return	void
	**/
	public function _setupDefaultStylesheet()
	{
		if($this->_getRenderConfig('css_file')) $this->addStylesheet($this->_getRenderConfig('css_file'), false);
	}

	/**
	 * addLibrary
	 * 
	 * @param	string $url
	 * @param	bool $xoopsUrl
	 * 
	 * @return	void
	**/
	public function addLibrary($url, $xoopsUrl=true)
	{
		$libUrl = ($xoopsUrl==true) ? XOOPS_URL. $url : $url;
		if(! in_array($libUrl, $this->_mLibrary)){
			 $this->_mLibrary[] = $libUrl;
		}
	}

	/**
	 * addStylesheet
	 * 
	 * @param	string $url
	 * @param	bool $xoopsUrl
	 * 
	 * @return	void
	**/
	public function addStylesheet($url, $xoopsUrl=true)
	{
		$libUrl = ($xoopsUrl==true) ? XOOPS_URL. $url : $url;
		if(! in_array($libUrl, $this->_mStylesheet)){
			 $this->_mStylesheet[] = $libUrl;
		}
	}

	/**
	 * addScript
	 * 
	 * @param	string $script
	 * @param	bool $isOnloadFunction
	 * 
	 * @return	void
	**/
	public function addScript($script, $isOnloadFunction=true)
	{
		if($isOnloadFunction==true){
			$this->_mOnloadScript[] = $script;
		}
		else{
			$this->_mScript[] = $script;
		}
	}

	/**
	 * getLibraryArr
	 * 
	 * @param	void
	 * 
	 * @return	string[]
	**/
	public function getLibraryArr()
	{
		return $this->_mLibrary;
	}

	/**
	 * getScriptArr
	 * 
	 * @param	bool	$isOnloadFunction
	 * 
	 * @return	string[]
	**/
	public function getScriptArr($isOnloadFunction=true)
	{
		if($isOnloadFunction==true){
			return $this->_mOnloadScript;
		}
		else{
			return $this->_mScript;
		}
	}

	/**
	 * setMeta
	 * 
	 * @param	string	$rel
	 * @param	string	$type
	 * @param	string	$title
	 * @param	string	$href
	 * 
	 * @return	void
	**/
	public function setLink(/*** string ***/ $rel, /*** string ***/ $type, /*** string ***/ $title, /*** string ***/ $href)
	{
		$this->_mLink[] = array('rel'=>$rel, 'type'=>$type, 'title'=>$title, 'href'=>$href);
	}

	/**
	 * setMeta
	 * 
	 * @param	string	$name
	 * @param	string	$content
	 * 
	 * @return	void
	**/
	public function setMeta(/*** string ***/ $name, /*** string ***/ $content)
	{
		$this->_mMeta[$name] = $content;
	}

	/**
	 * getMeta
	 * 
	 * @param	string	$name
	 * 
	 * @return	string
	**/
	public function getMeta(/*** string ***/ $name)
	{
		return $this->_mMeta[$name];
	}

	/**
	 * createLibraryTag
	 * 
	 * @param	void
	 * 
	 * @return	string
	**/
	public function createLibraryTag()
	{
		$html = "";
	
		//prototype.js compatibility
		if($this->mUsePrototype){
			$html .= '<script type="text/javascript" src="'. $this->mPrototypeUrl .'"></script>';
		}
		
		//load main library
		if($this->mMainLibrary=='google'){
			$html .= $this->_loadGoogleJQueryLibrary();
		}
		elseif($this->mMainLibrary=='local'){
			$html .= $this->_loadLocalJQueryLibrary();
		}
	
		//load plugin libraries
		foreach($this->_mLibrary as $lib){
			$html .= "<script type=\"text/javascript\" src=\"". $lib ."\"></script>\n";
		}
	
		//load css
		foreach($this->_mStylesheet as $css){
			$html .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"". $css ."\" />\n";
		}
	
		//load link
		foreach($this->_mStylesheet as $css){
			$html .= "<link type=\"text/css\" rel=\"stylesheet\" href=\"". $css ."\" />\n";
		}
	
		//set rss auto-discovery
		if($this->_getRenderConfig('feed_url')){
			$html .= sprintf('<link rel="alternate" type="application/rss+xml" title="rss" href="%s" />'."\n", $this->_getRenderConfig('feed_url'));
		}
		return $html;
	}

	/**
	 * _loadGoogleJQueryLibrary
	 * 
	 * @param	void
	 * 
	 * @return	string
	**/
	protected function _loadGoogleJQueryLibrary()
	{
		$apiKey = XCube_Root::getSingleton()->getSiteConfig('jQuery', 'GoogleApiKey');
		$apiKey = (isset($apiKey)) ? '?key='.$apiKey : null;
		return '<script type="text/javascript" src="http://www.google.com/jsapi'.$apiKey.'"></script>
<script type="text/javascript"><!--
google.load("language", "1"); 
google.load("jquery", "'. $this->mMainVersion .'");
google.load("jqueryui", "'. $this->mUIVersion .'");
//-->
</script>
';
	}

	/**
	 * _loadLocalJQueryLibrary
	 * 
	 * @param	void
	 * 
	 * @return	string
	**/
	protected function _loadLocalJQueryLibrary()
	{
		$html = "";
		if($this->mMainUrl) $html .= '<script type="text/javascript" src="'. $this->mMainUrl .'"></script>';
		if($this->mUIUrl) $html .= '<script type="text/javascript" src="'. $this->mUIUrl .'"></script>';
	
		return $html;
	}

	/**
	 * createOnloadFunctionTag
	 * 
	 * @param	void
	 * 
	 * @return	string
	**/
	public function createOnloadFunctionTag()
	{
		$html = null;
		if(count($this->_mOnloadScript)>0||count($this->_mScript)>0){
			$html = "<script type=\"text/javascript\"><!--\n";
			if($this->mMainLibrary == "google"){
				$html .= "google.setOnLoadCallback(function() {\n";
			}
			else{
				$html .= "$(document).ready(function(){\n";
			}
			$html .= $this->_makeScript(true);
			$html .= "\n});\n";
			$html .= $this->_makeScript(false);
			$html .= "// --></script>"."\n";
		}
		return $html;
	}

	/**
	 * _makeScript
	 * 
	 * @param	bool	$isOnloadFunction
	 * 
	 * @return	string
	**/
	protected function _makeScript($isOnloadFunction=true)
	{
		$html = null;
		$scriptArr = ($isOnloadFunction===true) ? $this->_mOnloadScript : $this->_mScript;
		foreach($scriptArr as $script){
			$html .= $this->_convertFuncName($script);
		}
		return $html;
	}

	/**
	 * _convertFuncName
	 * 
	 * @param	string $script
	 * 
	 * @return	string
	**/
	protected function _convertFuncName($script)
	{
		if($this->mFuncNamePrefix){
			$script = str_replace("$(", $this->mFuncNamePrefix."$(", $script);
		}
		return $script;
	}

	/**
	 * _getRenderConfig
	 * 
	 * @param	string $key
	 * 
	 * @return	string
	**/
	protected function _getRenderConfig($key)
	{
		$handler =& xoops_gethandler('config');
		$configArr =& $handler->getConfigsByDirname('legacyRender');
		return $configArr[$key];
	}

}
?>
