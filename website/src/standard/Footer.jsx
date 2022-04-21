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
 */
export default class Footer extends react.Component {
  render() {
    return [
      <div className="footer-spacing"></div>,
      <footer>
        <Container>
          <Row className="p-2">
            <Col sm={1}></Col>
            <Col sm={2}>
              <p><strong>Email</strong>: contact@dvsc.services</p>
              <p><strong>Phone</strong>: 1234 567 8910</p>
            </Col>
            <Col sm={2}>
              <Link to="/legal/privacy-policy">Privacy Policy</Link>
            </Col>
          </Row>
          <Row className="p-2 text-center darken">
            <p>Copyright &copy; Donaldsons' Vehicle Specialist Consultancy.</p>
          </Row>
        </Container>
      </footer>
    ];
  }
}