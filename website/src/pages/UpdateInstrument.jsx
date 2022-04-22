import react from 'react';

import { makeAuthConsumer } from "../utils/components/Authentication";
import Main from '../standard/Main';
import { Container, Form, Row, Col, Button } from 'react-bootstrap';

import { fetchJSON } from '../utils/fetch';
import { mapObj } from '../utils/utils';

/**
 * The page for updating the official check expiry date of garage instruments.
 * 
 * @extends {react.Component<APIConsumer>}
 * 
 * @author William Taylor (19009576)
 */
class UpdateInstrument extends react.Component {
  constructor(props) {
    super(props);

    this.state = {
      instruments: [],

      instrument: "",
      newExpiryDate: ""
    };
  }

  render() {
    return (
      <Main>
        <Container>
          <h1>Update Instrument Due Date</h1>
          <p className="mb-3">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla laoreet tellus velit, at efficitur magna malesuada fermentum. Proin interdum tristique ultrices. Morbi maximus ex in mi ultricies pretium tincidunt id.</p>

          <Form>
            <Row>
              <Col lg={3}></Col>
              <Col lg={6}>
                <Form.Group className="mb-3" controlId="contactSubject">
                  <Form.Label></Form.Label>
                  <Form.Select
                    onChange={(e) => this.setInstrument(e.target.value)}
                  >
                    {mapObj(this.state.instruments, (_, instrument, i) => (
                      <option key={i} value={instrument.id}>{instrument.name}</option>
                    ), false)}
                  </Form.Select>
                </Form.Group>

                <Form.Group className="mb-3" controlId="contactMessage">
                  <Form.Label>Next Check Due Date</Form.Label>
                  <Form.Control
                    type="date"
                    value={this.state.newExpiryDate}
                    onChange={(e) => this.setNewExpiryDate(e.target.value)}
                  />
                </Form.Group>

                <Button variant="primary" onClick={this.updateInstrument}>
                  Update
                </Button>
              </Col>
            </Row>
          </Form>
        </Container>
      </Main>
    );
  }

  newInstrumentStateFor = (instruments, selectedID) => ({
    instrument: selectedID,
    newExpiryDate: this.formatDate(
      new Date(instruments[selectedID].officialCheckExpiryDate)
    )
  });
  setInstrument = (instrumentID) => this.setState(
    this.newInstrumentStateFor(this.state.instruments, instrumentID)
  );
  setNewExpiryDate = (newExpiryDate) => this.setState({
    newExpiryDate: newExpiryDate
  });

  /**
   * Load the list.
   */
  fetchInstrumentSelection() {
    fetchJSON(
        "GET",
        this.props.approot + "/api/garages/"+this.props.auth.token.decoded.id,
        this.getHeaders()
    )
      .then((garage) => {
        // Derive the additional state for the first instrument, if there is one
        let additionalState = {};
        if (garage.instruments.length > 0) {
          additionalState = this.newInstrumentStateFor(
            garage.instruments,
            garage.instruments[0].id
          );
        }

        // Set the state
        this.setState(Object.assign(
          {
            instruments: this.assignAllByID({}, garage.instruments)
          },
          additionalState
        ));
      })
      .catch((error) => {
        this.props.handleIfAuthError(error);
        this.setState({ instruments: error });
      });
  }

  /**
   * Update instrument
   */
  updateInstrument = () => {
    //
  }

  /**
   * Fetch/clear the reading list on login/logout (respectively) while this
   * component is mounted.
   * 
   * @param {object} prevProps The previous render's props.
   */
  componentDidUpdate(prevProps) {
    if (this.props.auth.token === prevProps.auth.token) return; // Loop guard

    if (this.props.auth.token === null) {
      this.setState({ instruments: {} }); // Reset after logout
    } else {
      this.fetchInstrumentSelection();
    }
  }

  /**
   * Fetch the reading list when this component is mounted if the user is
   * already logged in.
   */
  componentDidMount() {
    /* If you reload the page this component is on, during the initial mount of
     * the whole page, this component's mount won't have the token, because (by
     * definition of a *context* provider) this component is part of the subtree
     * of the auth context provider, which means the provider won't have loaded
     * its data in its componentDidMount() yet. However, switching to (or back
     * to) the page this component is on will remount this component, without
     * re-mounting the auth context provider (because that's handled at the App
     * level), or calling this component's componentDidUpate() [1].
     * 
     * [1] https://reactjs.org/docs/react-component.html#componentdidupdate
     */
    if (this.props.auth.token !== null) {
      this.fetchInstrumentSelection();
    }
  }

  /* Utils
  -------------------------------------------------- */

  /**
   * @returns The headers needed for this component's fetches.
   */
  getHeaders = () => {
    return {
      "Authorization": "bearer " + this.props.auth.token
    }
  };

  // Based on: https://stackoverflow.com/a/12409344
  formatDate = (date) => {
    const yyyy = date.getFullYear();
    let mm = date.getMonth() + 1; // Months start at 0!
    let dd = date.getDate();

    if (mm < 10) mm = '0' + mm;
    if (dd < 10) dd = '0' + dd;

    return yyyy+"-"+mm+"-"+dd;
  }

  // Based on: https://stackoverflow.com/a/19346876 and the above
  formatTime = (date) => {
    let hh = date.getHours();
    let mm = date.getMinutes();
    let ss = date.getSeconds();

    if (hh < 10) hh = '0' + hh;
    if (mm < 10) mm = '0' + mm;
    if (ss < 10) ss = '0' + ss;

    return this.formatDate() +" "+ hh+"-"+mm+"-"+ss;
  }

  /**
   * Return a copy of the target object, plus all given objects assigned to
   * the value of their ID property (ie. { [obj.id]: obj }) in the new object.
   * 
   * @param {object} target The object to assign to.
   * @param {Array<object>} objs The objects to assign by ID.
   * @returns {object} A copy of the target object with all given objects
   *   assigned by their IDs.
   */
  assignAllByID(target, objs) {
    return objs.reduce((accum, obj) => {
      accum[obj.id] = obj;
      return accum;
    }, Object.assign({}, target));
  }
}
export default makeAuthConsumer(UpdateInstrument);
