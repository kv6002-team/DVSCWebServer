import react from 'react';

import Main from '../standard/Main';

/**
 * The 404 page.
 * 
 * @author William Taylor (19009576)
 */
export default class NotFound extends react.Component {
  render() {
    return (
      <Main header="Page Not Found">
        <p>Sorry, the page you were looking for was not found.</p>
      </Main>
    );
  }
}
