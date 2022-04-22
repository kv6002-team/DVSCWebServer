import react from 'react';

import Main from '../standard/Main';

import { Container, Form, Button } from 'react-bootstrap';

/**
 * The contact us page.
 * 
 * @extends {react.Component<APIConsumer>}
 * 
 * @author William Taylor (19009576)
 */
export default class ContactUs extends react.Component {
  constructor(props) {
    super(props);
    this.state = {
      useLogin: false, // TODO: Make this the current auth state by default
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
          <p className="mb-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla laoreet tellus velit, at efficitur magna malesuada fermentum. Proin interdum tristique ultrices. Morbi maximus ex in mi ultricies pretium tincidunt id.</p>

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
            <Button variant="primary">
              Login
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
}
