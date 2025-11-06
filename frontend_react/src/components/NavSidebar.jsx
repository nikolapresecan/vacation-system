import React from "react";
import { Link } from "react-router-dom";
import "../styles/NavSidebar.css";

function NavSidebar({ fullName, profilePicture, onLogout, setOpen }) {
  const defaultAvatar = "http://localhost:8000/uploads/profile_images/default.jpg";
  const avatarSrc = profilePicture || defaultAvatar;

  return (
    <div className="navsidebar-container">
      <div className="navsidebar-header">
        <button className="navsidebar-close" onClick={() => setOpen(false)}>
          &times;
        </button>
      </div>

      <div className="navsidebar-avatar">
        <img
          src={avatarSrc}
          alt="Profil"
          className="rounded-circle"
          style={{ width: "90px", height: "90px", objectFit: "cover" }}
          onError={(e) => {
            e.target.onerror = null;
            e.target.src = defaultAvatar;
          }}
        />
        <div className="navsidebar-fullname">{fullName}</div>
      </div>

      <nav className="navsidebar-links">
        <Link to="/profil" className="navsidebar-link text-center">
          Profil
        </Link>
        <a
          href="#"
          className="navsidebar-link navsidebar-logout text-center"
          onClick={(e) => {
            e.preventDefault();
            onLogout();
          }}
        >
          Odjava
        </a>
      </nav>
    </div>
  );
}

export default NavSidebar;
