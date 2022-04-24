import react from "react";

import withRouter from "./withRouter"

import Login from "./Login";
import Logout from "./Logout";

import { fetchJSON } from "../fetch";
import jwtDecode from "jwt-decode";

/**
 * The authentication react context.
 * 
 * @type {react.Context<Auth | null>}
 */
const AuthContext = react.createContext(null);

/**
 * A provider of the authentication context.
 * 
 * @extends {react.Component<LocalStorageConsumer>}
 * 
 * @author William Taylor (19009576)
 */
class AuthenticationProvider extends react.Component {
  constructor(props) {
    super(props);

    this.localStorageKey = this.props.localStoragePrefix + "_authToken";

    this.prevToken = null; // To prevent update loops
    this.state = {
      token: null,
      error: null
    };
  }

  render() {
    /** @type {Auth} */
    const auth = {
      token: this.state.token,
      error: this.state.error,

      setToken: this.setToken,
      setError: this.setError,

      login: this.login,
      logout: this.logout
    };

    return (
      <AuthContext.Provider value={auth}>
        {this.props.children}
      </AuthContext.Provider>
    );
  }

  /**
   * Set the auth token in this auth context to the given token.
   * 
   * @param {string|null} token A valid JWT, or null to clear the token.
   */
  setToken = (token) => {
    let fullToken = null;
    if (token != null) {
      fullToken = {
        encoded: token,
        decoded: jwtDecode(token)
      };
      localStorage.setItem(this.localStorageKey, token);
    } else {
      localStorage.removeItem(this.localStorageKey);
    }

    this.setState({
      token: fullToken,
      error: null
    });
  }

  /**
   * Set the auth error in this auth context to the given error.
   * 
   * @param {HTTPError|null} error A http error that was thrown during auth or
   *   during an auth operation, or null to clear the error.
   */
  setError = (error) => {
    // An existing token should not survive errors, eg. expired
    localStorage.removeItem(this.localStorageKey);
    this.setState({ token: null, error: error });
  };

  /**
   * Attempts to authenticate using the endpoint given as a prop with the
   * credentials entered by the user, and if successful, updates the nearest
   * auth context.
   * 
   * @param {string} username The username the user entered.
   * @param {string} password The password the user entered.
   */
  login = (username, password) => {
    const authEndpoint = this.props.approot + this.props.authEndpoint;

    const headers = {
      "Authorization": "basic " + btoa(`${username}:${password}`)
    };

    const supplementaryInfo = new FormData();
    supplementaryInfo.append('types', 'garage');

    fetchJSON("POST", authEndpoint, headers, supplementaryInfo)
      .then(({token}) => this.setToken(token))
      .catch(this.setError);
  };

  /**
   * Logs out the current user, and if successful, updates the nearest auth
   * context.
   */
  logout = () => this.setToken(null);

  /**
   * Load the auth token from localstorage, if it exists.
   */
  componentDidMount() {
    this.setToken(localStorage.getItem(this.localStorageKey));
  }

  /**
   * Switch to the "Reset Password (Required)" route if the auth token returned
   * has an insufficient authorisation to perform most actions, but a sufficient
   * authorisation to reset the user's password without email verification.
   */
  componentDidUpdate() {
    // Loop guard
    if (this.state.token === this.prevToken) {
      return;
    } else {
      this.prevToken = this.state.token;
    }

    if (this.hasResetAuthorisation(this.state.token)) {
      this.props.router.navigate(this.props.resetPasswordRequiredRoute);
    }
  }

  hasNoAuthorisation = (token) => (
    token === null || !(
      token.decoded.authorisations.includes("general") ||
      token.decoded.authorisations.includes("password_reset__password_auth")
    )
  );
  hasGeneralAuthorisation = (token) => (
    token !== null &&
    token.decoded.authorisations.includes("general")
  );
  hasResetAuthorisation = (token) => (
    token !== null &&
    !token.decoded.authorisations.includes("general") &&
    token.decoded.authorisations.includes("password_reset__password_auth")
  );
}
export const AuthProvider = withRouter(AuthenticationProvider);

/**
 * Provides the props of an {@link AuthConsumer} to its children.
 * 
 * @extends {react.Component}
 * 
 * @author William Taylor (19009576)
 */
class AuthenticationConsumer extends react.Component {
  // Modified from: https://stackoverflow.com/a/54235540/16967315

  render() {
    return (
      <AuthContext.Consumer>
        {auth => this.childrenWithAdditionalProps({
          auth: auth,
          handleIfAuthError: (error) => this.handleIfAuthError(error, auth)
        })}
      </AuthContext.Consumer>
    );
  }

  /**
   * Handle auth-related HTTP errors (401 and 403) by updating the nearest auth
   * context.
   * 
   * @param {HTTPError} error The error that occured when performing an
   *   authenticated operation.
   * @param {Auth} auth The current auth context.
   */
  handleIfAuthError = (error, auth) => {
    if ([401, 403].includes(error.code)) {
      auth.setError(error);
    }
  };

  // Utils

  /**
   * Add the given props to all children of this component and return the
   * new children.
   * 
   * @param {object} props An object containing props
   * @returns {react.ReactNode} Copies of all element children of this component
   *   with the given props added.
   */
  childrenWithAdditionalProps(props) {
    // Modified from: https://stackoverflow.com/a/32371612/16967315
    // Will use index-based keys, so not changing the number/type of
    // children over the Consumer's lifecycle is advisable.
    return react.Children.map(
      this.props.children,
      // Checking isValidElement is the safe way and avoids a typescript
      // error too.
      (child) => react.isValidElement(child) ?
        react.cloneElement(child, props) :
        child
    );
  }
}
export const AuthConsumer = AuthenticationConsumer;

/**
 * A higher-order component that wraps the given component with a
 * {@link Consumer}.
 * 
 * @param {react.ReactElement} Component The component to make into an auth
 *   consumer.
 * @returns {react.ReactElement} The wrapped component.
 * 
 * @author William Taylor (19009576)
 */
export const makeAuthConsumer = (Component) => (
  (props) => (
    <AuthConsumer>
      <Component {...props} />
    </AuthConsumer>
  )
);

/**
 * A component that renders basic login and logout controls and updates the auth
 * context accordingly.
 * 
 * @typedef {object} OwnProps
 * @prop {string} endpoint The endpoint to submit login requests to. It must
 *   accept POST requests with `username` and `password` parameters in form data
 *   in the body, and return a 200 (OK) JSON response that includes a valid auth
 *   token as a string. The response must meet the following schema:
 *     {
 *       "token": <base64_encoded_token_string>
 *     }
 * 
 * @extends {react.Component<OwnProps & AuthConsumer>}
 * 
 * @author William Taylor (19009576)
 */
class AuthenticationManager extends react.Component {
  render() {
    if (this.props.auth.token == null) {
      return (<Login onLogin={this.props.auth.login} />);
    }
    return (<Logout onLogout={this.props.auth.logout} />);
  }
}

/** A {@link Manager} wrapped in a {@link Consumer}. */
export const AuthManager = makeAuthConsumer(AuthenticationManager);

/**
 * A component that renders its children only if a user is logged in.
 * 
 * @extends {react.Component<AuthConsumer>}
 * 
 * @author William Taylor (19009576)
 */
class AuthenticationRestricted extends react.Component {
  render() {
    if (this.props.auth.token != null) {
      return this.props.children;
    }
    return this.props.renderNotAuthorised(this.props.auth);
  }
}

/** A {@link Consumer} wrapped in a {@link Consumer}. */
export const AuthRestricted = makeAuthConsumer(AuthenticationRestricted);
