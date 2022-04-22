/**
 * Class for HTTPError objects.
 * 
 * @class
 * 
 * @author William Taylor (19009576)
 */
export class HTTPError {
    /**
     * Initialise the error's data.
     * 
     * @param {number} code The error code.
     * @param {*} semantics The meaning of the error code.
     * @param {*} explanation A user-friendly explanation of the error.
     * @param {*} reason A developer-friendly (including API developers) reason.
     */
    constructor(code, semantics, explanation, reason) {
        /**
         * The error code.
         * @type {number}
         */
        this.code = code;

        /**
         * The meaning of the error code.
         * @type {string}
         */
        this.semantics = semantics;

        /**
         * A user-friendly explanation of the error.
         * @type {string}
         */
        this.explanation = explanation;

        /**
         * A developer-friendly (including API developers) reason.
         * @type {string}
         */
        this.reason = reason;
    }
}

/**
 * Handler function that raises an Error for any error in the response of
 * fetch(). Useful to use as the 'resolve' callback for the first .then() in the
 * chain to convert 'soft' errors into exceptions.
 * 
 * @param {Response} response The response from the fetch() call.
 * @returns {Response} The given response (or a new Promise containing the given
 * response if used as a .then() callback).
 * 
 * @throws {Response} The response if it was not successful.
 * 
 * @author William Taylor (19009576)
 */
export function raiseFetchErrors(response) {
    if (!response.ok) {
        throw response;
    }
    return response;
}

/**
 * A simplification of the fetch() method that only takes the four most
 * commonly-used parameters. Their order is given so as to read well, eg.
 *     defaultFetch("POST", "https://yourdomain.com/forward",
 *       {"Content-Type": "application/json"}, "{message: 'hello'}")
 *  -> "Post to yourdomain-dot-com, 'forward' (where the content-type I'm
 *      sending is JSON) the message 'hello'."
 * 
 * Always throws an exception when a HTTP response is an error.
 * 
 * All parameters are passed directly to fetch where they are given. If not
 * given (or null), parameters are either eliminated from the fetch call,
 * or, in the case of mandatory parameters, are given a sensible default.
 * These defaults are as follows:
 *   method: "GET"
 * 
 * @param {String} method Any HTTP method supported by fetch()
 * @param {String} path The URL to fetch from
 * @param {Object} headers An object containing HTTP headers in the format
 * supported by fetch()
 * @param {String} body The body of the request
 * 
 * @author William Taylor (19009576)
 */
export function defaultFetch(method, path, headers, body) {
    const options = [
        method == null ? {method: "GET"} : {method: method},
        headers == null ? {} : {headers: headers},
        body == null ? {} : {body: body}
    ]

    return fetch(path, Object.assign({}, ...options))
        .then(raiseFetchErrors);
}

/**
 * Call defaultFetch(), automatically converting the response to JSON.
 * 
 * @param {String} method Any HTTP method supported by fetch()
 * @param {String} path The URL to fetch from
 * @param {Object} headers An object containing HTTP headers in the format
 * supported by fetch()
 * @param {String} body The body of the request
 * 
 * @author William Taylor (19009576)
 */
export function fetchJSON(method, path, headers, body) {
    return defaultFetch(method, path, headers, body)
        .then((response) => {
            if (response.status === 204) {
                return null; // No Content as an object is null
            }
            return response.json();
        })
        .catch((response) => response.json()
            .then((error) => {
                let {code, semantics, explanation, reason} = error;
                throw new HTTPError(code, semantics, explanation, reason);
            })
        );
}

const fetchHelpers = Object.freeze({
    raiseFetchErrors,
    defaultFetch,
    fetchJSON
});
export default fetchHelpers;
