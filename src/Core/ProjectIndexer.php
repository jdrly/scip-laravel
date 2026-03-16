<?php

declare(strict_types=1);

namespace ScipLaravel\Core;

use ScipLaravel\Cli\ApplicationFactory;
use ScipLaravel\Php\AstTraverser;
use ScipLaravel\Php\Parser;
use ScipLaravel\Php\TypeResolver;
use ScipLaravel\Project\ComposerProjectReader;
use ScipLaravel\Project\ProjectFileFinder;
use ScipLaravel\Scip\Index;
use ScipLaravel\Scip\Metadata;
use ScipLaravel\Scip\SymbolNamer;

use function str_replace;

final class ProjectIndexer
{
    public function __construct(
        private readonly ComposerProjectReader $projectReader = new ComposerProjectReader(),
        private readonly ProjectFileFinder $fileFinder = new ProjectFileFinder(),
        private readonly Parser $parser = new Parser(),
        private readonly AstTraverser $traverser = new AstTraverser(),
        private readonly SymbolNamer $symbolNamer = new SymbolNamer(),
    ) {
    }

    public function index(string $projectRoot): Index
    {
        $project = $this->projectReader->read($projectRoot);
        $projectVersion = 'dev';
        $documents = [];

        foreach ($this->fileFinder->phpFiles($project) as $absolutePath) {
            $documentIndexer = new DocumentIndexer(
                traverser: $this->traverser,
                symbolNamer: $this->symbolNamer,
                typeResolver: new TypeResolver(),
                project: $project,
                projectVersion: $projectVersion,
            );
            $documents[] = $documentIndexer->index(
                absolutePath: $absolutePath,
                relativePath: str_replace($project->rootPath . '/', '', $absolutePath),
                statements: $this->parser->parseFile($absolutePath),
            );
        }

        return new Index(
            metadata: new Metadata(
                projectRoot: 'file://' . $project->rootPath,
                toolName: 'scip-laravel',
                toolVersion: ApplicationFactory::VERSION,
            ),
            documents: $documents,
        );
    }
}
