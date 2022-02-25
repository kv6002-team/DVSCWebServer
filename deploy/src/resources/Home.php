<?php
namespace kv6002\resources;

use html\HTML;
use dispatcher\Dispatcher;
use router\resource\BasicResource;
use kv6002\standard\builders\HTMLBuilder;

/**
 * The Home page. Provides basic information about the site and allows
 * navigation to the documentation page.
 * 
 * @author William Taylor (19009576)
 */
class Home extends BasicResource {
    public function __construct($pathfinder) {
        $header = HTML::h(1,
            "Donaldsons' Vehicle Specialist Consultancy (DVSC)"
        );
        $content = null;

        parent::__construct([
            "GET" => HTMLBuilder::typeSelector(
                function ($request) use ($content, $header) {
                    return [
                        "header" => $header,
                        "content" => $content
                    ];
                },
                $pathfinder
            )
        ]);
    }
}
