<?php

declare(strict_types=1);

namespace ScipLaravel\Laravel;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeVisitorAbstract;
use ScipLaravel\Php\AstTraverser;
use ScipLaravel\Php\Parser;
use ScipLaravel\Project\ProjectModel;
use ScipLaravel\Scip\SymbolNamer;

use function in_array;
use function is_file;
use function is_string;
use function scandir;

final class CommandRegistryBuilder
{
    public function __construct(
        private readonly Parser $parser,
        private readonly AstTraverser $traverser,
        private readonly SymbolNamer $symbolNamer,
    ) {
    }

    public function build(ProjectModel $projectModel, string $projectVersion): CommandRegistry
    {
        /** @var list<CommandDefinition> $definitions */
        $definitions = [];

        foreach ($projectModel->commandDirectories as $commandDirectory) {
            $absoluteDirectory = $projectModel->composerProject->rootPath . '/' . $commandDirectory;
            $entries = scandir($absoluteDirectory);
            if ($entries === false) {
                continue;
            }

            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $relativePath = $commandDirectory . '/' . $entry;
                $absolutePath = $projectModel->composerProject->rootPath . '/' . $relativePath;
                if (!is_file($absolutePath)) {
                    continue;
                }

                $visitor = new class (
                    $projectModel,
                    $projectVersion,
                    $relativePath,
                    $this->symbolNamer,
                ) extends NodeVisitorAbstract {
                    /** @var list<CommandDefinition> */
                    public array $definitions = [];

                    private ?string $currentClassName = null;

                    private ?string $currentClassSymbol = null;

                    public function __construct(
                        private readonly ProjectModel $projectModel,
                        private readonly string $projectVersion,
                        private readonly string $relativePath,
                        private readonly SymbolNamer $symbolNamer,
                    ) {
                    }

                    public function enterNode(Node $node): ?Node
                    {
                        if ($node instanceof ClassLike && $node->name !== null) {
                            $this->currentClassName = $node->namespacedName?->toString() ?? $node->name->toString();
                            $this->currentClassSymbol = $this->symbolNamer->classLike(
                                $this->projectModel->composerProject->packageName,
                                $this->projectVersion,
                                $this->currentClassName,
                            );
                            return null;
                        }

                        if (
                            !$node instanceof Property
                            || $this->currentClassName === null
                            || $this->currentClassSymbol === null
                        ) {
                            return null;
                        }

                        if ($node->props[0]->default instanceof String_) {
                            $propertyName = $node->props[0]->name->toString();
                            if (in_array($propertyName, ['signature', 'name'], true)) {
                                $this->definitions[] = new CommandDefinition(
                                    $this->relativePath,
                                    $this->currentClassName,
                                    $this->currentClassSymbol,
                                    $node->props[0]->default->value,
                                );
                            }
                        }

                        return null;
                    }

                    public function leaveNode(Node $node): ?Node
                    {
                        if ($node instanceof ClassLike) {
                            $this->currentClassName = null;
                            $this->currentClassSymbol = null;
                        }

                        return null;
                    }
                };

                $this->traverser->traverse($this->parser->parseFile($absolutePath), $visitor);
                foreach ($visitor->definitions as $definition) {
                    $definitions[] = $definition;
                }
            }
        }

        return new CommandRegistry($definitions);
    }
}
