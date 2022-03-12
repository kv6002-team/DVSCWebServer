<?php
/**
 * Define functions for error and exception handling, and a function to set
 * these as the PHP process-wide error/exception handlers.
 */

/* I know 'panic' is not PHP terminology (more Go/Rust), but:
 * - It is funny.
 * - If you get an uncaught exception in production, that's a good reason to
 *   panic if you are the back-end developer.
 * - It is noticable to "require panic" in your index file. Errors happen.
 */

/**
 * The basic error handler.
 * 
 * Convert errors, warnings, and notices that are enabled
 * ({@see \error_reporting()}) into ErrorExceptions.
 */
function basicErrorHandler($errno, $errstr, $errfile, $errline) {
    // Implementation from:
    //   https://www.php.net/manual/en/class.errorexception.php

    // Check error isn’t excluded by server settings
    if(!(error_reporting() & $errno)) { 
        return;
    }
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
};

/**
 * The basic exception handler.
 * 
 * This handler is used as a last resort if an error occurs:
 * - Really early (such as during includes/requires)
 * - During creation or setting of a more user-friendly error handler
 * - While handling another error
 */
function basicExceptionHandler($exception) {
    http_response_code(500);
    header("Content-Type: text/plain");

    $lines = [
        "500 Internal Server Error",
        "An unknown error occured, sorry.",
        "",
        "You can try:",
        "- Refreshing the page (for normal page requests) / resubmitting the request (for API requests)",
        "- Waiting a few minutes then refreshing the page",
        "- Contacting us about the issue - we'll try to resolve it for you."
    ];
    array_push($lines, strval($exception));
    printf(str_repeat("%s\n", count($lines)), ...$lines);

    die(1);
};

/**
 * Register the basic error and exception handlers defined in this file as the
 * PHP process-wide error and exception handlers.
 */
function registerPanic() {
    set_error_handler("basicErrorHandler");
    set_exception_handler("basicExceptionHandler");    
}
