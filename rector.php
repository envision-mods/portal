<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Contract\Rector\RectorInterface;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\RuleDefinition;

final class ExplicitNullableParamRector extends AbstractRector implements RectorInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Make parameter types explicitly nullable when default value is null',
            []
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Param::class];
    }

    /**
     * @param Param $node
     */
    public function refactor(Node $node): ?Node
    {
        // Skip parameters without type
        if ($node->type === null) {
            return null;
        }

        // Only act when default value is null
        if (! $node->default instanceof Node\Expr\ConstFetch) {
            return null;
        }

        if (strtolower($node->default->name->toString()) !== 'null') {
            return null;
        }

        // Already nullable? Skip
        if ($node->type instanceof NullableType) {
            return null;
        }

        // Convert "T" to "?T"
        $node->type = new NullableType($node->type);

        return $node;
    }
}

use Rector\Config\RectorConfig;

use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;

require 'ImportNamespacesRector.php';

use Envision\Rector\ImportNamespacesRector;

return RectorConfig::configure()
    ->withConfiguredRule(
        ImportNamespacesRector::class, [
                'EnvisionPortal\\'
                
        ]
    )
    ->withRules([
        // Add ?Type to params when default null or inferred via static analysis
        ExplicitNullableParamRector::class,

        //~ // Add ?Type to params when default null or inferred via static analysis
        //~ AddParamTypeDeclarationRector::class,

        //~ // Add ?Type to return types when null returned
        //~ AddReturnTypeDeclarationRector::class,

        //~ // Add ?Type to properties when assigned null or inferred via assigns
        //~ TypedPropertyFromAssignsRector::class,
    ])
	->withPaths([
		__DIR__ . '/src',
		__DIR__ . '/tests',
	])
	->withSkip([
		__DIR__ . '/src/ep_source/vendor',
	])
	//~ ->withImportNames(importShortClasses: false, removeUnusedImports: true)
	->withComposerBased(phpunit: true)
	->withPhpSets();