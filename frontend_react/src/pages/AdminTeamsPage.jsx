import React, { useEffect, useState } from "react";
import SidebarWrapper from "../components/SidebarWrapper";
import TopNav from "../components/TopNav";
import CreateTeamModal from "../components/CreateTeamModal";
import { useNavigate, Link } from "react-router-dom";
import axios from "axios";

function AdminTeamsPage() {
  const [teams, setTeams] = useState([]);
  const [showModal, setShowModal] = useState(false);
  const [loading, setLoading] = useState(true);
  const token = localStorage.getItem("jwt");

  const fetchTeams = async () => {
    try {
      const response = await axios.get("http://localhost:8000/api/admin/teams/all", {
        headers: { Authorization: `Bearer ${token}` },
      });
      setTeams(response.data);
    } catch (err) {
      console.error("Greška prilikom dohvaćanja timova:", err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    setLoading(true);
    fetchTeams();
  }, [showModal]);

  return (
    <>
      <TopNav />
      <SidebarWrapper>
        <div className="container mt-4">
          <h1 className="fw-bold mb-0">Timovi</h1>
          <br />
          <Link
            className="btn btn-sm btn-warning"
            onClick={() => setShowModal(true)}
          >
            Dodaj novi tim
          </Link>
          <br /><br />

          <div className="row">
            {loading ? (
              <div className="d-flex justify-content-center align-items-center" style={{ minHeight: "200px" }}>
                <div className="spinner-border text-primary" role="status">
                  <span className="visually-hidden">Učitavanje...</span>
                </div>
              </div>
            ) : (
              teams.length > 0 ? (
                teams.map((team) => (
                  <div className="col-md-4 mb-4" key={team.id}>
                    <div className="card shadow-sm border-1 rounded-3">
                      <div className="card-body d-flex flex-column">
                        <h5 className="card-title fw-semibold mb-3">{team.name}</h5>

                        <div className="mb-2">
                          <strong>Voditelj tima:</strong>
                          <div>{team.teamLeader?.fullName || "-"}</div>
                        </div>

                        <div className="mb-2">
                          <strong>Voditelj projekata:</strong>
                          <div>{team.projectManager?.fullName || "-"}</div>
                        </div>

                        <div className="mb-2">
                          <strong>Članovi:</strong>
                          {team.members?.length > 0 ? (
                            <ul className="ps-3 mb-0">
                              {team.members.map((member) => (
                                <li key={member.id}>{member.fullName}</li>
                              ))}
                            </ul>
                          ) : (
                            <div>-</div>
                          )}
                        </div>

                        <Link
                          className="btn btn-outline-warning mt-auto align-self-start"
                          to={`/admin/teams/team/${team.id}/edit`}
                        >
                          Uredi članove
                        </Link>
                      </div>
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center">Nema dostupnih timova.</div>
              )
            )}
          </div>

          {showModal && (
            <CreateTeamModal
              onClose={() => setShowModal(false)}
              onSuccess={() => setShowModal(false)}
            />
          )}
        </div>
      </SidebarWrapper>
    </>
  );
}

export default AdminTeamsPage;