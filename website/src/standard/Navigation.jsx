import react from 'react';
import { Link, NavLink } from 'react-router-dom'

import { AuthManager } from '../utils/components/Authentication';

import { Container, Navbar, Nav } from 'react-bootstrap';
import { mapObj } from '../utils/utils';

/**
 * Renders the standardised navigation for the site.
 * 
 * Includes navigation links to the main routes of the app, and an auth manager
 * that allows login/logout.
 * 
 * @typedef {object} OwnProps
 * @prop {object} items The { "/path": "Name" } pairs to create the navigation
 *   links from.
 * 
 * @extends {react.Component<OwnProps & BasicComponent & APIConsumer & LocalStorageConsumer>}
 * 
 * @author William Taylor (19009576)
 */
export default class Navigation extends react.Component {
  render() {
    return (
      <Navbar>
        <Container>
          {/* "Link" in brand component since just redirect is needed */}
          <Navbar.Brand as={Link} to='/'>DVSC</Navbar.Brand>
          <Nav>
            {/* "NavLink" here since "active" class styling is needed */}
            {mapObj(this.props.items, (path, name, i) => (
              <Nav.Link as={NavLink} to={path}>{name}</Nav.Link>
            ), false)}
            <AuthManager
              endpoint={this.props.approot + "/api/auth"}
              localStoragePrefix={this.props.localStoragePrefix}
            />
          </Nav>
        </Container>
      </Navbar>
    );
  }
}
