<?php

declare(strict_types=1);

namespace Tests\Unit\Scip;

use PHPUnit\Framework\TestCase;
use Scip\Document as ProtoDocument;
use Scip\Index as ProtoIndex;
use ScipLaravel\Core\ProjectIndexer;
use ScipLaravel\Scip\ProtoIndexEncoder;
use Tests\Support\FixturePaths;

final class ProtoIndexEncoderTest extends TestCase
{
    public function testEncodeProducesReadableScipPayload(): void
    {
        $index = (new ProjectIndexer())->index(FixturePaths::fixture('plain-php-modern'));
        $payload = (new ProtoIndexEncoder())->encode($index);

        $decodedIndex = new ProtoIndex();
        $decodedIndex->mergeFromString($payload);

        $metadata = $decodedIndex->getMetadata();
        self::assertNotNull($metadata);
        $toolInfo = $metadata->getToolInfo();
        self::assertNotNull($toolInfo);

        /** @var iterable<int, ProtoDocument> $protoDocuments */
        $protoDocuments = $decodedIndex->getDocuments();
        $documents = [];
        foreach ($protoDocuments as $document) {
            if ($document instanceof ProtoDocument) {
                $documents[] = $document;
            }
        }

        self::assertSame('file://' . FixturePaths::fixture('plain-php-modern'), $metadata->getProjectRoot());
        self::assertSame('scip-laravel', $toolInfo->getName());
        self::assertCount(3, $documents);
        self::assertSame('src/ExampleClass.php', $documents[0]->getRelativePath());
    }
}
