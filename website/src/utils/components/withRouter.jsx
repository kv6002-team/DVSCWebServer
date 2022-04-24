import { useLocation, useNavigate, useParams } from "react-router-dom";

/**
 * A higher-order component that replaces the old withRouter() HOC from
 * react-router v5 with the newer tools (location, navigate, params).
 * 
 * From: https://stackoverflow.com/a/70223200
 * 
 * @param {react.Component} Component The component to wrap.
 * @returns {react.Component} The wrapped component.
 * 
 * @author Jack Li
 */
export default function withRouter(Component) {
  function ComponentWithRouterProp(props) {
    let location = useLocation();
    let navigate = useNavigate();
    let params = useParams();
    return (
      <Component
        {...props}
        router={{ location, navigate, params }}
      />
    );
  }

  return ComponentWithRouterProp;
}
