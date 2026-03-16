<?php

declare(strict_types=1);

namespace ScipLaravel\Core;

use PhpParser\Node\Stmt;
use ScipLaravel\Cli\ApplicationFactory;
use ScipLaravel\Laravel\RouteAnalyzer;
use ScipLaravel\Php\AstTraverser;
use ScipLaravel\Php\Parser;
use ScipLaravel\Php\TypeResolver;
use ScipLaravel\Project\ProjectFileFinder;
use ScipLaravel\Project\ProjectModel;
use ScipLaravel\Project\ProjectModelDetector;
use ScipLaravel\Scip\Document;
use ScipLaravel\Scip\Index;
use ScipLaravel\Scip\Metadata;
use ScipLaravel\Scip\Occurrence;
use ScipLaravel\Scip\SymbolNamer;

use function in_array;
use function str_replace;

final class ProjectIndexer
{
    public function __construct(
        private readonly ProjectModelDetector $projectModelDetector = new ProjectModelDetector(),
        private readonly ProjectFileFinder $fileFinder = new ProjectFileFinder(),
        private readonly Parser $parser = new Parser(),
        private readonly AstTraverser $traverser = new AstTraverser(),
        private readonly SymbolNamer $symbolNamer = new SymbolNamer(),
    ) {
    }

    public function index(string $projectRoot): Index
    {
        $projectModel = $this->projectModelDetector->detect($projectRoot);
        $project = $projectModel->composerProject;
        $projectVersion = 'dev';
        $documents = [];

        foreach ($this->fileFinder->phpFiles($project) as $absolutePath) {
            $relativePath = str_replace($project->rootPath . '/', '', $absolutePath);
            $statements = $this->parser->parseFile($absolutePath);

            $documentIndexer = new DocumentIndexer(
                traverser: $this->traverser,
                symbolNamer: $this->symbolNamer,
                typeResolver: new TypeResolver(),
                project: $project,
                projectVersion: $projectVersion,
            );

            $document = $documentIndexer->index(
                absolutePath: $absolutePath,
                relativePath: $relativePath,
                statements: $statements,
            );

            $documents[] = $this->augmentLaravelRouteDocument(
                $document,
                $projectModel,
                $projectVersion,
                $absolutePath,
                $relativePath,
                $statements,
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

    /** @param list<Stmt> $statements */
    private function augmentLaravelRouteDocument(
        Document $document,
        ProjectModel $projectModel,
        string $projectVersion,
        string $absolutePath,
        string $relativePath,
        array $statements,
    ): Document {
        if (
            $projectModel->framework !== 'laravel'
            || !in_array($relativePath, $projectModel->routeFiles, true)
        ) {
            return $document;
        }

        $routeOccurrences = (new RouteAnalyzer(
            traverser: $this->traverser,
            symbolNamer: $this->symbolNamer,
            projectModel: $projectModel,
            projectVersion: $projectVersion,
        ))->occurrences($absolutePath, $statements);

        if ($routeOccurrences === []) {
            return $document;
        }

        /** @var list<Occurrence> $occurrences */
        $occurrences = [...$document->occurrences, ...$routeOccurrences];

        return new Document(
            relativePath: $document->relativePath,
            occurrences: $occurrences,
            symbols: $document->symbols,
        );
    }
}
