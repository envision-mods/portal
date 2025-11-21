<?php

declare(strict_types=1);

namespace Envision\Rector;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\RuleDefinition;

final class ImportNamespacesRector extends AbstractRector implements RectorInterface, ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private array $whitelistNamespaces = [];

    public function configure(array $configuration): void
    {
        $this->whitelistNamespaces = $configuration['whitelistNamespaces'] ?? [];
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Inline and import types from whitelisted namespaces; keep all others fully-qualified.'
        );
    }

    public function getNodeTypes(): array
    {
        return [
            Param::class,
            PropertyProperty::class,
            FunctionLike::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        $typeNode = null;

        if ($node instanceof Param || $node instanceof PropertyProperty) {
            $typeNode = $node->type;
        } elseif ($node instanceof FunctionLike) {
            $typeNode = $node->getReturnType();
        }

        if (! $typeNode instanceof FullyQualified) {
            return null;
        }

        $fqcn = $typeNode->toString();

        $matches = false;
        foreach ($this->whitelistNamespaces as $prefix) {
            if (str_starts_with($fqcn, $prefix)) {
                $matches = true;
                break;
            }
        }

        if (! $matches) {
            return null;
        }

        $shortName = $typeNode->getLast();

        if ($node instanceof Param || $node instanceof PropertyProperty) {
            $node->type = new Name($shortName);
        } elseif ($node instanceof FunctionLike) {
            $node->returnType = new Name($shortName);
        }

        // Add use statement only if missing
        $namespace = $this->betterNodeFinder->findParentType($node, Namespace_::class);
        if (! $namespace instanceof Namespace_) {
            return $node;
        }

        foreach ($namespace->stmts as $stmt) {
            if ($stmt instanceof Use_) {
                foreach ($stmt->uses as $useUse) {
                    if ($useUse->name->toString() === $fqcn) {
                        return $node;
                    }
                }
            }
        }

        array_unshift(
            $namespace->stmts,
            new Use_([new UseUse(new FullyQualified($fqcn))])
        );

        return $node;
    }
}
