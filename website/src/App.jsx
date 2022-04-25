import { BrowserRouter, Routes, Route } from 'react-router-dom';

import { mapObj } from './utils/utils';
import Page from './standard/Page';
import { AuthProvider } from './utils/components/Authentication';

import Home from './pages/Home';
import ContactUs from './pages/ContactUs';
import UpdateInstrument from './pages/UpdateInstrument';
import PrivacyPolicy from './pages/PrivacyPolicy';
import ChangePassword from './pages/ChangePassword';
import NotFound from './pages/NotFound';
import AdditionalFiles from './pages/AdditionalFiles'

import './App.css';

/**
 * Manages the app's routes and authentication provider, and renders app.
 * 
 * @returns {import('react').ReactElement} The root element of the app.
 */
export default function App() {
  const approot = "https://dvsc.services";
  const basename = "";
  const localStoragePrefix = "DISSystemAssignment";

  const authEndpoint = "/api/auth";
  const resetPasswordRequiredRoute = "/account/reset-password-required";

  const pages = {
    "/": {
      name: "Home",
      content: Home,
      onNav: true
    },

    "/contact-us": {
      name: "Contact Us",
      content: ContactUs,
      onNav: true
    },

    "/update-instrument": {
      name: "Update Instrument",
      content: UpdateInstrument,
      onNav: (auth) => auth !== null
    },

    "/additional-files": {
      name: "Additional Files",
      content: AdditionalFiles,
      onNav: (auth) => auth !== null
    },

    "/account/reset-password": {
      name: "Forgot Password",
      content: ChangePassword,
      onNav: (auth) => auth === null
    },
    "/account/change-password": {
      name: "Change Password",
      content: ChangePassword,
      onNav: (auth) => (
        auth !== null &&
        !auth.decoded.authorisations.includes("password_reset__password_auth")
      )
    },
    [resetPasswordRequiredRoute]: {
      name: "Reset Password (Required)",
      content: ChangePassword,
      // Put it on the navbar if a password reset is needed so that if the user
      // changes pages within the app, you can get back to the password reset
      // page without having to reload the whole app.
      onNav: (auth) => (
        auth !== null &&
        auth.decoded.authorisations.includes("password_reset__password_auth")
      )
    },

    "/legal/privacy-policy": {
      name: "Privacy Policy",
      content: PrivacyPolicy,
      onNav: false
    },

    "*": {
      name: "Not Found",
      content: NotFound,
      onNav: false
    }
  };

  return (
    <BrowserRouter basename={basename}>
      <AuthProvider
        localStoragePrefix={localStoragePrefix}
        approot={approot}
        authEndpoint={authEndpoint}
        resetPasswordRequiredRoute={resetPasswordRequiredRoute}
      >
        <Page pages={pages}>
          <Routes>
            {mapObj(
              pages,
              (path, pageInfo, i) => {
                const Content = pageInfo.content;
                return <Route
                  key={i}
                  path={path}
                  element={<Content approot={approot} />}
                />;
              },
              false
            )}
          </Routes>
        </Page>
      </AuthProvider>
    </BrowserRouter>
  );
}
