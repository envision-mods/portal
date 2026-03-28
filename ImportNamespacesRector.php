<?php

declare(strict_types=1);

namespace Envision\Rector;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Name\Relative;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Instanceof_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\RuleDefinition;

final class ImportNamespacesRector extends AbstractRector implements ConfigurableRectorInterface
{
    /** @var string[] */
    private array $namespacePrefixes = [];

    public function configure(array $configuration): void
    {
        // configuration key can be 'namespacePrefixes' or direct array
        $this->namespacePrefixes = $configuration['namespacePrefixes'] ?? $configuration;
        if (! is_array($this->namespacePrefixes)) {
            $this->namespacePrefixes = [];
        }
        // normalize prefixes (no leading slash, trailing backslash trimmed)
        $this->namespacePrefixes = array_map(function ($p) {
            return rtrim(ltrim((string) $p, '\\'), '\\') . '\\';
        }, $this->namespacePrefixes);
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Import FQCNs and partially-qualified names matching configured namespace prefixes.');
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [
            Param::class,
            PropertyProperty::class,
            FunctionLike::class,
            Class_::class,
            Interface_::class,
            TraitUse::class,
            New_::class,
            StaticCall::class,
            ClassConstFetch::class,
            Instanceof_::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        foreach ($this->extractTypeNodes($node) as [$typeNode, $setter]) {
            try {
                $this->processTypeNode($typeNode, $setter, $node);
            } catch (\Throwable $e) {
                // Be defensive in custom rules; do not break the run — log for debugging if needed.
                // You can add debug printing here during development:
                // echo "ImportNamespacesRector error: " . $e->getMessage();
            }
        }

        return $node;
    }

    /**
     * Process a single type node (recursively handles nullable/union).
     *
     * @param Node $typeNode Name|FullyQualified|Identifier|NullableType|UnionType
     * @param callable $setter  function(Name|FullyQualified|Identifier $replacement): void
     * @param Node $parentNode
     */
    private function processTypeNode(Node $typeNode, callable $setter, Node $parentNode): void
    {
        // unwrap nullable
        if ($typeNode instanceof NullableType) {
            $this->processTypeNode($typeNode->type, $setter, $parentNode);
            return;
        }

        // union types
        if ($typeNode instanceof UnionType) {
            foreach ($typeNode->types as $inner) {
                $this->processTypeNode($inner, $setter, $parentNode);
            }
            return;
        }

        // only names / identifiers are relevant
        if (! $typeNode instanceof Name && ! $typeNode instanceof FullyQualified && ! $typeNode instanceof Identifier && ! $typeNode instanceof Relative) {
            return;
        }

        // get plain token for scalar check
        $token = $typeNode instanceof Identifier ? $typeNode->toString() : (string) $typeNode->toString();

        // skip scalar / builtin / pseudo types
        $scalarTypes = [
            'int','string','bool','float','array','iterable','object','mixed',
            'void','false','null','true','self','static','parent','callable'
        ];
        if (in_array(strtolower($token), $scalarTypes, true)) {
            return;
        }

        // resolve to FQCN (no leading slash)
        $fqcn = $this->resolveFqcn($typeNode, $parentNode);

        // whitelist check
        $matches = false;
        foreach ($this->namespacePrefixes as $prefix) {
            // prefix normalized with trailing backslash in configure()
            if (str_starts_with($fqcn . '\\', $prefix)) {
                $matches = true;
                break;
            }
        }
        if (! $matches) {
            return;
        }

        // short name (last part)
        $shortName = $this->extractShortNameFromNode($typeNode);

        if ($shortName === '') {
            return;
        }

        // ensure no short-name collision (very simple check)
        $nsNode = $this->betterNodeFinder->findParentType($parentNode, Namespace_::class);
        if ($nsNode instanceof Namespace_) {
            if ($this->shortNameConflicts($nsNode, $shortName, $fqcn)) {
                // do not import if conflict found
                return;
            }
        }

        // replace node with unqualified Name(short)
        $setter(new Name($shortName));

        // add use if missing
        if ($nsNode instanceof Namespace_) {
            if (! $this->hasUseForFqcn($nsNode, $fqcn)) {
                array_unshift($nsNode->stmts, new Use_([new UseUse(new FullyQualified($fqcn))]));
            }
        }
    }

    /**
     * Resolve Name/Identifier/Relative/FullyQualified to a normalized FQCN string (no leading slash).
     */
    private function resolveFqcn(Node $nameLikeNode, Node $contextNode): string
    {
        // FullyQualified: just trim leading backslash
        if ($nameLikeNode instanceof FullyQualified) {
            return ltrim($nameLikeNode->toString(), '\\');
        }

        // Relative (e.g. namespace-relative starting with namespace\)
        if ($nameLikeNode instanceof Relative) {
            return ltrim($nameLikeNode->toString(), '\\');
        }

        // Identifier: bare name - resolve using current namespace if present
        if ($nameLikeNode instanceof Identifier) {
            $bare = $nameLikeNode->toString();
            $ns = $this->betterNodeFinder->findParentType($contextNode, Namespace_::class);
            if ($ns instanceof Namespace_ && $ns->name !== null) {
                return $ns->name->toString() . '\\' . $bare;
            }
            return $bare;
        }

        // Name (unqualified or qualified)
        if ($nameLikeNode instanceof Name) {
            // isFullyQualified returns true if node starts with "\" in source
            if ($nameLikeNode->isFullyQualified()) {
                return ltrim($nameLikeNode->toString(), '\\');
            }

            // Qualified like A\B (no leading slash) — resolve relative to namespace if present
            $ns = $this->betterNodeFinder->findParentType($contextNode, Namespace_::class);
            if ($ns instanceof Namespace_ && $ns->name !== null) {
                // If the Name already contains the current namespace as prefix, avoid doubling
                $candidate = $ns->name->toString() . '\\' . $nameLikeNode->toString();
                return ltrim($candidate, '\\');
            }

            // Global unqualified name
            return $nameLikeNode->toString();
        }

        // fallback
        return (string) $nameLikeNode->toString();
    }

    private function extractShortNameFromNode(Node $node): string
    {
        if ($node instanceof Identifier) {
            return $node->toString();
        }
        if ($node instanceof Name) {
            $parts = $node->parts;
            return (string) end($parts);
        }
        return '';
    }

    private function hasUseForFqcn(Namespace_ $nsNode, string $fqcn): bool
    {
        foreach ($nsNode->stmts as $stmt) {
            if (! $stmt instanceof Use_) {
                continue;
            }
            foreach ($stmt->uses as $use) {
                if ($use->name->toString() === $fqcn) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Simple conflict detection:
     * - if a different use has the same short name
     * - or a class/interface/trait with that short name is declared in the same namespace
     */
    private function shortNameConflicts(Namespace_ $nsNode, string $shortName, string $fqcn): bool
    {
        foreach ($nsNode->stmts as $stmt) {
            if ($stmt instanceof Use_) {
                foreach ($stmt->uses as $use) {
                    if ($use->name->getLast() === $shortName && $use->name->toString() !== $fqcn) {
                        return true;
                    }
                }
            }
            if ($stmt instanceof Class_ || $stmt instanceof Interface_) {
                $declared = $stmt->name instanceof Identifier ? $stmt->name->toString() : '';
                if ($declared === $shortName) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Extract all relevant nodes that can contain class names, returning [nodePart, setter].
     *
     * @return array<array{0: Node,1: callable}>
     */
    private function extractTypeNodes(Node $node): array
    {
        $result = [];

        if ($node instanceof Param || $node instanceof PropertyProperty) {
            if ($node->type !== null) {
                $result[] = [$node->type, fn($v) => $node->type = $v];
            }
            return $result;
        }

        if ($node instanceof FunctionLike) {
            if ($node->getReturnType() !== null) {
                $result[] = [$node->getReturnType(), fn($v) => $node->returnType = $v];
            }
            return $result;
        }

        if ($node instanceof Class_) {
            if ($node->extends !== null) {
                $result[] = [$node->extends, fn($v) => $node->extends = $v];
            }
            foreach ($node->implements as $i => $impl) {
                $result[] = [$impl, fn($v) => $node->implements[$i] = $v];
            }
            return $result;
        }

        if ($node instanceof Interface_) {
            foreach ($node->extends as $i => $ext) {
                $result[] = [$ext, fn($v) => $node->extends[$i] = $v];
            }
            return $result;
        }

        if ($node instanceof TraitUse) {
            foreach ($node->traits as $i => $t) {
                $result[] = [$t, fn($v) => $node->traits[$i] = $v];
            }
            return $result;
        }

        if ($node instanceof New_ || $node instanceof StaticCall || $node instanceof ClassConstFetch || $node instanceof Instanceof_) {
            // Some of these nodes store a Name|FullyQualified|Identifier in ->class
            if (property_exists($node, 'class') && $node->class !== null) {
                $result[] = [$node->class, fn($v) => $node->class = $v];
            }
            return $result;
        }

        return $result;
    }
}
