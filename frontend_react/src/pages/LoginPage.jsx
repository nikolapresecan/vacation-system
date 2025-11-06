import { useState } from "react";
import { useNavigate } from "react-router-dom";
import axios from "axios";
import { jwtDecode } from "jwt-decode";
import RoleSelectModal from "../components/RoleSelectModal";
import "../styles/LoginPage.css";

const ROLE_TO_ROUTE = {
  "ROLE_ADMIN": "/admin/dashboard",
  "ROLE_EMPLOYEE": "/employees/dashboard",
  "ROLE_TEAM LEADER": "/management/dashboard",
  "ROLE_PROJECT MANAGER": "/management/dashboard",
};

const ROLE_LABELS = {
  "ROLE_ADMIN": "Administrator",
  "ROLE_EMPLOYEE": "Zaposlenik",
  "ROLE_TEAM LEADER": "Voditelj tima",
  "ROLE_PROJECT MANAGER": "Voditelj projekta",
};

function LoginPage() {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [roleOptions, setRoleOptions] = useState([]);
  const [showModal, setShowModal] = useState(false);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const response = await axios.post("http://localhost:8000/api/login_check", {
        username,
        password,
      });

      const data = response.data;

      if (data.token) {
        localStorage.setItem("jwt", data.token);
        const payload = jwtDecode(data.token);
        const roles = payload.roles || [];
        const teamRoles = payload.teamRoles || [];
        
        if (roles.includes("ROLE_ADMIN")) {
          navigate("/admin/dashboard");
          return;
        }

        if (teamRoles.length === 1) {
          const { role, teamId } = teamRoles[0];
          navigate(`${ROLE_TO_ROUTE[role]}?teamId=${teamId}`);
          return;
        }
        
        if (teamRoles.length > 1) {
          setRoleOptions(teamRoles);
          setShowModal(true);
          return;
        }
        
        setError("Nemate pravo pristupa nijednom timu.");
      } else {
        setError("Neispravno korisničko ime ili lozinka!");
      }
    } catch (err) {
      setError("Greška u komunikaciji s backendom!");
    } finally {
      setLoading(false);
    }
  };


  const handleRoleSelect = ({ role, teamId }) => {
    setShowModal(false);
    navigate(`${ROLE_TO_ROUTE[role]}?teamId=${teamId}`);
  };

  return (
    <div className="login-page">
      <div className={`login-card ${loading ? "loading" : ""}`}>
        <h2 className="mb-4 text-center">Prijava</h2>
        <form onSubmit={handleSubmit}>
          <div className="mb-3">
            <label className="form-label">Korisničko ime</label>
            <input
              type="text"
              className="form-control"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              required
              autoFocus
            />
          </div>
          <div className="mb-3">
            <label className="form-label">Lozinka</label>
            <input
              type="password"
              className="form-control"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>
          {error && <div className="alert alert-danger">{error}</div>}

          <button type="submit" className="btn btn-primary w-100" disabled={loading}>
            {loading && (
              <span
                className="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>
            )}
            {loading ? "Prijavljivanje..." : "Prijava"}
          </button>
        </form>
      </div>

      <RoleSelectModal
        show={showModal}
        roles={roleOptions}
        labels={ROLE_LABELS}
        onSelect={handleRoleSelect}
        onClose={() => setShowModal(false)}
      />
    </div>
  );
}

export default LoginPage;
