import react from 'react';
import { Container } from 'react-bootstrap/lib/Tab';
import { makeAuthConsumer } from '../utils/components/Authentication';

/**
 * @author Scotty (w19019810)
 */
class AdditionalFiles extends react.Component {
  constructor(props){
  }

  render(){
    return (
      <Main>
        <Container>
          <h1>Additional Forms</h1>
          <p className='mb-3'>Here is where you can download additional files related to DVSC</p>
          <Table>
            <thead>
              <tr>
                <th>File</th>
                <th>Download</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Monthly Report</td>
                <td><Button onClick={this.getFile('monthlyreport')}>Download</Button></td>
              </tr>
              <tr>
                <td>Contract</td>
                <td><Button onClick={this.getFile('contract')}>Download</Button></td>
              </tr>
              <tr>
                <td>Monthly Check Sheet</td>
                <td><Button onClick={this.getFile('monthlychecksheet')}>Download</Button></td>
              </tr>
              <tr>
                <td>Calibration Date Document</td>
                <td><Button onClick={this.getFile('calibrationdatedocument')}>Download</Button></td>
              </tr>
              <tr>
                <td>Defective Equipment Log</td>
                <td><Button onClick={this.getFile('defectiveequipmentlog')}>Download</Button></td>
              </tr>
              <tr>
                <td>Quality Control Sheet</td>
                <td><Button onClick={this.getFile('qualitycontrolsheet')}>Download</Button></td>
              </tr>
              <tr>
                <td>Tyre Depth Check Sheet</td>
                <td><Button onClick={this.getFile('tyredepthchecksheet')}>Download</Button></td>
              </tr>
            </tbody>
          </Table>
        </Container>
      </Main>
    )
  }

  getFile = filename => {
    //send request to server to get file... then figure out a way to download it I guess...
  }
}
export default makeAuthConsumer(AdditionalFiles)