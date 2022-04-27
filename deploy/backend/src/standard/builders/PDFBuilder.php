<?php
namespace kv6002\standard\builders;

// Composer
use Spipu\Html2Pdf\Html2Pdf;

// Project
use router\ErroredRequest;
use router\Response;
use router\resource\ContentTypeSelector;
use kv6002\standard\Page;

class PDFResponse extends Response {
    private $filename;

    /**
     * Create a possible response.
     * 
     * @param int $status The HTTP status code for the response.
     * @param array<string,string> $headers A list of header names and values to
     *   include.
     * @param string $content A HTML string to generate the PDF from.
     * @param string $filename The file name for the returned PDF.
     * 
     * @see addHeader() to add multiple headers with the same name.
     */
    public function __construct($status, $headers, $content, $filename) {
        parent::__construct($status, $headers, $content);
        $this->filename = $filename;
    }

    public function send() {
        // Largely a copy of Response
        http_response_code($this->status());
        foreach ($this->headers() as $header) {
            header($header);
        }

        // The PDF-specific bit
        $html2pdf = new Html2Pdf();
        $html2pdf->writeHTML($this->body());
        $html2pdf->output($this->filename);
    }
}

/**
 * A Builder for generating PDFs.
 */
class PDFBuilder implements Builder {
    private $builderFn;
    private $pathfinder;

    /**
     * Make a HTMLBuilder.
     * 
     * @param RequestHandler $builderFn A request handler that returns a HTML
     *   string.
     */
    public function __construct($builderFn) {
        $this->builderFn = $builderFn;
    }

    /**
     * Crete a standard page, allow the builder function to construct the page's
     * body, then construct a response from the return value.
     * 
     * The status code is determined by the state of the request.
     * 
     * @param Request $request The HTTP request to handle, or whose processing
     *   caused the error to handle (may be an ErroredRequest).
     * @param mixed $args Any other data to pass down to the handler.
     * @return Response The response (valid or error) to return to the client.
     */
    public function __invoke($request, ...$args) {
        $builderFn = $this->builderFn; // PHP <=5.6
        list($content, $filename) = $builderFn($request, ...$args);

        // Get the expected response code after the builder is called so that
        // we respect what the builder sets it to (if possible for that kind of
        // request object).
        $code = $request->expectedResponseStatusCode();

        return new PDFResponse(
            $code,
            ["Content-Type" => "application/pdf"],
            $content,
            $filename
        );
    }

    /* Static Factory
    -------------------------------------------------- */

    /**
     * A utility method to create a ContentTypeSelector that only supports HTML.
     * 
     * @param RequestHandler $builderFn A request handler that returns a HTML
     *   string.
     * 
     * @return ContentTypeSelector A ContentTypeSelector that only supports
     *   HTML.
     */
    public static function typeSelector($builderFn) {
        return new ContentTypeSelector([
            "application/pdf" => new self($builderFn)
        ], "application/pdf");
    }
}
