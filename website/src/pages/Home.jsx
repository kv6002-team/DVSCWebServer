import react from 'react';

import Main from '../standard/Main';

import { Container, Row, Col, Image } from 'react-bootstrap';
import './Home.css';
import coverImage from '../media/Portrait_Placeholder.png';

/**
 * The home page - shows a random paper with a nice layout.
 * 
 * @extends {react.Component<APIConsumer>}
 * 
 * @author William Taylor (19009576)
 */
export default class Home extends react.Component {
  render() {
    return (
      <Main>
        <div className="p-5 accent-1">
          <Container>
            <Row>
              <Col sm={2}></Col>
              <Col sm={2}>
                <Image
                  fluid={true}
                  roundedCircle={true}
                  className="shadow-3"
                  src={coverImage}
                  alt="cover image"
                ></Image>
              </Col>
              <Col sm={6}>
                About Us
              </Col>
            </Row>
          </Container>
        </div>

        <Container>
          <Row className="pt-5 pb-5">
            <Col lg={2}>
              <Image
                fluid={true}
                src={coverImage}
                alt="cover image"
              ></Image>
            </Col>
            <Col lg={4}>
              More stuff about us
            </Col>
            <Col lg={2}>
              <Image
                fluid={true}
                src={coverImage}
                alt="cover image"
              ></Image>
            </Col>
            <Col lg={4}>
              More stuff about us
            </Col>
          </Row>
        </Container>
      </Main>
    );
  }
}
