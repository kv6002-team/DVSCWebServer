<?php
namespace kv6002\standard;

use util\Util;
use time\Timestamp;
use time\Duration;

use router\exceptions\HTTPError;

use firebase\jwt\JWT;
use firebase\jwt\Key;
use firebase\jwt\BeforeValidException;
use firebase\jwt\ExpiredException;
use firebase\jwt\SignatureInvalidException;

use kv6002\daos;

/**
 * Authenticator that uses JWT (bearer token) authentication.
 * 
 * @author William Taylor (19009576)
 */
class JWTAuthenticator {
    private $jwtSecret;
    private $issuer;
    private $dao;

    /**
     * Construct a JWTAuthenticator.
     * 
     * @param string $jwtSecret The JTW secret key, used to verify the token's
     *   signature.
     * @param string $issuer The full URL to the endpoint that issues tokens.
     * @param Database $db The database to get users from.
     */
    public function __construct($jwtSecret, $issuer, $db) {
        $this->jwtSecret = $jwtSecret;
        $this->issuer = $issuer;
        $this->dao = new daos\Users($db);
    }

    /* Token Creation
    -------------------------------------------------- */

    /**
     * Generate and return an auth JWT for the given user.
     * 
     * @param User $user The user to generate an auth JWT for.
     * @param array<string> $authorisations A list of strings stating the
     *   purposes for which this token will be valid. Which purposes are
     *   available depends on the resources using this authenticator.
     * 
     * @return string An encoded JWT suitable for use on a live system.
     */
    public function standardAuthToken($user, $authorisations) {
        return $this->createToken(
            $user,
            $authorisations,
            Timestamp::now(),
            Duration::of(1, Duration::DAY)
        );
    }

    /**
     * Generate and return an auth JWT for the given user that lasts a short
     * amount of time.
     * 
     * This kind of token is intended to be used for ephemeral tokens for
     * specific purposes (`authorisations` list values).
     * 
     * @param User $user The user to generate an auth JWT for.
     * @param array<string> $authorisations A list of strings stating the
     *   purposes for which this token will be valid. Which purposes are
     *   available depends on the resources using this authenticator.
     * 
     * @return string An encoded JWT suitable for use on a live system.
     */
    public function shortAuthToken($user, $authorisations) {
        return $this->createToken(
            $user,
            $authorisations,
            Timestamp::now(),
            Duration::of(10, Duration::MINUTE)
        );
    }

    /* Token Verification
    -------------------------------------------------- */

    /* Token Extraction
    -------------------- */

    /**
     * Return a function that checks if the request contains a valid auth token,
     * and that includes the user and authorisations for that token in the
     * return value.
     * 
     * The user is a `domain\User` object.
     * 
     * The authorisations list is an array of strings stating the purposes for
     * which the token is valid. What strings are valid purposes is determined
     * by the resources using this auth system.
     * 
     * @param Request $request The request to check for authentication and 
     *   authorisation.
     * 
     * @return array<mixed> An array of [$request, $user, $authorisations],
     *   where $user will be null and $authorisations will be the empty array if
     *   no token was sent.
     * 
     * @throws HTTPError 401 (Unauthorised) if the auth token is invalid in
     *   unacceptable ways (eg. type given but token not, not issued by this
     *   website, otherwise forged, expired, etc.).
     */
    public function auth() {
        return function ($request) {
            // Check if a token was sent
            $authType = $request->authType();
            if ($authType === null) {
                $user = null;
                $authorisations = [];

            } else {
                // Check auth type
                if ($authType !== "bearer") {
                    throw new HTTPError(401,
                        "Authorisation scheme not supported: '$authType'"
                    );
                }

                // Decode and verify token
                $encodedToken = $request->authValue();
                try {
                    $token = JWT::decode(
                        $encodedToken,
                        new Key($this->jwtSecret, "HS256")
                    );

                } catch (BeforeValidException $e) {
                    throw new HTTPError(401,
                        "Auth token not yet valid (this is likely due to an"
                        ." incorrectly set clock)"
                    );

                } catch (ExpiredException $e) {
                    throw new HTTPError(401, "Auth token expired");

                } catch (SignatureInvalidException $e) {
                    throw new HTTPError(401, "Auth token signature invalid");
                }

                // Validate and extract user of authentication (if claimed)
                if (property_exists($token, "id")) {
                    if (!property_exists($token, "usertype")) {
                        throw new HTTPError(401,
                            "Auth token claiming authentication must contain user type"
                        );
                    }
                    $user = $this->dao->get($token->usertype, $token->id);
                    if ($user === null) {
                        throw new HTTPError(401,
                            "User given in auth token claiming authentication no longer exists"
                        );
                    }
                } else {
                    $user = null;
                }

                // Validate and extract authorisations (if claimed)
                if (property_exists($token, "authorisations")) {
                    $authorisations = $token->authorisations;
                    if (!is_array($authorisations)) {
                        throw new HTTPError(401,
                            "`authorisation` property of auth token claiming authorisations must be a list"
                        );
                    }
                } else {
                    $authorisations = [];
                }
            }

            // Return everything
            return [$request, $user, $authorisations];
        };
    }

    /* Optional Token Validation
    -------------------- */

    /**
     * Require authentication (ie. a user in the token).
     * 
     * If a user ID is given, require the authenticated user to have that ID
     * (uses strict comparison).
     * 
     * @param mixed $id The ID of the required user.
     * 
     * @return array<mixed> Passes on what auth() returns.
     * 
     * @throws HTTPError 401 (Unauthorised) if the auth token was not given, or
     *   if the authenticated user is not allowed for this resource.
     */
    public function requireAuthentication($id = null) {
        return function ($request, $user, $authorisations) use ($id) {
            if ($user === null) {
                throw new HTTPError(401,
                    "Authentication required, but not claimed"
                );
            }

            if ($id !== null && $user->id() !== $id) {
                throw new HTTPError(403,
                    "Authenticated user is not authorised to take that action"
                );
            }

            return [$request, $user, $authorisations];
        };
    }

    /**
     * Require authentication (ie. an authorisation entry in the token that
     * matches at least one of the given set of authorisations).
     * 
     * @param array<string> $validAuthorisations The list of valid
     *   authorisations. At least one of these must be contained in the list of
     *   authorisations in the token.
     * 
     * @return callable (Request, User, array<string>)->array<mixed> A callable
     *   that takes what auth() returns, checks that the authorisations in the
     *   token includes at least one of the given set of authorisations and
     *   throws a HTTPError 403 (Forbidden) if not, then returns the parameters
     *   it was given.
     */
    public function requireAuthorisation($validAuthorisations) {
        if (!is_array($validAuthorisations)) {
            $validAuthorisations = [$validAuthorisations];
        }

        return function ($request, $user, $authorisations)
                use ($validAuthorisations)
        {
            if ($authorisations === null || count($authorisations) === 0) {
                throw new HTTPError(403,
                    "Authorisation required, but none claimed"
                );
            }

            $hasValidAuthorisations = Util::any(
                $validAuthorisations,
                function ($validAuthorisation) use ($authorisations) {
                    return in_array($validAuthorisation, $authorisations);
                }
            );
            if (!$hasValidAuthorisations) {
                throw new HTTPError(403,
                    "Authenticated user is not authorised to take that action"
                );
            }

            return [$request, $user, $authorisations];
        };
    }

    /* Utils
    -------------------------------------------------- */

    /**
     * Encode an auth JWT for the given user.
     * 
     * @param User $user The user to generate an auth JWT for.
     * @param array<string> $authorisations A list of strings stating the
     *   purposes for which this token will be valid. Which purposes are
     *   available depends on the resources using this authenticator.
     * @param Timestamp $issueTimestamp The 'generation time' of the token. May
     *   or may not be the current time.
     * @param Duration $validDuration The duration after the 'generation time'
     *   that this token is valid.
     * 
     * @return string An encoded JWT.
     * 
     * @see standardAuthToken() for the correct way of making an auth token for
     *   the application.
     */
    private function createToken(
            $user,
            $authorisations,
            $issueTimestamp,
            $validDuration
    ) {
        return JWT::encode(
            [
                // Authentication Data
                "id" => $user !== null ? $user->id() : null,
                "usertype" => $user !== null ? $user->type() : null,
                "username" => $user !== null ? $user->username() : null,

                // Authorisation Data
                "authorisations" => is_array($authorisations) ?
                    $authorisations :
                    [$authorisations],

                // JWT Metadata
                "iss" => $this->issuer,
                "iat" => $issueTimestamp->get(),
                "nbf" => $issueTimestamp->get(),
                "exp" => $issueTimestamp->plus($validDuration)->get()
            ],
            $this->jwtSecret,
            "HS256"
        );
    }
}
