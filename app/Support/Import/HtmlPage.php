<?php

namespace App\Support\Import;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

/** Small DOM/XPath wrapper for reading the old ASP.NET pages. */
class HtmlPage
{
    public readonly DOMXPath $xpath;

    public function __construct(public readonly string $html, public readonly string $url = '')
    {
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        // The XML prolog makes libxml read the page as UTF-8 (Arabic text).
        $doc->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        $this->xpath = new DOMXPath($doc);
    }

    /** @return list<DOMElement> */
    public function all(string $xpath, ?DOMNode $context = null): array
    {
        return array_values(array_filter(iterator_to_array($this->xpath->query($xpath, $context) ?: []), fn ($n) => $n instanceof DOMElement));
    }

    public function first(string $xpath, ?DOMNode $context = null): ?DOMElement
    {
        return $this->all($xpath, $context)[0] ?? null;
    }

    public function text(string $xpath, ?DOMNode $context = null): ?string
    {
        $node = $this->first($xpath, $context);

        return $node ? self::clean($node->textContent) : null;
    }

    public static function clean(?string $text): ?string
    {
        $text = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', (string) $text));

        return $text === '' ? null : $text;
    }

    public static function innerHtml(DOMNode $node): string
    {
        return implode('', array_map(fn ($child) => $node->ownerDocument->saveHTML($child), iterator_to_array($node->childNodes)));
    }

    /** XPath predicate for elements carrying a CSS class. */
    public static function hasClass(string $class): string
    {
        return "contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')";
    }

    /** Resolve a link from this page to an absolute URL. */
    public function absolute(?string $href): ?string
    {
        $href = trim((string) $href);

        if ($href === '' || str_starts_with($href, 'javascript:') || str_starts_with($href, '#')) {
            return null;
        }

        if (preg_match('~^https?://~i', $href)) {
            return $href;
        }

        // ASP.NET app-relative links ("~/images/x.pdf").
        $href = preg_replace('#^~/?#', '', $href);
        $parts = parse_url($this->url);
        $origin = $parts['scheme'].'://'.$parts['host'];

        if (str_starts_with($href, '/')) {
            return $origin.$href;
        }

        $dir = rtrim(preg_replace('~/[^/]*$~', '', $parts['path'] ?? '/'), '/');

        return $origin.$dir.'/'.$href;
    }
}
