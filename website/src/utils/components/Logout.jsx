import react from 'react';

/**
 * Displays a logout button.
 * 
 * Allows customisation of the logout action.
 * 
 * @typedef {object} OwnProps
 * @prop {Function} onLogout The action to take on logout.
 * 
 * @extends {react.Component<OwnProps>}
 * 
 * @author William Taylor (19009576)
 */
export default class Login extends react.Component {
  render() {
    return (
      <div className="logout">
        <button onClick={this.props.onLogout}>Logout</button>
      </div>
    );
  }
}
