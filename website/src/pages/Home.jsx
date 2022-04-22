import react from 'react';

import Main from '../standard/Main';

import { Container, Row, Col, Image } from 'react-bootstrap';
import './Home.css';
import coverImage from '../media/Portrait_Placeholder.png';

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
                About Us. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin pharetra nec massa quis ultricies. Suspendisse maximus efficitur magna, in pulvinar nisi. Curabitur vestibulum libero a diam ullamcorper, eget sodales ex lobortis. Cras eget odio et elit vulputate imperdiet. Duis maximus rhoncus turpis non auctor. Integer eu ultrices ipsum. Suspendisse id pulvinar tellus, et molestie dolor. Vestibulum orci felis, tristique quis nisi eget, volutpat placerat elit. Phasellus eu nulla eget leo tempor eleifend. Mauris ut vulputate orci. Curabitur fringilla odio vel metus ultrices, a viverra tellus gravida. Aliquam sed ipsum nec eros hendrerit efficitur et vitae lacus. Vestibulum rhoncus nec erat sed varius. Quisque mollis vestibulum ex, nec euismod leo. Pellentesque mollis lorem vitae auctor varius. 
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
              More stuff about us. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin pharetra nec massa quis ultricies. Suspendisse maximus efficitur magna, in pulvinar nisi. Curabitur vestibulum libero a diam ullamcorper, eget sodales ex lobortis. Cras eget odio et elit vulputate imperdiet. Duis maximus rhoncus turpis non auctor. Integer eu ultrices ipsum. Suspendisse id pulvinar tellus, et molestie dolor. Vestibulum orci felis, tristique quis nisi eget, volutpat placerat elit. Phasellus eu nulla eget leo tempor eleifend. Mauris ut vulputate orci. Curabitur fringilla odio vel metus ultrices, a viverra tellus gravida. Aliquam sed ipsum nec eros hendrerit efficitur et vitae lacus. Vestibulum rhoncus nec erat sed varius. Quisque mollis vestibulum ex, nec euismod leo. Pellentesque mollis lorem vitae auctor varius. 
            </Col>
            <Col lg={2}>
              <Image
                fluid={true}
                src={coverImage}
                alt="cover image"
              ></Image>
            </Col>
            <Col lg={4}>
              More stuff about us. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin pharetra nec massa quis ultricies. Suspendisse maximus efficitur magna, in pulvinar nisi. Curabitur vestibulum libero a diam ullamcorper, eget sodales ex lobortis. Cras eget odio et elit vulputate imperdiet. Duis maximus rhoncus turpis non auctor. Integer eu ultrices ipsum. Suspendisse id pulvinar tellus, et molestie dolor. Vestibulum orci felis, tristique quis nisi eget, volutpat placerat elit. Phasellus eu nulla eget leo tempor eleifend. Mauris ut vulputate orci. Curabitur fringilla odio vel metus ultrices, a viverra tellus gravida. Aliquam sed ipsum nec eros hendrerit efficitur et vitae lacus. Vestibulum rhoncus nec erat sed varius. Quisque mollis vestibulum ex, nec euismod leo. Pellentesque mollis lorem vitae auctor varius. 
            </Col>
          </Row>
        </Container>
      </Main>
    );
  }
}
