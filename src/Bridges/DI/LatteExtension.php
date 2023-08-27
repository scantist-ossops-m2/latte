<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Latte\Bridges\DI;

use Latte;
use Nette;
use Nette\Schema\Expect;


/**
 * Latte extension for Nette DI.
 */
final class LatteExtension extends Nette\DI\CompilerExtension
{
	private bool $debugMode;
	private string $tempDir;


	public function __construct(string $tempDir, bool $debugMode = false)
	{
		$this->tempDir = $tempDir;
		$this->debugMode = $debugMode;
	}


	public function getConfigSchema(): Nette\Schema\Schema
	{
		return Expect::structure([
			'extensions' => Expect::arrayOf('string|Nette\DI\Definitions\Statement'),
			'strictTypes' => Expect::bool(false),
			'strictParsing' => Expect::bool(false),
			'phpLinter' => Expect::string(),
		]);
	}


	public function loadConfiguration()
	{
		$config = $this->config;
		$builder = $this->getContainerBuilder();

		$factory = $builder->addFactoryDefinition($this->prefix('factory'))
			->setImplement(LatteFactory::class)
			->getResultDefinition()
				->setFactory(Latte\Engine::class)
				->addSetup('setTempDirectory', [$this->tempDir])
				->addSetup('setAutoRefresh', [$this->debugMode])
				->addSetup('setStrictTypes', [$config->strictTypes])
				->addSetup('setStrictParsing', [$config->strictParsing])
				->addSetup('enablePhpLinter', [$config->phpLinter]);

		foreach ($config->extensions as $extension) {
			$this->addExtension($extension);
		}
	}


	public function addExtension(Nette\DI\Definitions\Statement|string $extension): void
	{
		$extension = is_string($extension)
			? new Nette\DI\Definitions\Statement($extension)
			: $extension;

		$builder = $this->getContainerBuilder();
		$builder->getDefinition($this->prefix('factory'))
			->getResultDefinition()
			->addSetup('addExtension', [$extension]);
	}
}
