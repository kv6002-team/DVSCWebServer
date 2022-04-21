import react from 'react';

import Main from '../standard/Main';

import { Container, Form, Button } from 'react-bootstrap';
import './Home.css';

/**
 * The contact us page.
 * 
 * @extends {react.Component<APIConsumer>}
 * 
 * @author William Taylor (19009576)
 */
export default class ContactUs extends react.Component {
  render() {
    return (
      <Main>
        <Container>
          <p className="mb-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla laoreet tellus velit, at efficitur magna malesuada fermentum. Proin interdum tristique ultrices. Morbi maximus ex in mi ultricies pretium tincidunt id.</p>
          <Form>
            <Form.Group className="mb-3" controlId="contactSubject">
              <Form.Label>Subject</Form.Label>
              <Form.Control type="text" placeholder="Subject" />
            </Form.Group>
            <Form.Group className="mb-3" controlId="contactMessage">
              <Form.Label>Message</Form.Label>
              <Form.Control as="textarea" rows={3} placeholder="How can we help?" />
            </Form.Group>
            <Button variant="primary" type="submit">
              Login
            </Button>
          </Form>
        </Container>
      </Main>
    );
  }
}
