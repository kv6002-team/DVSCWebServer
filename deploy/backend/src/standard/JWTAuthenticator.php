<?php
namespace kv6002\standard;

use router\exceptions\HTTPError;

use time\Timestamp;
use time\Duration;

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
     */
    public function __construct($jwtSecret, $issuer, $db) {
        $this->jwtSecret = $jwtSecret;
        $this->issuer = $issuer;
        $this->dao = new daos\Users($db);
    }

    /**
     * Generate and return an auth JWT for the given user.
     * 
     * @param User $user The user to generate an auth JWT for.
     * @return string An encoded JWT suitable for use on a live system.
     */
    public function standardAuthToken($user) {
        return $this->createToken(
            $user,
            Timestamp::now(),
            Duration::of(90, Duration::DAY)
        );
    }

    /**
     * Check if the request contains a valid auth token, and include the user
     * for that token in the return value.
     * 
     * @param Request $request The request to check for authentication and 
     *   authorisation.
     * @return array<mixed> An array of [$request, $user].
     * @throws HTTPError 401 (Unauthorised) if the request is not authorised.
     */
    public function __invoke($request) {
        $authType = $request->authType();
        if ($authType === null) {
            throw new HTTPError(401, "No authorisation token sent");

        } elseif ($authType !== "Bearer") {
            throw new HTTPError(401,
                "Authorisation scheme not supported: '$authType'"
            );
        }

        $encodedToken = $request->authValue();
        try {
            $token = JWT::decode(
                $encodedToken,
                new Key($this->jwtSecret, "HS256")
            );

        } catch (BeforeValidException $e) {
            throw new HTTPError(401,
                "Auth token not yet valid (this is likely due to an incorrectly"
                ." set clock)"
            );

        } catch (ExpiredException $e) {
            throw new HTTPError(401, "Auth token expired");

        } catch (SignatureInvalidException $e) {
            throw new HTTPError(401, "Auth token signature invalid");
        }

        $user = $this->getUser($token->id);
        return [$request, $user];
    }

    /* Utils
    -------------------------------------------------- */

    /**
     * Encode an auth JWT for the given user.
     * 
     * @param User $user The user to generate an auth JWT for.
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
    private function createToken($user, $issueTimestamp, $validDuration) {
        return JWT::encode(
            [
                // Data
                "id" => $user->id(),
                "username" => $user->username(),

                // Metadata
                "iss" => $this->issuer,
                "iat" => $issueTimestamp->get(),
                "nbf" => $issueTimestamp->get(),
                "exp" => $issueTimestamp->plus($validDuration)->get()
            ],
            $this->jwtSecret,
            "HS256"
        );
    }

    /**
     * Return the User with the given ID, or throw a 401 HTTPError if the user
     * does not exist.
     * 
     * @param int $userID The ID of the user to get.
     * @return User The user with that ID.
     * @throws HTTPError If there is no user with that ID.
     */
    private function getUser($userID) {
        $user = $this->dao->getUser($userID);
        if ($user === null) {
            throw new HTTPError(401, "User given in auth token does not exist");
        }
        return $user;
    }
}
