import react from 'react';

import { makeAuthConsumer } from "../utils/components/Authentication";
import Main from '../standard/Main';
import { Container, Form, Button, Alert } from 'react-bootstrap';

import { fetchJSON } from '../utils/fetch';

/**
 * The contact us page.
 * 
 * @extends {react.Component<APIConsumer>}
 * 
 * @author William Taylor (19009576)
 */
class ContactUs extends react.Component {
  constructor(props) {
    super(props);
    this.state = {
      success: null,
      error: null,

      email: "",
      phone: "",
      subject: "",
      message: ""
    };
  }

  render() {
    return (
      <Main>
        <Container>
          <h1>Contact Us</h1>
          <p className="mb-3">Please do not hesitate to get in contact with us. DVSC aim to respond in a timely manner. If your request is more urgent then please phone: +44 07493904628</p>

          {(() => {
            if (this.state.success === null) return null;
            if (this.state.success === true) {
              return (
                <Alert variant="success" dismissible onClose={() => this.setState({success: null})}>
                  <p>Message sent successfully.</p>
                </Alert>
              );
            } else {
              return (
                <Alert variant="danger" dismissible onClose={() => this.setState({success: null})}>
                  <p>Message failed to send.</p>
                  <p>{this.state.error}</p>
                </Alert>
              );
            }
          })()}

          <Form>
            <Form.Group className="mb-3" controlId="contactEmail">
              <Form.Label>Your Contact Email Address</Form.Label>
              <Form.Control
                type="email"
                placeholder="your@email-address.com"
                value={this.state.email}
                onChange={(e) => this.setEmail(e.target.value)}
              />
            </Form.Group>

            <Form.Group className="mb-3" controlId="contactPhone">
              <Form.Label>Your Contact Phone Number</Form.Label>
              <Form.Control
                type="tel"
                placeholder="12345678910"
                value={this.state.phone}
                onChange={(e) => this.setPhone(e.target.value)}
              />
            </Form.Group>

            <Form.Group className="mb-3" controlId="contactSubject">
              <Form.Label>Subject</Form.Label>
              <Form.Control
                type="text"
                placeholder="Subject"
                value={this.state.subject}
                onChange={(e) => this.setSubject(e.target.value)}
              />
            </Form.Group>

            <Form.Group className="mb-3" controlId="contactMessage">
              <Form.Label>Message</Form.Label>
              <Form.Control
                as="textarea"
                rows={3}
                placeholder="How can we help?"
                value={this.state.message}
                onChange={(e) => this.setMessage(e.target.value)}
              />
            </Form.Group>

            <Button variant="primary" onClick={this.send}>
              Send Message
            </Button>
          </Form>
        </Container>
      </Main>
    );
  }

  setEmail = (email) => this.setState({ email: email });
  setPhone = (phone) => this.setState({ phone: phone });
  setSubject = (subject) => this.setState({ subject: subject });
  setMessage = (message) => this.setState({ message: message });

  send = () => {
    const body = new URLSearchParams({
      emailAddress: this.state.email,
      telephoneNumber: this.state.phone,
      subject: this.state.subject,
      message: this.state.message
    });

    const headers = this.props.auth.token !== null ? {
      "Authorization": "bearer " + this.props.auth.token.encoded
    } : {};

    fetchJSON(
        "POST",
        this.props.approot + "/api/contact-messages",
        headers,
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
}
export default makeAuthConsumer(ContactUs);
