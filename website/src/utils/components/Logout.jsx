import react from 'react';

import { Form, Col, Button } from 'react-bootstrap';

/**
 * Displays a logout button.
 * 
 * Allows customisation of the logout action.
 * 
 * @typedef {object} OwnProps
 * @prop {Function} onLogout The action to take on logout.
 * 
 * @extends {react.Component<OwnProps>}
 * 
 * @author William Taylor (19009576)
 */
export default class Logout extends react.Component {
  render() {
    return (
      <Form>
        <Col sm={2}>
          <Button variant="primary" onClick={this.props.onLogout}>
            Logout
          </Button>
        </Col>
      </Form>
    );
  }
}
