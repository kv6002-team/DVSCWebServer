import { BrowserRouter, Routes, Route } from 'react-router-dom';

import { mapObj } from './utils/utils';
import Page from './standard/Page';
import { AuthProvider } from './utils/components/Authentication';

import Home from './pages/Home';
import NotFound from './pages/NotFound';

import './App.css';

/**
 * Manages the app's routes and authentication provider, and renders app.
 * 
 * @returns {import('react').ReactElement} The root element of the app.
 */
export default function App() {
  const approot = ""; // Server root
  const basename = approot + "";
  const localStoragePrefix = "DISSystemAssignment";

  const pages = {
    "/": {
      name: "Home",
      content: Home,
      nav: true,
      auth: false
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
        <Page approot={approot} pages={pages}>
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
