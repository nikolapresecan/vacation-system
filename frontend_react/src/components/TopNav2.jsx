import React, { useEffect, useState, useRef } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import axios from "axios";
import "../styles/TopNav.css";

const ROLE_LABELS = {
  "ROLE_EMPLOYEE": "Zaposlenik",
  "ROLE_TEAM LEADER": "Voditelj tima",
  "ROLE_PROJECT MANAGER": "Voditelj projekta",
};

const ROLE_TO_ROUTE = {
  "ROLE_EMPLOYEE": "/employees/dashboard",
  "ROLE_TEAM LEADER": "/management/dashboard",
  "ROLE_PROJECT MANAGER": "/management/dashboard",
};

function TopNav2() {
  const [teamOptions, setTeamOptions] = useState([]);
  const [selectedTeamId, setSelectedTeamId] = useState(null);
  const [danigod, setDanigod] = useState("");
  const token = localStorage.getItem("jwt");
  const navigate = useNavigate();
  const location = useLocation();
  const ran = useRef(false);

  const isProfile = location.pathname === "/profil"; 

  useEffect(() => {
    const fetchProfile = async () => {
      try {
        const res = await axios.get("http://localhost:8000/api/profil/all", {
          headers: { Authorization: `Bearer ${token}` },
        });

        const teams = res.data.teams || [];
        setDanigod(res.data.vacationDays || "");

        const options = teams.map((t) => ({
          id: t.id,
          role: `ROLE_${t.role.toUpperCase()}`, 
          label: `${ROLE_LABELS[`ROLE_${t.role.toUpperCase()}`] || t.role} – ${t.name}`,
        }));

        setTeamOptions(options);

        const urlParams = new URLSearchParams(location.search);
        const currentTeamId = urlParams.get("teamId");

        if (currentTeamId) {
          setSelectedTeamId(currentTeamId);
          return;
        }

        if (options.length === 0) return;
        
        setSelectedTeamId(options[0].id);

        if (isProfile) return; 

        const route = ROLE_TO_ROUTE[options[0].role] || "/";
        const target = `${route}?teamId=${options[0].id}`;
        const current = `${location.pathname}${location.search}`;

        if (!ran.current && current !== target) {
          ran.current = true; 
          navigate(target, { replace: true });
        }
      } catch (err) {
        console.error("Greška kod dohvaćanja profila:", err);
      }
    };

    fetchProfile();
  }, [location.pathname, location.search, navigate, token, isProfile]);

  const handleTeamChange = (e) => {
    const newTeamId = e.target.value;
    setSelectedTeamId(newTeamId);

    const selected = teamOptions.find((opt) => String(opt.id) === String(newTeamId));
    const route = ROLE_TO_ROUTE[selected?.role] || "/";

    navigate(`${route}?teamId=${newTeamId}`);
  };

  return (
    <nav className="navbar navbar-expand custom-topnav sticky-top">
      <div className="container justify-content-between px-3">
        <span className="navbar-brand text-dark">
          Preostalo dana godišnjeg: {danigod}/20
        </span>

        {teamOptions.length > 1 && (
          <div className="d-flex align-items-center ms-auto gap-2">
            <span className="text-dark fw-light">Promijeni tim:</span>
            <select
              className="team-switch-select"
              value={selectedTeamId || ""}
              onChange={handleTeamChange}
            >
              {teamOptions.map((opt) => (
                <option key={opt.id} value={opt.id}>
                  {opt.label}
                </option>
              ))}
            </select>
          </div>
        )}
      </div>
    </nav>
  );
}

export default TopNav2;
