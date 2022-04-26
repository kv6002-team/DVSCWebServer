import react from 'react';

import { defaultFetch } from '../utils/fetch';
import fileDownload from 'js-file-download';

import { makeAuthConsumer } from '../utils/components/Authentication';
import Main from '../standard/Main';
import { Container, Button, Table } from 'react-bootstrap';

/**
 * @author Scotty (w19019810)
 * @author William Taylor (w19009576)
 */
class AdditionalFiles extends react.Component {
  render() {
    return (
      <Main>
        <Container>
          <h1>Additional Forms</h1>
          <p className='mb-3'>Here is where you can download additional files related to DVSC.</p>

          <Table id="additional-files-downloads">
            <thead>
              <tr>
                <th>File</th>
                <th className="download-row">Download</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Monthly Report</td>
                <td className="download-row"><Button onClick={() => this.getFile('monthly-report')}>Download</Button></td>
              </tr>
              <tr>
                <td>Contract</td>
                <td className="download-row"><Button onClick={() => this.getFile('contract')}>Download</Button></td>
              </tr>
              <tr>
                <td>Monthly Check Sheet</td>
                <td className="download-row"><Button onClick={() => this.getFile('monthly-check-sheet')}>Download</Button></td>
              </tr>
              <tr>
                <td>Calibration Date Document</td>
                <td className="download-row"><Button onClick={() => this.getFile('calibration-date-document')}>Download</Button></td>
              </tr>
              <tr>
                <td>Defective Equipment Log</td>
                <td className="download-row"><Button onClick={() => this.getFile('defective-equipment-log')}>Download</Button></td>
              </tr>
              <tr>
                <td>Quality Control Sheet</td>
                <td className="download-row"><Button onClick={() => this.getFile('quality-control-sheet')}>Download</Button></td>
              </tr>
              <tr>
                <td>Tyre Depth Check Sheet</td>
                <td className="download-row"><Button onClick={() => this.getFile('tyre-depth-check-sheet')}>Download</Button></td>
              </tr>
            </tbody>
          </Table>
        </Container>
      </Main>
    )
  }

  getFile = async (filename) => {
    if (this.props.auth.token === null) return;

    const response = await defaultFetch(
      "GET", this.props.approot + `/api/files/${filename}`,
      { "Authorization" : `bearer ${this.props.auth.token.encoded}` }
    );
    fileDownload(response.blob(), `${filename}.pdf`);
  }
}
export default makeAuthConsumer(AdditionalFiles)
