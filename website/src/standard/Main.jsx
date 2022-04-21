import react from "react";

import { optionalEntries } from '../utils/utils';

/**
 * Renders page content in the standardised structure for the site.
 * 
 * @typedef {object} OwnProps
 * @prop {string} header The header text for this page.
 * 
 * @extends {react.Component<OwnProps & BasicComponent>}
 * 
 * @author William Taylor (19009576)
 */
export default class Main extends react.Component {
  render() {
    return (
      <main {...optionalEntries({
          id: this.props.id,
          className: this.props.className
      })}>
        {this.props.header != null ?
          <header>
            <h1>{this.props.header}</h1>
          </header> :
          null
        }

        {this.props.children}
      </main>
    );
  }
}
