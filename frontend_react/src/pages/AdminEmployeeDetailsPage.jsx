import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import axios from "axios";
import { format } from "date-fns";
import { hr } from "date-fns/locale";
import SidebarWrapper from "../components/SidebarWrapper";
import TopNav from "../components/TopNav";

const ROLE_LABELS = {
  ROLE_ADMIN: "Administrator",
  ROLE_EMPLOYEE: "Zaposlenik",
  "ROLE_TEAM LEADER": "Voditelj tima",
  "ROLE_PROJECT MANAGER": "Voditelj projekta",
};

const DEFAULT_AVATAR_URL = "http://localhost:8000/uploads/profile_images/default.jpg";

function AdminEmployeeDetailsPage() {
  const { id } = useParams();
  const token = localStorage.getItem("jwt");
  const [employee, setEmployee] = useState(null);
  const [loading, setLoading] = useState(true);
  const [approvedRequests, setApprovedRequests] = useState([]);
  const [loadingApproved, setLoadingApproved] = useState(true);
  const [openingId, setOpeningId] = useState(null); 

  useEffect(() => {
    const fetchEmployee = async () => {
      try {
        const response = await axios.get(
          `http://localhost:8000/api/admin/employees/employee/${id}`,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        setEmployee(response.data);
      } catch (err) {
        console.error("Greška:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchEmployee();
  }, [id]);

  useEffect(() => {
    if (!employee) return;

    const fetchApproved = async () => {
      setLoadingApproved(true);
      try {
        const res = await axios.get(
          `http://localhost:8000/api/admin/employees/employee/${id}/approvedRequests`,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        setApprovedRequests(res.data);
      } catch (err) {
        console.error("Ne mogu dohvatiti odobrene zahtjeve.", err);
      } finally {
        setLoadingApproved(false);
      }
    };

    fetchApproved();
  }, [employee]);

  const formatDate = (dateStr) => {
    try {
      return format(new Date(dateStr), "dd.MM.yyyy.", { locale: hr });
    } catch {
      return dateStr;
    }
  };

  const formatDateTime = (dateStr) => {
    try {
      return format(new Date(dateStr), "dd.MM.yyyy. HH:mm", { locale: hr });
    } catch {
      return dateStr;
    }
  };

  const formatRoles = (roles = []) => {
    return roles.map((r, i) => (
      <span key={i} className="badge bg-primary me-1">
        {ROLE_LABELS[r] || r}
      </span>
    ));
  };

  const formatTeamRole = (role) => {
    if (!role) return "";
    return ROLE_LABELS[`ROLE_${role.toUpperCase()}`] || role;
    };

  if (!employee && !loading) {
    return (
      <div className="container mt-5">
        <div className="alert alert-danger">Zaposlenik nije pronađen.</div>
      </div>
    );
  }

  const getFullImageUrl = (val) => {
    if (!val) return DEFAULT_AVATAR_URL;
    if (typeof val !== "string") return DEFAULT_AVATAR_URL;

    if (val.startsWith("http")) return val;
    if (val.startsWith("/uploads/")) return `http://localhost:8000${val}`;

    return `http://localhost:8000/uploads/profile_pictures/${val}`;
  };

  const openPdfInline = async (approvalStatusId) => {
    try {
      setOpeningId(approvalStatusId);

      const res = await axios.get(`http://localhost:8000/api/admin/download/by-approval/${approvalStatusId}?inline=1`,
        {
          headers: { Authorization: `Bearer ${token}` },
          responseType: "blob",
        }
      );
      const pdfUrl = window.URL.createObjectURL(res.data);
      const win = window.open(pdfUrl, "_blank");
      if (!win) {
        const iframe = document.createElement("iframe");
        iframe.style.display = "none";
        iframe.src = pdfUrl;
        document.body.appendChild(iframe);
      }
      setTimeout(() => URL.revokeObjectURL(pdfUrl), 30000);
    } catch (e) {
      console.error("Ne mogu otvoriti rješenje:", e);
      alert("Ne mogu otvoriti rješenje.");
    } finally {
      setOpeningId(null);
    }
  };

  return (
    <>
      <TopNav />
      <SidebarWrapper>
        <div className="container mt-4">
          <div className="card shadow-sm rounded-4 border-0 p-4">
            {loading ? (
              <div className="d-flex justify-content-center align-items-center py-5">
                <div className="spinner-border text-primary" role="status" />
              </div>
            ) : (
              <>
                <div className="d-flex align-items-center mb-4">
                  <img
                    src={getFullImageUrl(employee?.profilePicture)}
                    alt="Profilna slika"
                    className="rounded-circle border"
                    width="120"
                    height="120"
                    style={{ objectFit: "cover", marginRight: "20px" }}
                    onError={(e) => { e.currentTarget.onerror = null; e.currentTarget.src = DEFAULT_AVATAR_URL; }}
                  />
                  <h2 className="text-primary mb-0">
                    {employee.firstName} {employee.lastName} ({employee.oib})
                  </h2>
                </div>

                <div className="row g-4">
                  <div className="col-md-6">
                    <p><strong>Korisničko ime:</strong> {employee.username}</p>
                    <p><strong>Email:</strong> {employee.email}</p>
                    <p><strong>Datum rođenja:</strong> {formatDate(employee.birthDate)}</p>
                    <p><strong>Datum zaposlenja:</strong> {formatDate(employee.employmentDate)}</p>
                    <p><strong>Godine staža:</strong> {employee.serviceYears}</p>
                    <p><strong>Broj dana godišnjeg:</strong> {employee.vacationDays}</p>
                    <p><strong>Pozicija:</strong> {employee.job?.name || "Nema"}</p>
                  </div>
                  <div className="col-md-6">
                    <p><strong>Uloge:</strong><br />{formatRoles(employee.roles)}</p>
                    <p><strong>Timovi:</strong></p>
                    <ul className="list-group">
                      {employee.teams?.length > 0 ? (
                        employee.teams.map((team, index) => (
                          <li key={index} className="list-group-item d-flex justify-content-between align-items-center">
                            {team.team}
                            <span className="badge bg-info text-dark">
                              {formatTeamRole(team.role)}
                            </span>
                          </li>
                        ))
                      ) : (
                        <li className="list-group-item">Nije član nijednog tima</li>
                      )}
                    </ul>
                  </div>
                </div>

                <hr className="my-4" />
                <h4 className="mb-3">Odobreni zahtjevi</h4>

                {loadingApproved ? (
                  <div className="d-flex align-items-center gap-2">
                    <div className="spinner-border text-primary" role="status" />
                    <span>Učitavanje odobrenih zahtjeva…</span>
                  </div>
                ) : approvedRequests.length === 0 ? (
                  <div className="alert alert-info">
                    Nema odobrenih zahtjeva za ovog zaposlenika.
                  </div>
                ) : (
                  <div className="table-responsive">
                    <table className="table table-hover align-middle text-center">
                      <thead className="table-primary">
                        <tr>
                          <th>#</th>
                          <th>Razdoblje</th>
                          <th>Broj dana</th>
                          <th>Tim</th>
                          <th>Komentar</th>
                          <th>Kreirano</th>
                          <th>Rješenje</th>
                        </tr>
                      </thead>
                      <tbody>
                        {approvedRequests.map((r, idx) => (
                          <tr key={r.id}>
                            <td>{idx + 1}</td>
                            <td>{formatDate(r.startDate)} — {formatDate(r.endDate)}</td>
                            <td>{r.numberOfDays}</td>
                            <td>{r.team}</td>
                            <td style={{ maxWidth: 280 }}>
                              {r.comment || <span className="text-muted">—</span>}
                            </td>
                            <td>{formatDateTime(r.createdDate)}</td>
                            <td>
                              {r.approvalStatusId && r.solutionUrl ? (
                                <button
                                  className="btn btn-outline-primary btn-sm"
                                  onClick={() => openPdfInline(r.approvalStatusId)}
                                  disabled={openingId === r.approvalStatusId}
                                  aria-busy={openingId === r.approvalStatusId}
                                >
                                  {openingId === r.approvalStatusId ? (
                                    <>
                                      <span
                                        className="spinner-border spinner-border-sm me-2"
                                        role="status"
                                        aria-hidden="true"
                                      />
                                      Otvaranje…
                                    </>
                                  ) : (
                                    "Otvori rješenje"
                                  )}
                                </button>
                              ) : (
                                <span className="badge bg-info">Nema rješenja</span>
                              )}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                )}
              </>
            )}
          </div>
        </div>
      </SidebarWrapper>
    </>
  );
}

export default AdminEmployeeDetailsPage;
