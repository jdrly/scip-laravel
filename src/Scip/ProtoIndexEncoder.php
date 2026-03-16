<?php

declare(strict_types=1);

namespace ScipLaravel\Scip;

use Scip\Document as ProtoDocument;
use Scip\Index as ProtoIndex;
use Scip\Language;
use Scip\Metadata as ProtoMetadata;
use Scip\Occurrence as ProtoOccurrence;
use Scip\PositionEncoding;
use Scip\ProtocolVersion;
use Scip\SymbolInformation as ProtoSymbolInformation;
use Scip\SymbolInformation\Kind;
use Scip\SymbolRole;
use Scip\SyntaxKind;
use Scip\TextEncoding;
use Scip\ToolInfo;

use function array_map;
use function basename;
use function explode;
use function ltrim;
use function rtrim;
use function str_contains;
use function str_replace;
use function strrpos;
use function substr;

final class ProtoIndexEncoder
{
    public function encode(Index $index): string
    {
        $protoIndex = new ProtoIndex([
            'metadata' => new ProtoMetadata([
                'version' => ProtocolVersion::UnspecifiedProtocolVersion,
                'tool_info' => new ToolInfo([
                    'name' => $index->metadata->toolName,
                    'version' => $index->metadata->toolVersion,
                    'arguments' => [],
                ]),
                'project_root' => $index->metadata->projectRoot,
                'text_document_encoding' => TextEncoding::UTF8,
            ]),
            'documents' => array_map(
                fn(Document $document): ProtoDocument => $this->document($document),
                $index->documents,
            ),
            'external_symbols' => [],
        ]);

        return $protoIndex->serializeToString();
    }

    private function document(Document $document): ProtoDocument
    {
        return new ProtoDocument([
            'language' => Language::PHP,
            'relative_path' => $document->relativePath,
            'occurrences' => array_map(
                fn(Occurrence $occurrence): ProtoOccurrence => $this->occurrence($occurrence),
                $document->occurrences,
            ),
            'symbols' => array_map(
                fn(SymbolInformation $symbolInformation): ProtoSymbolInformation => $this->symbolInformation(
                    $symbolInformation,
                ),
                $document->symbols,
            ),
            'position_encoding' => PositionEncoding::UTF8CodeUnitOffsetFromLineStart,
        ]);
    }

    private function occurrence(Occurrence $occurrence): ProtoOccurrence
    {
        return new ProtoOccurrence([
            'range' => $occurrence->range,
            'symbol' => $occurrence->symbol,
            'symbol_roles' => $occurrence->role === 'definition'
                ? SymbolRole::Definition
                : SymbolRole::UnspecifiedSymbolRole,
            'syntax_kind' => $this->syntaxKind($occurrence->syntaxKind, $occurrence->role),
        ]);
    }

    private function symbolInformation(SymbolInformation $symbolInformation): ProtoSymbolInformation
    {
        return new ProtoSymbolInformation([
            'symbol' => $symbolInformation->symbol,
            'documentation' => [],
            'relationships' => [],
            'kind' => $this->symbolKind($symbolInformation->kind),
            'display_name' => $this->displayName($symbolInformation->symbol),
            'enclosing_symbol' => $this->enclosingSymbol($symbolInformation->symbol),
        ]);
    }

    private function symbolKind(string $kind): int
    {
        return match ($kind) {
            'class' => Kind::PBClass,
            'function' => Kind::PBFunction,
            'method' => Kind::Method,
            'property' => Kind::Property,
            default => Kind::UnspecifiedKind,
        };
    }

    private function syntaxKind(string $syntaxKind, string $role): int
    {
        return match ($syntaxKind) {
            'function' => $role === 'definition'
                ? SyntaxKind::IdentifierFunctionDefinition
                : SyntaxKind::IdentifierFunction,
            'method' => SyntaxKind::IdentifierFunction,
            'property' => SyntaxKind::Identifier,
            'string' => SyntaxKind::StringLiteral,
            'type' => SyntaxKind::IdentifierType,
            default => SyntaxKind::Identifier,
        };
    }

    private function displayName(string $symbol): string
    {
        $descriptor = $this->descriptor($symbol);
        $memberPosition = strrpos($descriptor, '#');
        if ($memberPosition === false) {
            return basename(str_replace('\\', '/', rtrim($descriptor, '.')));
        }

        $memberDescriptor = substr($descriptor, $memberPosition + 1);
        if ($memberDescriptor === '') {
            return basename(str_replace('\\', '/', substr($descriptor, 0, $memberPosition)));
        }

        $memberDescriptor = ltrim($memberDescriptor, '$');
        $memberDescriptor = rtrim($memberDescriptor, '.');
        $memberDescriptor = rtrim($memberDescriptor, '()');

        return $memberDescriptor;
    }

    private function enclosingSymbol(string $symbol): string
    {
        $descriptor = $this->descriptor($symbol);
        $memberPosition = strrpos($descriptor, '#');
        if ($memberPosition === false) {
            return '';
        }

        $parts = explode(' ', $symbol);
        if (count($parts) !== 5) {
            return '';
        }

        return $parts[0]
            . ' ' . $parts[1]
            . ' ' . $parts[2]
            . ' ' . $parts[3]
            . ' ' . substr($descriptor, 0, $memberPosition)
            . '#';
    }

    private function descriptor(string $symbol): string
    {
        $parts = explode(' ', $symbol, 5);

        return $parts[4] ?? $symbol;
    }
}
