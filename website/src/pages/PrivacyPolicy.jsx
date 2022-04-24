import react from 'react';

import Main from '../standard/Main';

/**
 * The privacy policy page.
 * 
 * @extends {react.Component<APIConsumer>}
 * 
 * @author William Taylor (19009576)
 */
export default class PrivacyPolicy extends react.Component {
  render() {
    return (
      <Main header="Donaldsons' Vehicle Specialist Consultancy">
        <h2>DVSC Privacy Policy</h2>
        <h3>Contact Details</h3>
        <p>
          Name: Kevin Donaldson
          Email: kevindonaldsondvsc@outlook.co.uk
        </p>
        <h3>Types of personal information we collect</h3>
        <p>
          We currently collect and process the following information:
          <ul>
            <li>Personal identifiers, contacts and characteristics (For example, name and contact details)</li>
          </ul>
        </p>
        <h3>How we collect personal information and why we have it</h3>
        <p>
          We do not collect information indirectly and only process information directly provided by you.
          Most of the personal infomation we process is provided to us directly by you for one of the following reasons:
          <ul>
            <li>Processing garage information to track instrument details</li>
            <li>Storing personal information required to contact you if required</li>
          </ul>
          We use the personal infromation you have provided us to keep track of garage information, allowing us to perform our job
          in ensuring that garage quality is sufficient according to a standards board.

          We will not share any of your information provided with any other 3rd-party organisation or individual.

          Under the UK General Data Protecection Regulation (UK GDPR), the lawfull bases we rely on for processing this information are:
          <ul>
            <li>Your consent. You are able to remove your conset at any time. You can do this by contacting Kevin Donaldson: kevindonaldsondvsc@outlook.com.</li>
            <li>We have a contractual obligation.</li>
            <li>We have legitimate interest.</li>
          </ul>
        </p>
        <h3>How we store your information</h3>
        <p>
          Your information is stored securely on an offsite server located in the UK.
          We keep your information for as long as you remain a customer.
          Data is deleted by permenantly removing it from our service database immediately after you are no longer a customer.
        </p>
        <h3>Your data protection rights</h3>
        <p>
          Under data protection law, you have rights including:
          <ul>
            <li>
              <strong>Your right of access</strong>
              <p>You have the right to ask us for copies of your personal information.</p>
            </li>
            <li>
              <strong>Your right to rectification</strong>
              <p>You have the right to ask us to rectify personal information you think is inaccurate. You also have the right to ask us to complete information you think is incomplete.</p>
            </li>
            <li>
              <strong>Your right to erasure</strong>
              <p>You have the right to ask us to erase your personal information in certain circumstances.</p>
            </li>
            <li>
              <strong>Your right to restriction of processing</strong>
              <p>You have the right to ask us to restrict the processing of your personal information in certain circumstances.</p>
            </li>
            <li>
              <strong>Your right to object to processing</strong>
              <p>You have the the right to object to the processing of your personal information in certain circumstances.</p>
            </li>
            <li>
              <strong>Your right to data portability</strong>
              <p>
                You have the right to ask that we transfer the personal information you gave us to another organisation, or to you, in certain circumstances.
                You are not required to pay any charge for exercising your rights. If you make a request, we have one month to respond to you.
              </p>
            </li>
          </ul>
          Please contact us at: kevindonaldsondvsc@outlook.com or +44 07493904628
        </p>
        <h3>How to complain</h3>
        <p>
          If you have any concerns about our use of your personal information, you can make a complaint to us at kevindonaldsondvsc@outlook.com or +44 07493904628.
          You can also complain to the ICO if you are unhappy with how we have used your data.
          The ICO's address:            
          Information Commissioner's Office
          Wycliffe House
          Water Lane
          Wilmslow
          Cheshire
          SK9 5AF

          Helpline number: 0303 123 1113
          ICO website: https://www.ico.org.uk
        </p>
      </Main>
    );
  }
}
