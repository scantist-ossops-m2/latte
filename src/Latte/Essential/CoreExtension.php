<?php

/**
 * This file is part of the Latte (https://latte.nette.org)
 * Copyright (c) 2008 David Grudl (https://davidgrudl.com)
 */

declare(strict_types=1);

namespace Latte\Essential;

use Latte;
use Latte\Compiler\Nodes\Php\Scalar;
use Latte\Compiler\Nodes\TemplateNode;
use Latte\Compiler\Nodes\TextNode;
use Latte\Compiler\Tag;
use Latte\Compiler\TemplateParser;
use Latte\Runtime;
use Latte\RuntimeException;
use Nette;


/**
 * Basic tags and filters for Latte.
 */
final class CoreExtension extends Latte\Extension
{
	private array $functions;
	private bool $strict;
	private Runtime\Template $template;


	public function beforeCompile(Latte\Engine $engine): void
	{
		$this->functions = $engine->getFunctions();
		$this->strict = $engine->isStrictParsing();
	}


	public function beforeRender(Runtime\Template $template): void
	{
		$this->template = $template;
	}


	public function getTags(): array
	{
		return [
			'embed' => Nodes\EmbedNode::create(...),
			'define' => Nodes\DefineNode::create(...),
			'block' => Nodes\BlockNode::create(...),
			'layout' => Nodes\ExtendsNode::create(...),
			'extends' => Nodes\ExtendsNode::create(...),
			'import' => Nodes\ImportNode::create(...),
			'include' => $this->includeSplitter(...),

			'n:attr' => Nodes\NAttrNode::create(...),
			'n:class' => Nodes\NClassNode::create(...),
			'n:tag' => Nodes\NTagNode::create(...),

			'parameters' => Nodes\ParametersNode::create(...),
			'varType' => Nodes\VarTypeNode::create(...),
			'varPrint' => Nodes\VarPrintNode::create(...),
			'templateType' => Nodes\TemplateTypeNode::create(...),
			'templatePrint' => Nodes\TemplatePrintNode::create(...),

			'=' => Nodes\PrintNode::create(...),
			'do' => Nodes\DoNode::create(...),
			'php' => Nodes\DoNode::create(...), // obsolete
			'contentType' => Nodes\ContentTypeNode::create(...),
			'spaceless' => Nodes\SpacelessNode::create(...),
			'capture' => Nodes\CaptureNode::create(...),
			'l' => fn(Tag $tag) => new TextNode('{', $tag->position),
			'r' => fn(Tag $tag) => new TextNode('}', $tag->position),
			'syntax' => $this->parseSyntax(...),

			'dump' => Nodes\DumpNode::create(...),
			'debugbreak' => Nodes\DebugbreakNode::create(...),
			'trace' => Nodes\TraceNode::create(...),

			'var' => Nodes\VarNode::create(...),
			'default' => Nodes\VarNode::create(...),

			'try' => Nodes\TryNode::create(...),
			'rollback' => Nodes\RollbackNode::create(...),

			'foreach' => Nodes\ForeachNode::create(...),
			'for' => Nodes\ForNode::create(...),
			'while' => Nodes\WhileNode::create(...),
			'iterateWhile' => Nodes\IterateWhileNode::create(...),
			'sep' => Nodes\FirstLastSepNode::create(...),
			'last' => Nodes\FirstLastSepNode::create(...),
			'first' => Nodes\FirstLastSepNode::create(...),
			'skipIf' => Nodes\JumpNode::create(...),
			'breakIf' => Nodes\JumpNode::create(...),
			'exitIf' => Nodes\JumpNode::create(...),
			'continueIf' => Nodes\JumpNode::create(...),

			'if' => Nodes\IfNode::create(...),
			'ifset' => Nodes\IfNode::create(...),
			'ifchanged' => Nodes\IfChangedNode::create(...),
			'n:ifcontent' => Nodes\IfContentNode::create(...),
			'n:else' => Nodes\NElseNode::create(...),
			'switch' => Nodes\SwitchNode::create(...),
		];
	}


	public function getFilters(): array
	{
		return [
			'batch' => Filters::batch(...),
			'breakLines' => Filters::breaklines(...),
			'breaklines' => Filters::breaklines(...),
			'bytes' => Filters::bytes(...),
			'capitalize' => extension_loaded('mbstring')
				? Filters::capitalize(...)
				: function () { throw new RuntimeException('Filter |capitalize requires mbstring extension.'); },
			'ceil' => Filters::ceil(...),
			'clamp' => Filters::clamp(...),
			'dataStream' => Filters::dataStream(...),
			'datastream' => Filters::dataStream(...),
			'date' => Filters::date(...),
			'escape' => Latte\Runtime\Filters::nop(...),
			'escapeCss' => Latte\Runtime\Filters::escapeCss(...),
			'escapeHtml' => Latte\Runtime\Filters::escapeHtml(...),
			'escapeHtmlComment' => Latte\Runtime\Filters::escapeHtmlComment(...),
			'escapeICal' => Latte\Runtime\Filters::escapeICal(...),
			'escapeJs' => Latte\Runtime\Filters::escapeJs(...),
			'escapeUrl' => 'rawurlencode',
			'escapeXml' => Latte\Runtime\Filters::escapeXml(...),
			'explode' => Filters::explode(...),
			'first' => Filters::first(...),
			'firstUpper' => extension_loaded('mbstring')
				? Filters::firstUpper(...)
				: function () { throw new RuntimeException('Filter |firstUpper requires mbstring extension.'); },
			'floor' => Filters::floor(...),
			'checkUrl' => Latte\Runtime\Filters::safeUrl(...),
			'implode' => Filters::implode(...),
			'indent' => Filters::indent(...),
			'join' => Filters::implode(...),
			'last' => Filters::last(...),
			'length' => Filters::length(...),
			'lower' => extension_loaded('mbstring')
				? Filters::lower(...)
				: function () { throw new RuntimeException('Filter |lower requires mbstring extension.'); },
			'number' => 'number_format',
			'padLeft' => Filters::padLeft(...),
			'padRight' => Filters::padRight(...),
			'query' => Filters::query(...),
			'random' => Filters::random(...),
			'repeat' => Filters::repeat(...),
			'replace' => Filters::replace(...),
			'replaceRe' => Filters::replaceRe(...),
			'replaceRE' => Filters::replaceRe(...),
			'reverse' => Filters::reverse(...),
			'round' => Filters::round(...),
			'slice' => Filters::slice(...),
			'sort' => Filters::sort(...),
			'spaceless' => Filters::strip(...),
			'split' => Filters::explode(...),
			'strip' => Filters::strip(...), // obsolete
			'stripHtml' => Filters::stripHtml(...),
			'striphtml' => Filters::stripHtml(...),
			'stripTags' => Filters::stripTags(...),
			'striptags' => Filters::stripTags(...),
			'substr' => Filters::substring(...),
			'trim' => Filters::trim(...),
			'truncate' => Filters::truncate(...),
			'upper' => extension_loaded('mbstring')
				? Filters::upper(...)
				: function () { throw new RuntimeException('Filter |upper requires mbstring extension.'); },
			'webalize' => class_exists(Nette\Utils\Strings::class)
				? [Nette\Utils\Strings::class, 'webalize']
				: function () { throw new RuntimeException('Filter |webalize requires nette/utils package.'); },
		];
	}


	public function getFunctions(): array
	{
		return [
			'clamp' => Filters::clamp(...),
			'divisibleBy' => Filters::divisibleBy(...),
			'even' => Filters::even(...),
			'first' => Filters::first(...),
			'last' => Filters::last(...),
			'odd' => Filters::odd(...),
			'slice' => Filters::slice(...),
			'hasBlock' => fn(string $name): bool => $this->template->hasBlock($name),
		];
	}


	public function getPasses(): array
	{
		return [
			'internalVariables' => fn(TemplateNode $node) => Passes::internalVariablesPass($node, $this->strict),
			'overwrittenVariables' => Passes::overwrittenVariablesPass(...),
			'customFunctions' => fn(TemplateNode $node) => Passes::customFunctionsPass($node, $this->functions),
			'moveTemplatePrintToHead' => Passes::moveTemplatePrintToHeadPass(...),
			'nElse' => Nodes\NElseNode::processPass(...),
		];
	}


	/**
	 * {include [file] "file" [with blocks] [,] [params]}
	 * {include [block] name [,] [params]}
	 */
	private function includeSplitter(Tag $tag, TemplateParser $parser): Nodes\IncludeBlockNode|Nodes\IncludeFileNode
	{
		$tag->expectArguments();
		$mod = $tag->parser->tryConsumeTokenBeforeUnquotedString('block', 'file');
		if ($mod) {
			$block = $mod->text === 'block';
		} elseif ($tag->parser->stream->tryConsume('#')) {
			$block = true;
		} else {
			$name = $tag->parser->parseUnquotedStringOrExpression();
			$block = $name instanceof Scalar\StringNode && preg_match('~[\w-]+$~DA', $name->value);
		}
		$tag->parser->stream->seek(0);

		return $block
			? Nodes\IncludeBlockNode::create($tag, $parser)
			: Nodes\IncludeFileNode::create($tag);
	}


	/**
	 * {syntax ...}
	 */
	private function parseSyntax(Tag $tag, TemplateParser $parser): \Generator
	{
		$tag->expectArguments();
		$token = $tag->parser->stream->consume();
		$lexer = $parser->getLexer();
		$saved = [$lexer->openDelimiter, $lexer->closeDelimiter];
		$lexer->setSyntax($token->text, $tag->isNAttribute() ? null : $tag->name);
		[$inner] = yield;
		[$lexer->openDelimiter, $lexer->closeDelimiter] = $saved;
		return $inner;
	}
}
