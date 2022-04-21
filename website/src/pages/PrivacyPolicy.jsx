import react from 'react';

import Main from '../standard/Main';

import './Home.css';

/**
 * The home page - shows a random paper with a nice layout.
 * 
 * @extends {react.Component<APIConsumer>}
 * 
 * @author William Taylor (19009576)
 */
export default class PrivacyPolicy extends react.Component {
  render() {
    return (
      <Main header="Donaldsons' Vehicle Specialist Consultancy">
        Privacy Policy
      </Main>
    );
  }
}
