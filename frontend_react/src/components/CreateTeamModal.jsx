import React, { useEffect, useRef, useState } from "react";
import axios from "axios";

function CreateTeamModal({ onClose, onSuccess }) {
  const [teamName, setTeamName] = useState("");
  const [error, setError] = useState("");
  const [isSubmitting, setIsSubmitting] = useState(false);
  const inputRef = useRef(null);
  const token = localStorage.getItem("jwt");

  useEffect(() => {
    inputRef.current?.focus();
    document.body.style.overflow = "hidden";

    const handleKeyDown = (e) => {
      if (e.key === "Escape") onClose();
    };
    document.addEventListener("keydown", handleKeyDown);

    return () => {
      document.body.style.overflow = "";
      document.removeEventListener("keydown", handleKeyDown);
    };
  }, [onClose]);

  const handleSubmit = (e) => {
    e.preventDefault();

    if (!teamName.trim()) {
      setError("Naziv tima je obavezan.");
      return;
    }

    setIsSubmitting(true);
    axios
      .post(
        "http://localhost:8000/api/admin/teams/new",
        { name: teamName },
        {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
          },
        }
      )
      .then(() => {
        setIsSubmitting(false);
        onSuccess();
      })
      .catch((err) => {
        setIsSubmitting(false);
        setError("Greška prilikom spremanja.");
        console.error(err);
      });
  };

  return (
    <>
      <div
        className="modal show fade"
        tabIndex="-1"
        style={{ display: "block" }}
        role="dialog"
        aria-modal="true"
      >
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content shadow rounded-3 border-0">
            <form onSubmit={handleSubmit}>
              <div className="modal-header border-0 bg-primary text-white rounded-top">
                <h5 className="modal-title">Kreiraj tim</h5>
                <button
                  type="button"
                  className="btn-close btn-close-white"
                  onClick={onClose}
                  aria-label="Zatvori"
                ></button>
              </div>
              <div className="modal-body">
                {error && <div className="alert alert-danger">{error}</div>}
                <div className="mb-3">
                  <label className="form-label">Naziv tima</label>
                  <input
                    type="text"
                    className="form-control"
                    value={teamName}
                    onChange={(e) => setTeamName(e.target.value)}
                    ref={inputRef}
                    disabled={isSubmitting}
                  />
                </div>
              </div>
              <div className="modal-footer border-0">
                <button
                  type="button"
                  className="btn btn-outline-secondary"
                  onClick={onClose}
                  disabled={isSubmitting}
                >
                  Zatvori
                </button>
                <button
                  type="submit"
                  className="btn btn-warning"
                  disabled={isSubmitting}
                >
                  {isSubmitting ? (
                    <span
                      className="spinner-border spinner-border-sm"
                      role="status"
                      aria-hidden="true"
                    ></span>
                  ) : (
                    "Dodaj tim"
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div className="modal-backdrop fade show"></div>
    </>
  );
}

export default CreateTeamModal;