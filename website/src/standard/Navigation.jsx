import react from 'react';
import { Link } from 'react-router-dom';

import { AuthManager } from '../utils/components/Authentication';

import { mapObj, optionalEntries } from '../utils/utils';

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
      <nav {...optionalEntries({
          id: this.props.id,
          className: this.props.className
      })}>
        <ul>
          {mapObj(this.props.items, (path, name, i) => (
            <li key={i}><Link className="button" to={path}>{name}</Link></li>
          ), false)}
        </ul>

        <AuthManager
          endpoint={this.props.approot + "/api/auth"}
          localStoragePrefix={this.props.localStoragePrefix}
        />
      </nav>
    );
  }
}
