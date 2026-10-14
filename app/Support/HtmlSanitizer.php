<?php

namespace App\Support;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Enumerable;

class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'a', 'h2', 'h3', 'h4', 'blockquote', 'span',
    ];

    private const REMOVE_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template', 'form',
    ];

    public static function clean(string $html): string
    {
        if (! str_contains($html, '<')) {
            return $html;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="sanitized-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('sanitized-root');
        if (! $root) {
            return '';
        }

        self::cleanChildren($root);

        return collect(iterator_to_array($root->childNodes))
            ->map(fn (DOMNode $node): string => $document->saveHTML($node))
            ->implode('');
    }

    public static function cleanMixed(mixed $value): mixed
    {
        if ($value instanceof Enumerable) {
            return self::cleanMixed($value->all());
        }

        if (is_string($value)) {
            return self::clean($value);
        }

        if (is_array($value)) {
            return array_map(self::cleanMixed(...), $value);
        }

        return $value;
    }

    private static function cleanChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMComment) {
                $parent->removeChild($node);

                continue;
            }

            if (! $node instanceof DOMElement) {
                continue;
            }

            self::cleanChildren($node);

            if (! in_array(strtolower($node->tagName), self::ALLOWED_TAGS, true)) {
                if (in_array(strtolower($node->tagName), self::REMOVE_WITH_CONTENT, true)) {
                    $parent->removeChild($node);

                    continue;
                }

                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);

                continue;
            }

            self::cleanAttributes($node);
        }
    }

    private static function cleanAttributes(DOMElement $element): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);
            $allowed = $name === 'class' || ($element->tagName === 'a' && in_array($name, ['href', 'title', 'target', 'rel'], true));

            if (! $allowed) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($element->tagName !== 'a') {
            return;
        }

        $href = trim($element->getAttribute('href'));
        if ($href !== '' && ! preg_match('#^(https?://|mailto:|tel:|/|\#)#i', $href)) {
            $element->removeAttribute('href');
        }

        if ($element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        } else {
            $element->removeAttribute('target');
        }
    }
}
