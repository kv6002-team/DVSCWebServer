import react from 'react';

import Main from '../standard/Main';

import { Container, Row, Col } from 'react-bootstrap';
import './Home.css';

/**
 * The home page.
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
              <Col sm={1}></Col>
              <Col sm={4}><h1>Welcome to Donaldsons Vehicle Specialist Consultancy Services</h1></Col>
              <Col sm={6}>
                <p>
                  We are currently in our third year of assisting MOT Stations with site visits and annual CPD training.
                  We are a local North East individual company offering a bespoke individual service for MOT Testers and MOT Managers.
                </p>
                <p>
                  Offering annual CPD training courses, as well as annual MOT Testers Training.
                </p>
                <p>
                  The Training will be delivered by a trained professional Ex-DVSA Enforcement officer of 17 years with an additional 23 years in the Motor Industry who has a wealth of knowledge and experience across a wide scope of Motor Vehicle classes and types.
                </p>
              </Col>
            </Row>
          </Container>
        </div>

        <Container>
          <Row className="pt-5 pb-5">
            <Col sm={1}></Col>
            <Col lg={4}>
              <p className="text-right">Individual Services include:</p>
            </Col>
            <Col lg={6}>
              <ul>
                <li>MOT VT8 Practical Training</li>
                <li>MOT VT6 Practical Training</li>
                <li>Annual Assessments</li>
                <li>Representation on disciplinary cases</li>
                <li>24/7 Helpline for MoT queries</li>
              </ul>
            </Col>
          </Row>
        </Container>
      </Main>
    );
  }
}
