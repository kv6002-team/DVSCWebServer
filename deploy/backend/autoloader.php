<?php
/**
 * Registers this project's autoloaders.
 */

// Ref: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md

// Use anonymous functions to avoid defining names that shouldn't be used
// outside of this context.
spl_autoload_register(function ($className) {
    $toPath = function ($nsPath) {
        return str_replace("\\", DIRECTORY_SEPARATOR, $nsPath);
    };

    $prefixMap = [
        "kv6002\\" => "src", # \kv6002\... maps to src/...
        "" => "lib"        # Everything else maps to lib/...
    ];

    foreach ($prefixMap as $classPrefix => $baseDir) {
        $classPrefixLen = strlen($classPrefix);

        if (strncmp($classPrefix, $className, $classPrefixLen) === 0) {
            $classSuffix = substr($className, $classPrefixLen);
            $path = $toPath("$baseDir\\$classSuffix.php");

            // "MUST NOT raise errors of any level" - PSR-4
            // See PSR-4 Meta Document for details:
            //   https://www.php-fig.org/psr/psr-4/meta/
            if (is_readable($path)) {
                require $path;
                break; // Stop at first matching prefix with readable file
            }
        }
    }
});

spl_autoload_register(require(__DIR__ . "/vendor/autoload.php"));
