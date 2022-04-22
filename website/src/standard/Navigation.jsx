import react from 'react';
import { Link, NavLink } from 'react-router-dom'

import { AuthManager, makeAuthConsumer } from '../utils/components/Authentication';

import { Container, Navbar, Nav } from 'react-bootstrap';
import { mapObj, filterObj } from '../utils/utils';

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
class Navigation extends react.Component {
  render() {
    return (
      <Navbar>
        <Container>
          {/* "Link" in brand component since just redirect is needed */}
          <Navbar.Brand as={Link} to='/'>DVSC</Navbar.Brand>
          <Nav>
            {/* "NavLink" here since "active" class styling is needed */}
            {mapObj(
              filterObj(
                this.props.pages,
                (_, pageInfo) => (
                  pageInfo.nav &&
                  (
                    !pageInfo.auth ||             // If it's not auth-requiring
                    this.props.auth.token != null // Or if we're authed
                  )
                )
              ),
              (path, pageInfo, i) => (
                <Nav.Link key={i} as={NavLink} to={path}>
                  {pageInfo.name}
                </Nav.Link>
              ),
              false
            )}
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
export default makeAuthConsumer(Navigation);
