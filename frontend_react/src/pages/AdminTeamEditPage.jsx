import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import SidebarWrapper from "../components/SidebarWrapper";
import TopNav from "../components/TopNav";
import axios from "axios";

function AdminTeamEditPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [team, setTeam] = useState(null);
  const [employees, setEmployees] = useState([]);
  const [teamLeaderId, setTeamLeaderId] = useState("");
  const [projectManagerId, setProjectManagerId] = useState("");
  const [selectedMembers, setSelectedMembers] = useState([]);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false); 
  const token = localStorage.getItem("jwt");

  useEffect(() => {
    const fetchData = async () => {
      try {
        const headers = { Authorization: `Bearer ${token}` };

        const [empRes, teamRes, allTeamsRes] = await Promise.all([
          axios.get("http://localhost:8000/api/admin/employees", { headers }),
          axios.get(`http://localhost:8000/api/admin/teams/team/${id}/members`, { headers }),
          axios.get("http://localhost:8000/api/admin/teams/all", { headers }),
        ]);

        const nonAdmins = empRes.data.filter((emp) => !emp.roles.includes("ROLE_ADMIN"));
        setEmployees(nonAdmins);

        setTeamLeaderId(teamRes.data.teamLeader?.toString() || "");
        setProjectManagerId(teamRes.data.projectManager?.toString() || "");
        setSelectedMembers(teamRes.data.members.map((id) => id.toString()));

        const found = allTeamsRes.data.find((t) => t.id.toString() === id);
        setTeam(found || null);
      } catch (err) {
        console.error(err);
        setError("Greška prilikom dohvaćanja podataka.");
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [id, token]);

  const handleMemberToggle = (empId) => {
    setSelectedMembers((prev) =>
      prev.includes(empId)
        ? prev.filter((id) => id !== empId)
        : [...prev, empId]
    );
  };

  const validate = () => {
    if (teamLeaderId && teamLeaderId === projectManagerId) {
      setError("Voditelj tima i Voditelj projekta ne mogu biti ista osoba.");
      return false;
    }
    if (
      selectedMembers.includes(teamLeaderId) ||
      selectedMembers.includes(projectManagerId)
    ) {
      setError("Voditelj tima i Voditelj projekta ne mogu biti članovi tima.");
      return false;
    }
    setError("");
    return true;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!validate()) return;

    setSaving(true);

    const formData = new FormData();
    formData.append("team_leader", teamLeaderId);
    formData.append("project_manager", projectManagerId);
    selectedMembers.forEach((id) => formData.append("members[]", id));

    try {
      await axios.post(
        `http://localhost:8000/api/admin/teams/team/${id}/add-members`,
        formData,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      navigate("/admin/teams");
    } catch (err) {
      console.error(err);
      setError("Greška prilikom spremanja.");
    } finally {
      setSaving(false); 
    }
  };

  if (loading) {
    return (
      <>
        <TopNav />
        <SidebarWrapper>
          <div className="container mt-5 text-center">
            <div className="spinner-border text-primary" role="status">
              <span className="visually-hidden">Učitavanje...</span>
            </div>
          </div>
        </SidebarWrapper>
      </>
    );
  }

  if (!team) return null;

  return (
    <>
      <TopNav />
      <SidebarWrapper>
        <div className="container mt-5">
          <div
            className={`card shadow-sm rounded-4 border-0 p-4 ${
              saving ? "opacity-75" : ""
            }`}
          >
            <h2 className="fw-bold mb-4">Uredi tim: {team.name}</h2>

            {error && <div className="alert alert-danger rounded-3">{error}</div>}

            <form
              onSubmit={handleSubmit}
              aria-busy={saving}
              aria-disabled={saving}
            >
              <div className="mb-3">
                <label className="form-label fw-semibold">Voditelj tima</label>
                <select
                  className="form-select rounded-3"
                  value={teamLeaderId}
                  onChange={(e) => setTeamLeaderId(e.target.value)}
                  disabled={saving}
                >
                  <option value="">-- Odaberi --</option>
                  {employees.map((emp) => (
                    <option key={emp.id} value={emp.id}>
                      {emp.firstName} {emp.lastName}
                    </option>
                  ))}
                </select>
              </div>

              <div className="mb-3">
                <label className="form-label fw-semibold">Voditelj projekta</label>
                <select
                  className="form-select rounded-3"
                  value={projectManagerId}
                  onChange={(e) => setProjectManagerId(e.target.value)}
                  disabled={saving}
                >
                  <option value="">-- Odaberi --</option>
                  {employees
                    .filter((e) => e.id.toString() !== teamLeaderId)
                    .map((emp) => (
                      <option key={emp.id} value={emp.id}>
                        {emp.firstName} {emp.lastName}
                      </option>
                    ))}
                </select>
              </div>

              <div className="mb-3">
                <label className="form-label fw-semibold">Članovi tima</label>
                <div className="ps-2">
                  {employees
                    .filter(
                      (e) =>
                        e.id.toString() !== teamLeaderId &&
                        e.id.toString() !== projectManagerId
                    )
                    .map((emp) => (
                      <div className="form-check" key={emp.id}>
                        <input
                          className="form-check-input"
                          type="checkbox"
                          checked={selectedMembers.includes(emp.id.toString())}
                          onChange={() => handleMemberToggle(emp.id.toString())}
                          id={`emp-${emp.id}`}
                          disabled={saving}
                        />
                        <label
                          className="form-check-label"
                          htmlFor={`emp-${emp.id}`}
                        >
                          {emp.firstName} {emp.lastName}
                        </label>
                      </div>
                    ))}
                </div>
              </div>

              <div className="d-flex justify-content-center">
                <button
                  type="submit"
                  className="btn btn-warning rounded-3 px-4 fw-semibold d-flex align-items-center"
                  disabled={saving}
                >
                  {saving && (
                    <span
                      className="spinner-border spinner-border-sm me-2"
                      role="status"
                      aria-hidden="true"
                    />
                  )}
                  <span aria-live="polite">
                    {saving ? "Spremanje..." : "Spremi"}
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </SidebarWrapper>
    </>
  );
}

export default AdminTeamEditPage;