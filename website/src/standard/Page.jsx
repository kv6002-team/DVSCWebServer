import react from 'react';

import withRouter from "../utils/components/withRouter"
import { makeAuthConsumer } from "../utils/components/Authentication";
import Navigation from './Navigation';
import Footer from './Footer';

import { optionalEntries, optionalJoin } from '../utils/utils';

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
class Page extends react.Component {
  render() {
    return (
      <div {...optionalEntries({
          id: this.props.id,
          className: optionalJoin(" ", ["page", this.props.className])
      })}>
        <Navigation approot={this.props.approot} pages={this.props.pages} />
        {this.props.children}
        <Footer approot={this.props.approot} />
      </div>
    );
  }

  componentDidUpdate(prevProps) {
    if (this.props.auth.token === prevProps.auth.token) return; // Loop guard

    const token = this.props.auth.token;
    if (
      token !== null &&
      token.decoded.authorisations.includes("password_reset__password_auth")
    ) {
      this.props.router.navigate(this.props.resetPasswordRequiredRoute);
    }
  }
}
export default makeAuthConsumer(withRouter(Page));
