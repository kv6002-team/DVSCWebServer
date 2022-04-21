import react from 'react';

import { optionalEntries } from '../utils/utils';

/**
 * Renders the standardised footer for the site.
 * 
 * @extends {react.Component<BasicComponent>}
 * 
 * @author William Taylor (19009576)
 */
export default class Footer extends react.Component {
  render() {
    return (
      <footer {...optionalEntries({
          id: this.props.id,
          className: this.props.className
      })}>
        <p>Copyright &copy; Donaldsons' Vehicle Specialist Consultancy.</p>
      </footer>
    );
  }
}
