<?php

namespace Lukaj\LinksCache;

/*
 * @author Lukas Mazur
 * @license LGPL
 */
trait LinksCacheTrait
{
	/** @var Lukaj\LinksCache\Cache */
	protected $_linksCache;
	
	/** @var bool */
	private $_linksCacheIsOn = TRUE;
	
	
	/**
	 * @param Lukaj\LinksCache\Cache $cachedLinks
	 * @return void
	 */
	public function injectLinksCache(Cache $linksCache)
	{
		$this->_linksCache = $linksCache;
	}
	
	/**
	 * @param string $destination
	 * @param array $args
	 * @return string
	 */
	public function link($destination, $args = array())
	{
		if (!$this->_linksCacheIsOn) {
			return parent::link($destination, $args);
		}
	
		if (($link = $this->_linksCache->getFromCache($destination, $args)) === NULL) {
			$link = $this->_linksCache->add($destination, $args, parent::link($destination, $args));
		}
		return $link;
	}
	
	/**
	 * @return void
	 */
	public function startLinksCache()
	{
		$this->_linksCacheIsOn = TRUE;
	}
	
	/**
	 * @return void
	 */
	public function stopLinksCache()
	{
		$this->_linksCacheIsOn = FALSE;
	}
}
