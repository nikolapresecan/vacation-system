import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";
import NavSidebar from "./NavSidebar";
import "../styles/NavSidebar.css";

function SidebarWrapper({ children }) {
  const [open, setOpen] = useState(true);
  const [fullName, setFullName] = useState("Ime i Prezime");
  const [profilePicture, setProfilePicture] = useState(null);
  const navigate = useNavigate();

  const handleLogout = () => {
    localStorage.removeItem("jwt");
    navigate("/login");
  };

  useEffect(() => {
    const token = localStorage.getItem("jwt");

    if (!token) {
      handleLogout();
      return;
    }

    try {
      axios
        .get("http://localhost:8000/api/profil/all", {
          headers: { Authorization: `Bearer ${token}` },
        })
        .then((res) => {
          setFullName(`${res.data.firstName} ${res.data.lastName}`);
          setProfilePicture(res.data.profilePicture); 
        })
        .catch((err) => {
          console.error("Greška pri dohvaćanju profila:", err);
          handleLogout();
        });
    } catch (err) {
      console.error("Neispravan token:", err);
      handleLogout();
    }
  }, []);

  return (
    <div className="layout-wrapper">
      {!open && (
        <button
          className="navsidebar-toggle"
          onClick={() => setOpen(true)}
          aria-label="Otvori izbornik"
        >
          &#9776;
        </button>
      )}

      {open && (
        <NavSidebar
          fullName={fullName}
          profilePicture={profilePicture}
          onLogout={handleLogout}
          setOpen={setOpen}
        />
      )}

      <div
        className="layout-content"
        style={{
          marginLeft: open ? 260 : 0,
          transition: "margin-left 0.3s ease",
          padding: "20px",
        }}
      >
        {children}
      </div>
    </div>
  );
}

export default SidebarWrapper;
