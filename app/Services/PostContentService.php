<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

final class PostContentService
{
    private HTMLPurifier $purifier;

    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
        $config->set('Output.Newline', "\n");
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^https://(?:www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
        $definition = $config->getHTMLDefinition(true);
        $definition->addElement('figure', 'Block', 'Flow', 'Common');
        $definition->addElement('figcaption', 'Block', 'Flow', 'Common');
        $this->purifier = new HTMLPurifier($config);
    }

    public function toHtml(?string $content): string
    {
        if ($content === null || $content === '') {
            return '';
        }

        // Legacy articles were plain text. Preserve their line breaks in the editor and public view.
        if (! preg_match('/<\/?[a-z][^>]*>/i', $content)) {
            $content = '<p>'.nl2br(e($content)).'</p>';
        }

        return $this->purifier->purify($content);
    }
}
