<?php

namespace App\Support\Import;

use Closure;
use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Turns Word-pasted HTML from the old sites into clean rich-text content:
 * keeps structure (paragraphs, headings, lists, tables, links, images), drops styles, fonts, spans and empty paragraphs.
 * Images and document links go through $localize so they can be copied off the old site before it is switched off.
 */
class ContentCleaner
{
    private const KEEP = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'a', 'img'];

    private const DROP = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'select', 'textarea', 'o:p', 'meta', 'link'];

    private const RENAME = ['b' => 'strong', 'i' => 'em', 'h1' => 'h2', 'h5' => 'h4', 'h6' => 'h4'];

    /** @param  Closure(string $absoluteUrl): ?string|null  $localize  returns the new URL, or null to drop the element */
    public function __construct(private ?Closure $localize = null) {}

    public function clean(string $html, string $pageUrl): ?string
    {
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?><div id="__root">'.$html.'</div>', LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $root = $doc->getElementById('__root');
        $page = new HtmlPage('', $pageUrl);
        $this->walk($root, $page);

        $out = HtmlPage::innerHtml($root);
        $out = preg_replace('~<p>(?:\s|&nbsp;|\x{00A0}|<br\s*/?>)*</p>~u', '', $out);
        $out = preg_replace('~(\s|&nbsp;)+~u', ' ', $out);
        $out = trim(preg_replace('~>\s+<~', '><', $out));

        return $out === '' ? null : $out;
    }

    private function walk(DOMNode $node, HtmlPage $page): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $child->parentNode->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP, true)) {
                $child->parentNode->removeChild($child);

                continue;
            }

            $this->walk($child, $page);

            if (isset(self::RENAME[$tag])) {
                $child = $this->rename($child, self::RENAME[$tag]);
                $tag = self::RENAME[$tag];
            }

            if (! in_array($tag, self::KEEP, true)) {
                $this->unwrap($child);

                continue;
            }

            $this->keepAttributes($child, $tag, $page);
        }
    }

    private function keepAttributes(DOMElement $el, string $tag, HtmlPage $page): void
    {
        $keep = match ($tag) {
            'a' => ['href'],
            'img' => ['src', 'alt'],
            'td', 'th' => ['colspan', 'rowspan'],
            default => [],
        };

        foreach (iterator_to_array($el->attributes) as $attr) {
            if (! in_array($attr->name, $keep, true)) {
                $el->removeAttribute($attr->name);
            }
        }

        $attribute = match ($tag) {
            'img' => 'src',
            'a' => 'href',
            default => null,
        };

        if (! $attribute) {
            return;
        }

        $url = $page->absolute($el->getAttribute($attribute));
        $isDocument = $tag === 'img' || preg_match('~\.(pdf|docx?|xlsx?|pptx?|jpe?g|png|gif|webp)(\?|$)~i', (string) $url);
        $local = ($url && $isDocument && $this->localize) ? ($this->localize)($url) : $url;

        if ($local === null) {
            $tag === 'img' ? $el->parentNode->removeChild($el) : $this->unwrap($el);

            return;
        }

        $el->setAttribute($attribute, $local);
    }

    private function unwrap(DOMElement $el): void
    {
        while ($el->firstChild) {
            $el->parentNode->insertBefore($el->firstChild, $el);
        }

        $el->parentNode->removeChild($el);
    }

    private function rename(DOMElement $el, string $tag): DOMElement
    {
        $new = $el->ownerDocument->createElement($tag);

        while ($el->firstChild) {
            $new->appendChild($el->firstChild);
        }

        $el->parentNode->replaceChild($new, $el);

        return $new;
    }
}
