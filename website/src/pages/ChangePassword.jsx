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

      newPassword: "",
      repeatNewPassword: ""
    };
  }

  render() {
    return (
      <Main>
        <Container>
          <h1>Change Password</h1>
          <p className="mb-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla laoreet tellus velit, at efficitur magna malesuada fermentum. Proin interdum tristique ultrices. Morbi maximus ex in mi ultricies pretium tincidunt id.</p>

          {this.state.success !== null ?
            (this.state.success === true ? (
              <Alert variant="success" dismissible onClose={() => this.setState({success: null})}>
                <p>Password changed successfully.</p>
              </Alert>
            ) : (
              <Alert variant="danger" dismissible onClose={() => this.setState({success: null})}>
                <p>Attempting to change your password failed.</p>
                <p>{this.state.error}</p>
              </Alert>
            )) : null
          }

          <Form>
            <Form.Group className="mb-3" controlId="changePasswordNewPassword">
              <Form.Label>New Password</Form.Label>
              <Form.Control
                type="text"
                placeholder="New Password"
                value={this.state.newPassword}
                onChange={(e) => this.setNewPassword(e.target.value)}
              />
            </Form.Group>

            <Form.Group className="mb-3" controlId="changePasswordRepeatNewPassword">
              <Form.Label>Repeat New Password</Form.Label>
              <Form.Control
                type="text"
                placeholder="New Password"
                value={this.state.repeatNewPassword}
                onChange={(e) => this.setRepeatNewPassword(e.target.value)}
              />
            </Form.Group>

            <Button variant="primary" onClick={this.changePassword}>
              Change Password
            </Button>
          </Form>
        </Container>
      </Main>
    );
  }

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

    const body = new URLSearchParams({
      newPassword: this.state.newPassword
    });

    fetchJSON(
        "POST",
        this.props.approot + "/api/change-password",
        this.getHeaders(),
        body
    )
      .then(() => {
        this.setState({ success: true });
      })
      .catch((error) => {
        this.props.handleIfAuthError(error);
        this.setState({ success: false, error: error.explanation });
      });
  }

  /* Utils
  -------------------------------------------------- */

  /**
   * @returns The headers needed for this component's fetches.
   */
   getHeaders = () => {
    return {
      "Authorization": "bearer " + this.props.auth.token.encoded
    }
  };
}
export default makeAuthConsumer(ChangePassword);
