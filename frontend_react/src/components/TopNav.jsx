import React from "react";
import { NavLink } from "react-router-dom";
import "../styles/TopNav.css";

function TopNav() {
  return (
    <nav className="navbar navbar-expand custom-topnav sticky-top">
      <div className="container justify-content-center">
        <ul className="navbar-nav flex-row gap-4">
          <li className="nav-item">
            <NavLink className="nav-link fw-bold fs-5" to="/admin/dashboard">
              Početna
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink className="nav-link fw-bold fs-5" to="/admin/employees">
              Zaposlenici
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink className="nav-link fw-bold fs-5" to="/admin/teams">
              Timovi
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink className="nav-link fw-bold fs-5" to="/admin/holidays">
              Praznici
            </NavLink>
          </li>
        </ul>
      </div>
    </nav>
  );
}

export default TopNav;
