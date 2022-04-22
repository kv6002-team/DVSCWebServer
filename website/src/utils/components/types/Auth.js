/**
 * @callback AuthSetTokenFn
 * @param {string} token The new token to use the auth provider that provided
 *   this auth object.
 */

/**
 * @callback AuthSetErrorFn
 * @param {object} error The object that was thrown as an exception to set as
 *   the auth error for the provider that provided this auth object.
 */

/**
 * @typedef {object} Auth
 * @prop {string|null} token The auth token, or null if the user is not authed.
 * @prop {object|null} error An error object, usually of the HTTPError class, or
 *   null if the last operation requiring auth succeeded (or there has not yet
 *   been an operation requiring auth).
 * @prop {AuthSetTokenFn} setToken Sets the token, which clears the last error
 *   encountered, if any. If given null, it will remove the token entirely.
 * @prop {AuthSetErrorFn} setError Sets an auth error, which deletes the current
 *   auth token, if any.
 */
