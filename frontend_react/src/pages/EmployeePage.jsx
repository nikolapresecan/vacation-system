import React, { useEffect, useState, useMemo } from "react";
import TopNav2 from "../components/TopNav2";
import SidebarWrapper from "../components/SidebarWrapper";
import { Link, useSearchParams } from "react-router-dom";
import axios from "axios";
import { format } from "date-fns";
import { hr } from "date-fns/locale";
import "../styles/EmployeePage.css";

function EmployeePage() {
  const [requests, setRequests] = useState([]);
  const [statuses, setStatuses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [teamName, setTeamName] = useState("");
  const [teamMembers, setTeamMembers] = useState([]);
  const [expandedRequestId, setExpandedRequestId] = useState(null);
  const [approvalDetails, setApprovalDetails] = useState({});
  const [statusFilter, setStatusFilter] = useState("ALL");

  const token = localStorage.getItem("jwt");
  const [searchParams] = useSearchParams();
  const selectedTeamId = searchParams.get("teamId");

  useEffect(() => {
    const fetchData = async () => {
      try {
        const statusRes = await axios.get("http://localhost:8000/api/status/all", {
          headers: { Authorization: `Bearer ${token}` },
        });
        setStatuses(statusRes.data);

        const requestRes = await axios.get("http://localhost:8000/api/employees/requests/myrequests", {
          headers: { Authorization: `Bearer ${token}` },
        });
        setRequests(requestRes.data);

        const teamMembersRes = await axios.get(`http://localhost:8000/api/teams/${selectedTeamId}/membersbyrole`, {
          headers: { Authorization: `Bearer ${token}` },
        });
        setTeamMembers(teamMembersRes.data);

        const filtered = requestRes.data.filter(
          (team) => String(team.teamId) === String(selectedTeamId)
        );
        if (filtered.length > 0 && filtered[0].teamName) {
          setTeamName(filtered[0].teamName);
        }
      } catch (err) {
        console.error("Greška pri dohvaćanju podataka:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [token, selectedTeamId]);

  const fetchApprovalDetails = async (requestId) => {
    try {
      const res = await axios.get(
        `http://localhost:8000/api/employees/requests/${requestId}/approval-status`,
        { headers: { Authorization: `Bearer ${token}` } }
      );
      setApprovalDetails((prev) => ({ ...prev, [requestId]: res.data }));
    } catch (err) {
      console.error("Greška kod dohvaćanja approval statusa:", err);
    }
  };

  const toggleDetails = (requestId) => {
    if (expandedRequestId === requestId) {
      setExpandedRequestId(null);
    } else {
      setExpandedRequestId(requestId);
      if (!approvalDetails[requestId]) {
        fetchApprovalDetails(requestId);
      }
    }
  };

  const statusTranslation = {
    CREATED: "Kreirano",
    APPROVED: "Odobreno",
    DECLINED: "Odbijeno",
    /* CANCELLED: "Otkazano",
    IN_PROGRESS: "U tijeku",
    TAKEN: "Iskorišteno",
    EXPIRED: "Isteklo", */
  };

  const STATUS_FILTERS = [
    "ALL",
    "CREATED",
    // "IN_PROGRESS",
    "APPROVED",
    "DECLINED",
    // "CANCELLED",
    // "TAKEN",
    // "EXPIRED",
  ];

  const getStatusNameById = (statusId) => {
    const status = statuses.find((s) => s.id === statusId);
    if (!status) return "Nepoznat";
    return statusTranslation[status.name] || status.name;
  };

  const getStatusCodeById = (statusId) => {
    const status = statuses.find((s) => s.id === statusId);
    return status ? status.name : "UNKNOWN";
  };

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

  const totalCounts = useMemo(() => {
    const counts = STATUS_FILTERS.reduce((acc, code) => ({ ...acc, [code]: 0 }), {});
    
    const groupsForTeam = requests.filter(
      (g) => !selectedTeamId || String(g.teamId) === String(selectedTeamId)
    );

    groupsForTeam.forEach((g) => {
      g.requests.forEach((req) => {
        const code = getStatusCodeById(req.statusId);
        if (counts.hasOwnProperty(code)) {
          counts[code] += 1;
        }
      });
    });
    
    counts.ALL = groupsForTeam.reduce((sum, g) => sum + g.requests.length, 0);
    return counts;
  }, [requests, statuses, selectedTeamId]);


  const filteredGroups = useMemo(() => {
    return requests
      .filter((team) => !selectedTeamId || String(team.teamId) === String(selectedTeamId))
      .map((team) => ({
        ...team,
        requests:
          statusFilter === "ALL"
            ? team.requests
            : team.requests.filter((r) => getStatusCodeById(r.statusId) === statusFilter),
      }));
  }, [requests, statuses, statusFilter, selectedTeamId]);

  return (
    <>
      <TopNav2 />
      <SidebarWrapper>
        <div className="container mt-4 employee-page">
          <h1 className="fw-bold mb-3">Moji zahtjevi za godišnji</h1>
          <Link
            className="btn btn-sm btn-warning"
            to={`/employees/requests/new?teamId=${selectedTeamId}`}
          >
            Kreiraj novi zahtjev
          </Link>
          <br />
          <br />
          <div className="row">
            <div className="col-lg-8">
              <div className="ms-auto w-100 w-lg-auto">
                <div
                  className="btn-group flex-wrap w-100"
                  role="group"
                  aria-label="Filteri po statusu"
                >
                  {STATUS_FILTERS.map((code) => (
                    <button
                      key={code}
                      type="button"
                      className={`btn btn-outline-success btn-sm filter-chip rounded-pill ${
                        statusFilter === code ? "active" : ""
                      }`}
                      onClick={() => setStatusFilter(code)}
                      title={
                        code === "ALL"
                          ? "Prikaži sve zahtjeve"
                          : `Prikaži: ${statusTranslation[code] || code}`
                      }
                      aria-pressed={statusFilter === code}
                    >
                      {code === "ALL" ? "Svi" : (statusTranslation[code] || code)}
                      <span className="badge text-dark ms-1">
                        {totalCounts[code] ?? 0}
                      </span>
                    </button>
                  ))}
                </div>
              </div>
              <br />
              {loading ? (
                <div className="text-center my-5">
                  <div className="spinner-border text-primary" role="status" />
                  <p className="mt-3">Učitavanje zahtjeva...</p>
                </div>
              ) : filteredGroups.every((g) => g.requests.length === 0) ? (
                <p className="text-muted">Nema zahtjeva za odabrani filter.</p>
              ) : (
                filteredGroups.map((team) => (
                  <div key={team.teamId} className="mb-5">
                    <div className="table-responsive shadow-sm rounded border">
                      <table className="table vacation-requests-table align-middle mb-0">
                        <thead>
                          <tr>
                            <th>Rbr.</th>
                            <th>Početak</th>
                            <th>Kraj</th>
                            <th>Broj dana</th>
                            <th>Status</th>
                            <th>Komentar</th>
                            <th>Kreirano</th>
                          </tr>
                        </thead>
                        <tbody>
                          {team.requests.map((req, i) => (
                            <React.Fragment key={req.id}>
                              <tr
                                onClick={() => toggleDetails(req.id)}
                                style={{ cursor: "pointer" }}
                                className={expandedRequestId === req.id ? "table-active" : ""}
                              >
                                <td>{i + 1}.</td>
                                <td>{formatDate(req.startDate)}</td>
                                <td>{formatDate(req.endDate)}</td>
                                <td>{req.numberOfDays}</td>
                                <td>
                                  <span className="badge bg-success">
                                    {getStatusNameById(req.statusId)}
                                  </span>
                                </td>
                                <td>{req.comment}</td>
                                <td>{formatDateTime(req.createdDate)}</td>
                              </tr>

                              {expandedRequestId === req.id && (
                                <tr>
                                  <td colSpan="7">
                                    <div className="bg-light p-3 rounded shadow-sm">
                                      <h6 className="fw-bold mb-2">Detalji odobravanja:</h6>
                                      {approvalDetails[req.id] ? (
                                        approvalDetails[req.id].length > 0 ? (
                                          <ul className="list-group list-group-flush">
                                            {approvalDetails[req.id].map((detail, index) => (
                                              <li
                                                key={index}
                                                className="list-group-item d-flex justify-content-between align-items-center"
                                              >
                                                <div>
                                                  <strong>{detail.role}</strong> – {detail.approver}
                                                  {detail.comment && <>: <em>{detail.comment}</em></>}
                                                </div>
                                                <span className="text-muted">
                                                  {statusTranslation[detail.status] || detail.status} • {formatDateTime(detail.approvedAt) || "nije još"}
                                                </span>
                                              </li>
                                            ))}
                                          </ul>
                                        ) : (
                                          <span className="text-muted">Odobravanja još nisu započetla.</span>
                                        )
                                      ) : (
                                        <span className="text-muted">Učitavanje podataka...</span>
                                      )}
                                    </div>
                                  </td>
                                </tr>
                              )}
                            </React.Fragment>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                ))
              )}
            </div>

            <div className="col-lg-4">
              <h5 className="fw-bold">Članovi tima: {teamName}</h5>
              <div className="accordion" id="teamMembersAccordion">
                <div className="accordion-item">
                  <h2 className="accordion-header" id="headingOne">
                    <button
                      className="accordion-button"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#collapseOne"
                      aria-expanded="true"
                      aria-controls="collapseOne"
                    >
                      Prikaži članove
                    </button>
                  </h2>
                  <div
                    id="collapseOne"
                    className="accordion-collapse collapse show"
                    aria-labelledby="headingOne"
                    data-bs-parent="#teamMembersAccordion"
                  >
                    <div className="accordion-body p-0">
                      <ul className="list-group rounded-0">
                        {teamMembers.map((member) => (
                          <li
                            key={member.id}
                            className="list-group-item d-flex justify-content-between align-items-center"
                          >
                            <span>{member.firstName} {member.lastName}</span>
                            <span className="text-muted">{member.role}</span>
                          </li>
                        ))}
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </SidebarWrapper>
    </>
  );
}

export default EmployeePage;
