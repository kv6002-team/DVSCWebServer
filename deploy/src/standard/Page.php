<?php
namespace kv6002\standard;

use html\MutableElement;
use html\DocumentTemplate;
use html\HTML;
use util\Util;

/**
 * The page template for this project/assignment. All HTML Responses should use
 * this template.
 * 
 * @author William Taylor (19009576)
 */
class Page extends DocumentTemplate {
    function __construct($pathfinder) {
        $components = [
            "header" => new MutableElement(),
            "content" => new MutableElement()
        ];

        $nav = [
            "Home" => "/"
        ];

        parent::__construct(
            HTML::basicDocument(
                "Donaldsons' Vehicle Specialist Consultantcy (DVSC)",
                "We can assist you in running your garage business.",
                [
                    HTML::linkCSS(
                        $pathfinder->serverPathFor("/css/main.css", true)
                    )
                ],
                [
                    HTML::nav([
                        HTML::ul(Util::mapKeysValues(
                            $nav,
                            function ($name, $loc) use ($pathfinder) {
                                return HTML::a(
                                    $pathfinder->serverPathFor($loc),
                                    $name
                                );
                            }
                        ))
                    ]),
                    HTML::main([
                        HTML::header($components["header"]),
                        $components["content"]
                    ]),
                    HTML::footer([
                        HTML::p("Website designed and created by Capytech Ltd."),
                    ])
                ]
            ),
            $components
        );
    }
}
