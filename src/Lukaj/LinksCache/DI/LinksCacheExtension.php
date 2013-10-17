<?php

namespace Lukaj\LinksCache\DI;

use Lukaj;
use Nette;


if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
}
if (!class_exists('Nette\Configurator')) {
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

/*
 * @author Lukas Mazur
 * @license LGPL
 * @internal
 */
class LinksCacheExtension extends Nette\DI\CompilerExtension
{
	/** @var array */
	private $defaults = array(
			'expiration' => 864000, // 10 days
			'key' => 'global',
			'registerOnShutdown' => TRUE
		);
	
	
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);
		
		$definition = $builder->addDefinition($this->prefix('cache'))
				->setClass('Lukaj\LinksCache\Cache');
		unset($config['registerOnShutdown']);
		$definition->addSetup('setOptions', array($config));
	}
	
	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$config = $this->getConfig($this->defaults);
		if ($config['registerOnShutdown']) {
			$method = $class->methods['createServiceLinksCache__cache'];
			$exploded = explode("\n", $method->body);
			$lastLine = array_pop($exploded);
			$exploded[] = '$this->getService(\'application\')->onShutdown[] = array($service, \'save\');';
			$exploded[] = $lastLine;
			$method->body = implode("\n", $exploded);
		}
	}
	
	/**
	 * Workaround for Nette 2.0
	 * @param Nette\Configurator $configurator
	 * @return void
	 */
	public static function register(Nette\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($configurator, Nette\DI\Compiler $compiler) {
			$compiler->addExtension('linksCache', new LinksCacheExtension);
		};
	}
}
