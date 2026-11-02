<?php

// Rev. 3.1 (2025-08-18) 

// Load Markdown converter
require dirname(__DIR__) . '/vendor/autoload.php';

// Before v 3.0 
// use League\CommonMark\CommonMarkConverter;
//   or
// use League\CommonMark\GithubFlavoredMarkdownConverter;
//
// New config to manage footnotes
// Sources : 
//   - https://daext.com/blog/convert-markdown-to-html-with-php/
//   - https://commonmark.thephpleague.com/2.7/extensions/attributes/
//   - https://commonmark.thephpleague.com/2.7/extensions/footnotes/
// 
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;

use League\CommonMark\Extension\Table\TableExtension;

use League\CommonMark\MarkdownConverter;
// Define your configuration, if needed
// Extension defaults are shown below
// If you're happy with the defaults, feel free to remove them from this array
$config = [
    // Rev. 4.5.2: "javascript:", "vbscript:", "file:" and "data:" links not rendered
    'allow_unsafe_links' => false,
    'footnote' => [
        'backref_class'      => 'footnote-backref',
        'backref_symbol'     => '↩',
        'container_add_hr'   => true,
        'container_class'    => 'footnotes',
        'ref_class'          => 'footnote-ref',
        'ref_id_prefix'      => 'fnref:',
        'footnote_class'     => 'footnote',
        'footnote_id_prefix' => 'fn:',
    ],

    'table' => [
        'wrap' => [
            'enabled' => false,
            'tag' => 'div',
            'attributes' => [],
        ],
        'alignment_attributes' => [
            'left'   => ['align' => 'left'],
            'center' => ['align' => 'center'],
            'right'  => ['align' => 'right'],
        ],
    ],
];
// Configure the Environment with all the CommonMark parsers/renderers
$environment = new Environment($config);
$environment->addExtension(new CommonMarkCoreExtension());
// Add the extension
$environment->addExtension(new FootnoteExtension());
$environment->addExtension(new TableExtension());
// Instantiate the converter engine and start converting some Markdown!
$converter = new MarkdownConverter($environment);
// echo $converter->convert("This is a footnote[^test] test.\n\n[^test]: Doesn't it look good!");
// To convert :
// $output = $converter->convert($output);



