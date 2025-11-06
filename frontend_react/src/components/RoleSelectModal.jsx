import React from "react";
import "../styles/LoginPage.css"; 

function RoleSelectModal({ show, roles, labels, onSelect, onClose }) {
  if (!show) return null;

  return (
    <div
      className="modal show d-block"
      tabIndex="-1"
      role="dialog"
      style={{ backgroundColor: "rgba(0,0,0,0.5)" }}
    >
      <div className="modal-dialog modal-dialog-centered">
        <div className="modal-content modal-glass border-0 shadow-lg rounded-4">
          <div className="modal-header modal-header-glass bg-light border-0 rounded-top">
            <h5 className="modal-title text-dark fw-bold">Odaberi tim i ulogu</h5>
          </div>
          <div className="modal-body">
            <p className="text-muted small mb-4">
              Imate više uloga i timova. Odaberite kako želite nastaviti:
            </p>

            <div className="role-grid">
              {roles.map(({ role, teamId, teamName }) => (
                <div
                  key={`${role}-${teamId}`}
                  className="role-card rounded-3 p-3 mb-3 shadow-sm border border-light-subtle bg-white hover-glow"
                  onClick={() => onSelect({ role, teamId })}
                  style={{ cursor: "pointer", transition: "0.3s ease" }}
                >
                  <div className="role-label text-dark fw-semibold">
                    {labels?.[role] ?? role}
                    <span className="text-muted"> – {teamName}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
          <div className="modal-footer border-0 d-flex justify-content-end">
            <button className="btn btn-outline-secondary" onClick={onClose}>
              Odustani
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default RoleSelectModal;
