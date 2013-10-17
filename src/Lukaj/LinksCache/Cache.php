<?php

namespace Lukaj\LinksCache;

use Nette;


/*
 * @author Lukas Mazur
 * @license LGPL
 */
class Cache extends Nette\Object
{
	/** @var Nette\Application\Application */
	private $application;
	
	/** @var Nette\Caching\Cache */
	private $cache;
	
	/** @var array */
	private $data;
	
	/** @var array */
	private $options = array();
	
	/** @var bool */
	private $changed = FALSE;
	
		
	/**
	 * @param Nette\Caching\IStorage $storage
	 * @param Nette\Application\Application $application
	 * @return void
	 */
	public function __construct(Nette\Caching\IStorage $storage, Nette\Application\Application $application)
	{
		$this->cache = new Nette\Caching\Cache($storage, 'Lukaj.LinksCache');
		$this->application = $application;
	}
	
	/**
	 * @param string destination
	 * @param array $args
	 * @return string|NULL
	 */
	public function getFromCache($destination, $args)
	{
		if (empty($this->data)) {
			$this->data = $this->cache->load($this->options['key']);
		}

		$key = $this->prepareKey($destination, $args);
		if (isset($this->data[$key])) {
			return $this->data[$key];
		} else {
			return NULL;
		}
	}
	
	/**
	 * @param string $destination
	 * @param array $args
	 * @param string $link
	 * @return string returns the parameter $link just for convenience
	 */
	public function add($destination, $args, $link)
	{
		if (empty($this->data)) {
			$this->data = $this->cache->load($this->options['key']);
		}
		
		$this->data[$this->prepareKey($destination, $args)] = $link;
		$this->changed = TRUE;

		return $link;
	}
	
	/**
	 * @param string $destination
	 * @param array $args
	 * @return string
	 */
	public function link($destination, $args = array())
	{
		if ($link = $this->getFromCache($destination, $args) === NULL) {
			$link = $this->add($destination, $args, $this->application->getPresenter()->link($destination, $args));
		}
		
		return $link;
	}
	
	/**
	 * @param mixed $options
	 * @return void
	 */
	public function setOptions(array $options)
	{
		if (isset($options['key'])) {
			$options['key'] = (string)$options['key'];
			if (isset($this->data) && $this->changed === TRUE) {
				$this->save();
				$this->changed = FALSE;
				unset($this->data);
			}
		}
		
		$this->options = $options + $this->options;
	}
	
	/**
	 * Application onShutdown event handler
	 * @return void
	 */
	public function save()
	{
		if ($this->changed) {
			$this->cache->save($this->options['key'], $this->data, isset($this->options['expiration']) ? array(Nette\Caching\Cache::EXPIRATION => $this->options['expiration']) : array());
		}
	}
	
	/**
	 * @param string $destination
	 * @param array $args
	 * @return string
	 */
	private function prepareKey($destination, $args)
	{
		return md5($destination . serialize($args));
	}
}
