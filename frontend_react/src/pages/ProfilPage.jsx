import React, { useEffect, useState, useRef } from "react";
import axios from "axios";
import SidebarWrapper from "../components/SidebarWrapper";
import TopNav from "../components/TopNav";
import TopNav2 from "../components/TopNav2";
import { jwtDecode } from "jwt-decode";

const DEFAULT_AVATAR_URL = "http://localhost:8000/uploads/profile_images/default.jpg";

function ProfilPage() {
  const [isAdmin, setIsAdmin] = useState(false);
  const [profile, setProfile] = useState(null);
  const [formData, setFormData] = useState({
    firstName: "",
    lastName: "",
    birthDate: "",
    profilePicture: null,
  });
  const [previewUrl, setPreviewUrl] = useState("");
  const [isEditing, setIsEditing] = useState(false);
  const [isResetLoading, setIsResetLoading] = useState(false);
  const [selectedFileName, setSelectedFileName] = useState("");
  const fileInputRef = useRef(null);

  const token = localStorage.getItem("jwt");

  useEffect(() => {
    if (!token) return;
    try {
      const decoded = jwtDecode(token);
      const roles = decoded.roles || [];
      setIsAdmin(roles.includes("ROLE_ADMIN"));
    } catch (err) {
      console.error("Greška kod dekodiranja tokena:", err);
    }
  }, [token]);

  useEffect(() => {
    if (!token) return;
    axios
      .get("http://localhost:8000/api/profil/all", {
        headers: { Authorization: `Bearer ${token}` },
      })
      .then((res) => {
        setProfile(res.data);
        setFormData({
          firstName: res.data.firstName || "",
          lastName: res.data.lastName || "",
          birthDate: (res.data.birthDate || "").slice(0, 10),
          profilePicture: null,
        });
        setPreviewUrl(res.data.profilePicture || DEFAULT_AVATAR_URL);
      })
      .catch((err) => console.error("Greška pri dohvaćanju profila:", err));
  }, [token]);

  const handleChange = (e) => {
    const { name, value, files } = e.target;
    if (name === "profilePicture") {
      const file = files?.[0];
      setFormData((prev) => ({ ...prev, profilePicture: file || null }));
      setSelectedFileName(file ? file.name : "");
      if (file) setPreviewUrl(URL.createObjectURL(file));
    } else {
      setFormData((prev) => ({ ...prev, [name]: value }));
    }
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const data = new FormData();
    data.append("firstName", formData.firstName);
    data.append("lastName", formData.lastName);
    data.append("birthDate", formData.birthDate || "");
    if (formData.profilePicture) data.append("profilePicture", formData.profilePicture);

    axios
      .post("http://localhost:8000/api/profil/edit", data, {
        headers: { Authorization: `Bearer ${token}`, "Content-Type": "multipart/form-data" },
      })
      .then(() => {
        alert("Profil ažuriran.");
        setIsEditing(false);
        if (!formData.profilePicture && profile?.profilePicture) {
          setPreviewUrl(`${profile.profilePicture}?v=${Date.now()}`);
        }
        
        setSelectedFileName("");
      })
      .catch((err) => {
        console.error("Greška pri spremanju profila:", err);
        alert("Došlo je do pogreške.");
      });
  };

  const handleSendResetLink = async () => {
    try {
      setIsResetLoading(true);
      await axios.post(
        "http://localhost:8000/api/security/reset-password",
        {},
        { headers: { Authorization: `Bearer ${token}` } }
      );
      alert("Poveznica za ponovno postavljanje lozinke je poslana na e-mail.");
    } catch (err) {
      console.error("Greška pri slanju linka za reset:", err);
      alert("Nije uspjelo slanje linka za reset lozinke.");
    } finally {
      setIsResetLoading(false);
    }
  };

  const triggerFileSelect = () => fileInputRef.current?.click();

  const avatarSrc = previewUrl || DEFAULT_AVATAR_URL;

  return (
    <>
      {isAdmin ? <TopNav /> : <TopNav2 suppressGuards />}
      <SidebarWrapper>
        <div className="container mt-5">
          <div className="card shadow-sm border-0">
            <div className="card-body p-4">
              <div className="d-flex justify-content-between align-items-center mb-4">
                <h2 className="fw-bold mb-0">Moj profil</h2>
                <button
                  type="button"
                  className="btn btn-outline-info btn-sm"
                  onClick={handleSendResetLink}
                  disabled={isResetLoading}
                >
                  {isResetLoading && (
                    <span
                      className="spinner-border spinner-border-sm me-2"
                      role="status"
                      aria-hidden="true"
                    ></span>
                  )}
                  {isResetLoading ? "Slanje…" : "Pošalji link za postavljanje nove lozinke"}
                </button>
              </div>

              {profile ? (
                <div className="row g-4 align-items-start">
                  <div className="col-lg-4">
                    <div className="text-center">
                      <img
                        src={avatarSrc}
                        alt="Profilna slika"
                        className="rounded-circle shadow-sm"
                        style={{ width: 140, height: 140, objectFit: "cover" }}
                        onError={(e) => {
                          e.currentTarget.onerror = null;
                          e.currentTarget.src = DEFAULT_AVATAR_URL;
                        }}
                      />

                      <div className="mt-3 d-flex flex-column align-items-center gap-2">
                        <div className="w-100 mx-auto text-center" style={{ maxWidth: 260 }}>
                          <input
                            ref={fileInputRef}
                            type="file"
                            name="profilePicture"
                            accept="image/*"
                            className="d-none"
                            onChange={handleChange}
                            disabled={!isEditing}
                          />

                          <div className="d-flex justify-content-center align-items-center gap-2 flex-wrap">
                            <button
                              type="button"
                              className="btn btn-outline-info btn-sm rounded-pill shadow-sm"
                              onClick={triggerFileSelect}
                              disabled={!isEditing}
                              title={isEditing ? "Odaberi novu sliku" : "Uključi uređivanje za promjenu slike"}
                            >
                              Promijeni profilnu sliku
                            </button>

                            <span
                              className="text-muted small"
                              style={{ maxWidth: 180, whiteSpace: "nowrap", overflow: "hidden", textOverflow: "ellipsis" }}
                            >
                              {selectedFileName || "Nije odabrana datoteka"}
                            </span>
                          </div>
                        </div>
                      </div>

                    </div>
                  </div>
                  
                  <div className="col-lg-8">
                    <div className="form-check form-switch mb-4">
                      <input
                        className="form-check-input"
                        type="checkbox"
                        id="editToggle"
                        checked={isEditing}
                        onChange={() => setIsEditing((prev) => !prev)}
                      />
                      <label className="form-check-label" htmlFor="editToggle">
                        Omogući uređivanje profila
                      </label>
                    </div>

                    <form onSubmit={handleSubmit}>
                      <div className="row">
                        <div className="col-md-6 mb-3">
                          <label className="form-label">Ime</label>
                          <input
                            type="text"
                            name="firstName"
                            value={formData.firstName}
                            onChange={handleChange}
                            className="form-control"
                            required
                            disabled={!isEditing}
                          />
                        </div>
                        <div className="col-md-6 mb-3">
                          <label className="form-label">Prezime</label>
                          <input
                            type="text"
                            name="lastName"
                            value={formData.lastName}
                            onChange={handleChange}
                            className="form-control"
                            required
                            disabled={!isEditing}
                          />
                        </div>
                        <div className="col-md-6 mb-3">
                          <label className="form-label">Datum rođenja</label>
                          <input
                            type="date"
                            name="birthDate"
                            value={formData.birthDate}
                            onChange={handleChange}
                            className="form-control"
                            disabled={!isEditing}
                          />
                        </div>
                      </div>

                      {isEditing && (
                        <button type="submit" className="btn btn-success mt-2">
                          Spremi promjene
                        </button>
                      )}
                    </form>
                  </div>
                </div>
              ) : (
                <p>Učitavanje profila...</p>
              )}
            </div>
          </div>
        </div>
      </SidebarWrapper>
    </>
  );
}

export default ProfilPage;
