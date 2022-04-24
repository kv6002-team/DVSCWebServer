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

  const resetPasswordRequiredRoute = "/reset-password-required";

  const pages = {
    "/": {
      name: "Home",
      content: Home,
      nav: true,
      auth: false
    },

    "/contact-us": {
      name: "Contact Us",
      content: ContactUs,
      nav: true,
      auth: false
    },

    "/update-instrument": {
      name: "Update Instrument",
      content: UpdateInstrument,
      nav: true,
      auth: true
    },

    "/legal/privacy-policy": {
      name: "Privacy Policy",
      content: PrivacyPolicy,
      nav: false,
      auth: false
    },

    [resetPasswordRequiredRoute]: {
      name: "Reset Password (Required)",
      content: ChangePassword,
      nav: false,
      auth: true
    },

    "*": {
      name: "Not Found",
      content: NotFound,
      nav: false,
      auth: false
    }
  };

  return (
    <BrowserRouter basename={basename}>
      <AuthProvider localStoragePrefix={localStoragePrefix}>
        <Page
          approot={approot}
          pages={pages}
          resetPasswordRequiredRoute={resetPasswordRequiredRoute}
        >
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
