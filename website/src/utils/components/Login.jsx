import react from 'react';

import './Login.css';

/**
 * Displays username/password-based login controls.
 * 
 * Allows customisation of the login action.
 * 
 * @callback LoginFn
 * @param {string} username The username the user has entered.
 * @param {string} password The password the user has entered.
 * 
 * @typedef {object} OwnProps
 * @prop {LoginFn} onLogin The action to take on login.
 * 
 * @extends {react.Component<OwnProps>}
 * 
 * @author William Taylor (19009576)
 */
export default class Login extends react.Component {
  constructor(props) {
    super(props);
    this.state = {
      username: "",
      password: ""
    }
  }

  render() {
    return (
      <div className="login">
        <label>
          <input
            type="text"
            placeholder="Username"
            value={this.state.username}
            onChange={(e) => this.setUsername(e.target.value)}
          />
        </label>
        <label>
          <input
            type="password"
            placeholder="Password"
            value={this.state.password}
            onChange={(e) => this.setPassword(e.target.value)}
          />
        </label>
        <button onClick={this.login}>Login</button>
      </div>
    );
  }

  setUsername = (username) => this.setState({ username: username });
  setPassword = (password) => this.setState({ password: password });
  login = () => this.props.onLogin(this.state.username, this.state.password);
}
