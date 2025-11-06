import React, { useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import axios from "axios";

function ResetPasswordPage() {
  const { token } = useParams();
  const navigate = useNavigate();

  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      const res = await axios.post(
        `http://localhost:8000/reset-password/${token}`,
        new URLSearchParams({ password }),
        {
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
        }
      );

      setSuccess("Lozinka je uspješno promjenjena.");
      setTimeout(() => navigate("/"), 2000);
    } catch (err) {
      console.error(err);
      setError("Nevažeći ili istekao token.");
    }
  };

  return (
    <div
      className="d-flex align-items-center justify-content-center vh-100 bg-light"
    >
      <div className="card shadow p-4" style={{ maxWidth: "400px", width: "100%" }}>
        <h4 className="mb-4 text-center">Postavi novu lozinku</h4>

        {error && <div className="alert alert-danger">{error}</div>}
        {success ? (
          <div className="alert alert-success text-center">{success}</div>
        ) : (
          <form onSubmit={handleSubmit}>
            <div className="mb-3">
              <label htmlFor="password" className="form-label">
                Nova lozinka
              </label>
              <input
                type="password"
                id="password"
                className="form-control"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
            </div>
            <button type="submit" className="btn btn-success w-100">
              Promijeni lozinku
            </button>
          </form>
        )}
      </div>
    </div>
  );
}

export default ResetPasswordPage;
