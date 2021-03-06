import react from 'react';
import { Link } from 'react-router-dom';

import { Container, Row, Col } from 'react-bootstrap';
import './Footer.css';

/**
 * Renders the standardised footer for the site.
 * 
 * @extends {react.Component<BasicComponent>}
 * 
 * @author William Taylor (19009576)
 * @author Scott Donaldson (19019810)
 */
export default class Footer extends react.Component {
  render() {
    return [
      <div className="footer-spacing"></div>,
      <footer className="bg-dark text-light">
        <Container>
          <Row>
            <Col sm={1}></Col>
            <Col sm={4}>
              <p><strong>Email</strong>: kevindonaldsondvsc@outlook.com</p>
              <p><strong>Phone</strong>: +44 07493904628</p>
            </Col>
            <Col sm={4}>
              <Link to="/legal/privacy-policy">Privacy Policy</Link>
            </Col>
          </Row>
          <Row className="p-2 block-emphasis">
            <Col>
              <p className="text-center mb-0">
                Copyright &copy; Donaldsons' Vehicle Specialist Consultancy.
              </p>
            </Col>
          </Row>
        </Container>
      </footer>
    ];
  }
}
