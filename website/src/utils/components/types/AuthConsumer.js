/**
 * @typedef {object} AuthConsumer
 * @prop {Auth} auth The current authentication context.
 * @prop {Function} handleIfAuthError An error handler that should be called if
 *   an error occurs after an authenticated fetch() request to handle auth
 *   errors.
 */
