<?php
namespace kv6002;

use util\Util;
use database\MySQLConnectionInfo;

/**
 * The project configuration.
 * 
 * @author William Taylor (19009576)
 */
class Config {
    private $config;

    /**
     * Construct the configuration.
     * 
     * @param string $appRoot The root directory of this app. The best way of
     *   getting this is to create the Config in a file in the application root
     *   and pass in `__dir__`.
     * @param array<string,string> $config (Optional) A map that will be merged
     *   into the config to override the defaults.
     */
    public function __construct($appRoot, $config = null) {
        // You can override the configuration on a per-script basis, but be
        // careful you know what you're doing!
        if (!isset($config)) $config = [];

        $serverRoot = realpath($_SERVER['DOCUMENT_ROOT']);
        $appRoot = realpath($appRoot);
        $appServerPrefix = substr($appRoot, strlen($serverRoot));

        $this->config = Util::mergeAssociativeArrays([
            /* General
            ------------------ */

            'development' => true,
            'logfile' => "/log/error.log",

            /* Web Server
            ------------------ */

            // Based on: https://stackoverflow.com/q/4503135
            // WARNING: I don't know if this is fully correct
            'server_protocol' => (
                (
                    !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
                    $_SERVER['SERVER_PORT'] === 443
                ) ?
                "https" :
                "http"
            ),
            'server_host' => $_SERVER['HTTP_HOST'],
            'server_root' => $serverRoot,

            'application_root' => $appRoot,
            'application_server_prefix' => $appServerPrefix,

            /* Database
            ------------------ */

            'database_info' => new MySQLConnectionInfo(
                "localhost", "dvsc", "dvsc", "n;Qe2h7w-4@E4#.m<)e<"
            ),

            /* Auth Tokens
            ------------------ */

            'jwt_secret' => "Fnbz6AZhU3HHAHaSLQSxtXK6HA8edu4q"
        ], $config);
    }

    /**
     * Retrieve the given configuration setting.
     * 
     * @param string $name The name of the configuration setting to retrieve.
     * @return mixed The configuration setting's value.
     */
    public function get($name) {
        return $this->config[$name];
    }
}
