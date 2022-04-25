import react from 'react';

import { makeAuthConsumer } from "../utils/components/Authentication";
import Main from '../standard/Main';

import { fetchJSON } from '../utils/fetch';
import { Container, Form, Button, Alert } from 'react-bootstrap';

/**
 * The page for changing and resetting an account password.
 * 
 * @extends {react.Component<APIConsumer>}
 * 
 * @author William Taylor (19009576)
 */
class ChangePassword extends react.Component {
  constructor(props) {
    super(props);
    this.state = {
      success: null,
      error: null,

      loggedIn: false,

      username: "",
      newPassword: "",
      repeatNewPassword: ""
    };
  }

  render() {
    return (
      <Main>
        <Container>
          <h1>Change Password</h1>

          {(() => {
            if (this.state.success === null) return null;
            if (this.state.success === true) {
              return (
                <Alert variant="success" dismissible onClose={() => this.setState({success: null})}>
                  <p>{this.state.loggedIn ? "Password changed successfully" : "Verification email sent"}.</p>
                </Alert>
              );
            } else {
              return (
                <Alert variant="danger" dismissible onClose={() => this.setState({success: null})}>
                  <p>{this.state.loggedIn ? "Attempting to change your password failed" : "VerificationEmail failed to send"}.</p>
                  <p>{this.state.error}</p>
                </Alert>
              );
            }
          })()}

          <Form>
            <Form.Group className="mb-3" controlId="changePasswordUsername">
              <Form.Label>Username</Form.Label>
              <Form.Control
                type="text"
                placeholder="username"
                value={this.state.username}
                disabled={this.state.loggedIn}
                onChange={(e) => this.setUsername(e.target.value)}
              />
            </Form.Group>

            {!this.state.loggedIn ? (
              <Alert variant="warning">
                You must verify your account's email address before you can
                change your password. We will send you an email containing link
                to a page in which you can change your password. The page will
                stop allowing you to change your password after 10 minutes from
                clicking 'Verify Email' below.
              </Alert>
            ) : null}

            <Form.Group className="mb-3" controlId="changePasswordNewPassword">
              <Form.Label>New Password</Form.Label>
              <Form.Control
                type="text"
                placeholder="password"
                value={this.state.newPassword}
                disabled={!this.state.loggedIn}
                onChange={(e) => this.setNewPassword(e.target.value)}
              />
            </Form.Group>

            <Form.Group className="mb-3" controlId="changePasswordRepeatNewPassword">
              <Form.Label>Repeat New Password</Form.Label>
              <Form.Control
                type="text"
                placeholder="password"
                value={this.state.repeatNewPassword}
                disabled={!this.state.loggedIn}
                onChange={(e) => this.setRepeatNewPassword(e.target.value)}
              />
            </Form.Group>

            <Button variant="primary" onClick={this.changePassword}>
              {this.state.loggedIn ? "Change Password" : "Verify Email"}
            </Button>
          </Form>
        </Container>
      </Main>
    );
  }

  setUsername = (username) => this.setState({ username: username });
  setNewPassword = (newPassword) => this.setState({ newPassword: newPassword });
  setRepeatNewPassword = (repeatNewPassword) => this.setState({ repeatNewPassword: repeatNewPassword });

  changePassword = () => {
    if (this.state.newPassword !== this.state.repeatNewPassword) {
      this.setState({
        success: false,
        error: "Passwords entered are not identical."
      });
      return;
    }

    const requestSpecificParams = this.state.loggedIn ?
      { newPassword: this.state.newPassword } : // Set the password if able
      { username: this.state.username }; // Otherwise request verification email
    const body = new URLSearchParams(
      Object.assign({ types: "garage,garage-consultant" }, requestSpecificParams)
    );

    const headers = this.props.auth.token !== null ? {
      "Authorization": "bearer " + this.props.auth.token.encoded
    } : {};

    fetchJSON(
        "POST",
        this.props.approot + "/api/change-password",
        headers,
        body
    )
      .then(() => {
        this.props.auth.login(this.state.username, this.state.newPassword);
        this.setState({ success: true });
      })
      .catch((error) => {
        this.props.handleIfAuthError(error);
        this.setState({ success: false, error: error.explanation });
      });
  }

  /**
   * Set/clear the username on login/logout (respectively) while this component
   * is mounted.
   * 
   * @param {object} prevProps The previous render's props.
   */
  componentDidUpdate(prevProps) {
    const token = this.props.auth.token;
    if (token === prevProps.auth.token) return; // Loop guard

    if (token === null) {
      // Reset after logout
      this.setState({ loggedIn: false, username: "" });
    } else {
      this.setState({ loggedIn: true, username: token.decoded.username });
    }
  }

  /**
   * Set the username value & fixed status on mount.
   */
  componentDidMount() {
    const token = this.props.auth.token;
    if (token !== null) {
      this.setState({ loggedIn: true, username: token.decoded.username });
    }
  }
}
export default makeAuthConsumer(ChangePassword);
