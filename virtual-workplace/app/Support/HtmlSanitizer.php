<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allowlist sanitizer for rich-text documents. Keeps a small set of
 * formatting tags, drops everything else (scripts, styles, event handlers,
 * iframes…) while keeping their text, and only lets links point at
 * http(s)/mailto. Run on every write so stored HTML is safe to render.
 */
class HtmlSanitizer
{
    /** tag => allowed attributes */
    private const ALLOWED = [
        'p' => ['dir'], 'br' => [], 'h2' => ['dir'], 'h3' => ['dir'], 'h4' => ['dir'],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'ul' => ['dir'], 'ol' => ['dir'], 'li' => ['dir'], 'blockquote' => ['dir'],
        'pre' => ['dir'], 'code' => [], 'a' => ['href'], 'hr' => [], 'div' => ['dir'],
    ];

    /** Removed together with their contents. */
    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'template', 'noscript', 'svg', 'math', 'form', 'textarea', 'select', 'button', 'input', 'link', 'meta', 'title', 'head'];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $doc->getElementsByTagName('body')->item(0);
        if (! $body) {
            return '';
        }
        self::walk($body);

        $out = '';
        foreach ($body->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    /** Plain text / markdown-ish legacy content → paragraphs (escaped). */
    public static function fromPlainText(string $text): string
    {
        $paragraphs = preg_split("/\R{2,}/", trim($text)) ?: [];

        return implode('', array_map(
            fn ($p) => '<p dir="auto">'.nl2br(e($p), false).'</p>',
            array_filter($paragraphs, fn ($p) => trim($p) !== '')
        ));
    }

    /** Render stored content: HTML is already clean; older plain text gets paragraphs. */
    public static function forDisplay(?string $content): string
    {
        $content = (string) $content;
        if ($content === '') {
            return '';
        }

        return preg_match('/<\s*(p|h[2-4]|ul|ol|blockquote|pre|div|br|strong|em)\b/i', $content)
            ? self::clean($content)
            : self::fromPlainText($content);
    }

    private static function walk(DOMNode $node): void
    {
        // Iterate over a static copy: children are replaced/removed as we go.
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                $node->removeChild($child);

                continue;
            }
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $node->removeChild($child);

                continue;
            }

            self::walk($child);

            if (! array_key_exists($tag, self::ALLOWED)) {
                // Unknown wrapper (span, font, table…): keep its text, lose the tag.
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attr) {
                $name = strtolower($attr->name);
                if (! in_array($name, self::ALLOWED[$tag], true)) {
                    $child->removeAttribute($attr->name);
                }
            }

            if ($tag === 'a') {
                $href = trim($child->getAttribute('href'));
                if (! preg_match('#^(https?://|mailto:)#i', $href)) {
                    $child->removeAttribute('href');
                } else {
                    $child->setAttribute('rel', 'noopener noreferrer nofollow');
                    $child->setAttribute('target', '_blank');
                }
            }
            if ($child->hasAttribute('dir') && ! in_array($child->getAttribute('dir'), ['rtl', 'ltr', 'auto'], true)) {
                $child->removeAttribute('dir');
            }
        }
    }
}
