import react from 'react';

import { Form, Row, Col, Button } from 'react-bootstrap';

/**
 * Displays username/password-based login controls.
 * 
 * Allows customisation of the login action.
 * 
 * @callback LoginFn
 * @param {string} username The username the user has entered.
 * @param {string} password The password the user has entered.
 * 
 * @typedef {object} OwnProps
 * @prop {LoginFn} onLogin The action to take on login.
 * 
 * @extends {react.Component<OwnProps>}
 * 
 * @author William Taylor (19009576)
 */
export default class Login extends react.Component {
  constructor(props) {
    super(props);
    this.state = {
      username: "",
      password: ""
    }
  }

  render() {
    return (
      <Form>
        <Row className="align-items-center">
          <Col sm={5}>
            <Form.Control
              type="text"
              placeholder="VTS Number"
              value={this.state.username}
              onChange={(e) => this.setUsername(e.target.value)}
            />
          </Col>
          <Col sm={5}>
            <Form.Control
              type="password"
              placeholder="Password"
              value={this.state.password}
              onChange={(e) => this.setPassword(e.target.value)}
            />
          </Col>
          <Col sm={2}>
            <Button variant="primary" onClick={this.login}>
              Login
            </Button>
          </Col>
        </Row>
      </Form>
    );
  }

  setUsername = (username) => this.setState({ username: username });
  setPassword = (password) => this.setState({ password: password });
  login = () => this.props.onLogin(this.state.username, this.state.password);
}
