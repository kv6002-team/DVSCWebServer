import react from 'react';

import Navigation from './Navigation';
import Footer from './Footer';

import { optionalEntries, optionalJoin } from '../utils/utils';

import { Container } from 'react-bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

import './Page.css';

/**
 * Renders the standardised page layout for the site.
 * 
 * Includes navbar, main content, and footer.
 * 
 * @typedef {object} OwnProps
 * @prop {object} navItems The { "/path": "Name" } pairs to create the
 *   navigation links from.
 * 
 * @extends {react.Component<OwnProps & BasicComponent & APIConsumer & LocalStorageConsumer>}
 * 
 * @author William Taylor (19009576)
 */
export default class Page extends react.Component {
  render() {
    return (
      <div {...optionalEntries({
          id: this.props.id,
          className: optionalJoin(" ", ["page", this.props.className])
      })}>
        <Navigation approot={this.props.approot} pages={this.props.pages} />
        <Container>
          {this.props.children}
        </Container>
        <Footer approot={this.props.approot} />
      </div>
    );
  }
}
