import react from 'react';

import './image.css';

/**
 * Displays an image.
 * 
 * @typedef {object} ImageProps
 * @prop {string} src The URL for the image.
 * @prop {string} alt Alternative text for the image (describes the image in
 *   words).
 * @prop {Attribution=} attrib An optional attribution for the image.
 * 
 * @extends {react.Component<ImageProps>}
 * 
 * @author William Taylor (19009576)
 */
export class Image extends react.Component {
  render() {
    return (
      <figure>
        <img src={this.props.src} alt={this.props.alt} />
        {this.props.attrib}
      </figure>
    );
  }
}

/**
 * Displays an image attribution as a caption.
 * 
 * @typedef {object} AttributionProps
 * @prop {string} creator The name of the creator of the image.
 * @prop {string} source The name of the location the image was taken from, eg.
 *   "Unsplash".
 * @prop {string=} sourceURL The URL to the website/source the image was taken
 *   from, or to information about the creator.
 * 
 * @extends {react.Component<AttributionProps>}
 * 
 * @author William Taylor (19009576)
 */
export class Attribution extends react.Component {
  render() {
    return (
      <figcaption>
        By {this.props.creator}, from {this.props.sourceURL != null ? (
          <a href={this.props.sourceURL}>{this.props.source}</a>
        ) : (
          this.props.source
        )}
      </figcaption>
    );
  }
}
