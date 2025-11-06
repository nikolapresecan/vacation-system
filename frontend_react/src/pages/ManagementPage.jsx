import React, { useEffect, useState } from "react";
import SidebarWrapper from "../components/SidebarWrapper";
import TopNav2 from "../components/TopNav2";
import ApprovalModal from "../components/ApprovalModal";
import axios from "axios";
import { useSearchParams } from "react-router-dom";
import { format } from "date-fns";
import { hr } from "date-fns/locale";
import "../styles/ManagementPage.css";
import TeamVacationCalendar from "../components/TeamVacationCalendar";

function ManagementPage() {
  const [requests, setRequests] = useState([]);
  const [approvedRequests, setApprovedRequests] = useState([]);
  const [teamMembers, setTeamMembers] = useState([]);
  const [teamName, setTeamName] = useState("");
  const [showModal, setShowModal] = useState(false);
  const [selectedRequestId, setSelectedRequestId] = useState(null);
  const [selectedStatus, setSelectedStatus] = useState("");
  const [comment, setComment] = useState("");
  const [loadingCreated, setLoadingCreated] = useState(true);
  const [loadingApproved, setLoadingApproved] = useState(true);
  const [modalLoading, setModalLoading] = useState(false);
  const [approvedSearch, setApprovedSearch] = useState("");
  const [openingId, setOpeningId] = useState(null);

  const token = localStorage.getItem("jwt");
  const [searchParams] = useSearchParams();
  const selectedTeamId = searchParams.get("teamId");

  useEffect(() => {
    if (selectedTeamId) {
      fetchRequests();
      fetchApprovedRequests();
      fetchTeamMembers();
      fetchTeamName();
    }
  }, [selectedTeamId]);

  const fetchRequests = async () => {
    setLoadingCreated(true);
    try {
      const res = await axios.get("http://localhost:8000/api/management/requests/created", {
        headers: { Authorization: `Bearer ${token}` },
        params: { teamId: selectedTeamId },
      });
      setRequests(res.data);
    } catch (err) {
      console.error("Greška pri dohvaćanju zahtjeva:", err);
    } finally {
      setLoadingCreated(false);
    }
  };

  const fetchApprovedRequests = async () => {
    setLoadingApproved(true);
    try {
      const res = await axios.get("http://localhost:8000/api/management/requests/approved", {
        headers: { Authorization: `Bearer ${token}` },
      });
      setApprovedRequests(res.data);
    } catch (err) {
      console.error("Greška pri dohvaćanju odobrenih zahtjeva:", err);
    } finally {
      setLoadingApproved(false);
    }
  };

  const fetchTeamMembers = async () => {
    try {
      const res = await axios.get(`http://localhost:8000/api/teams/${selectedTeamId}/membersbyrole`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setTeamMembers(res.data);
    } catch (err) {
      console.error("Greška pri dohvaćanju članova tima:", err);
    }
  };

  const fetchTeamName = async () => {
    try {
      const res = await axios.get(`http://localhost:8000/api/teams/${selectedTeamId}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      setTeamName(res.data.name);
    } catch (err) {
      console.error("Greška pri dohvaćanju naziva tima:", err);
    }
  };

  const handleActionClick = (id, status) => {
    setSelectedRequestId(id);
    setSelectedStatus(status);
    setComment("");
    setShowModal(true);
  };

  const handleSubmitApproval = async () => {
    try {
      setModalLoading(true);

      await axios.post("http://localhost:8000/api/management/approve",
        { id: selectedRequestId, status: selectedStatus, comment },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      setShowModal(false);
      fetchRequests();
      fetchApprovedRequests();
    } catch (err) {
      console.error("Greška pri slanju odobrenja:", err);
    } finally {
      setModalLoading(false); 
    }
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

  const normalize = (s) =>
    (s || "")
      .toLocaleLowerCase("hr-HR")
      .normalize("NFD")
      .replace(/\p{Diacritic}/gu, "");

  const approvedForSelectedTeam = approvedRequests.filter(
    (req) => String(req.teamId) === String(selectedTeamId)
  );

  const approvedFiltered = approvedForSelectedTeam.filter((req) =>
    normalize(req.employee).includes(normalize(approvedSearch))
  );

  const openPdfInline = async (approvalStatusId) => {
    try {
      setOpeningId(approvalStatusId);

      const res = await axios.get(`http://localhost:8000/api/management/download/by-approval/${approvalStatusId}?inline=1`,
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
      <TopNav2 />
      <SidebarWrapper>
        <div className="container mt-4">
          <div className="row">
            <div className="col-lg-8">
              <h1 className="fw-bold mb-4">Zahtjevi za godišnji</h1>
              {loadingCreated ? (
                <div className="text-center my-4">
                  <div className="spinner-border text-primary" role="status" />
                  <p className="mt-2">Učitavanje zahtjeva...</p>
                </div>
              ) : requests.length === 0 ? (
                <p className="text-muted">Nema zahtjeva u statusu KREIRANO.</p>
              ) : (
                <div className="table-responsive shadow-sm rounded border">
                  <table className="table table-hover align-middle text-center vacation-table">
                    <thead className="table-primary text-dark">
                      <tr>
                        <th>Zaposlenik</th>
                        <th>Od</th>
                        <th>Do</th>
                        <th>Dana</th>
                        <th>Komentar</th>
                        <th>Kreirano</th>
                        <th></th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      {requests.map((req) => (
                        <tr key={req.id}>
                          <td>{req.employee}</td>
                          <td>{formatDate(req.startDate)}</td>
                          <td>{formatDate(req.endDate)}</td>
                          <td>{req.numberOfDays}</td>
                          <td>{req.comment || "-"}</td>
                          <td>{formatDateTime(req.createdDate)}</td>
                          <td>
                            <button
                              className="btn btn-success btn-sm"
                              onClick={() => handleActionClick(req.id, "APPROVED")}
                            >
                              Odobri
                            </button>
                          </td>
                          <td>
                            <button
                              className="btn btn-warning btn-sm"
                              onClick={() => handleActionClick(req.id, "DECLINED")}
                            >
                              Odbij
                            </button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}

              <h2 className="fw-bold mt-5">Odobreni zahtjevi</h2>

              {!loadingApproved && approvedForSelectedTeam.length > 0 && (
                <div className="d-flex justify-content-end my-3">
                  <input
                    type="text"
                    className="form-control"
                    style={{ maxWidth: "250px" }}
                    placeholder="Pretraži po imenu i prezimenu"
                    value={approvedSearch}
                    onChange={(e) => setApprovedSearch(e.target.value)}
                  />
                </div>
              )}

              {loadingApproved ? (
                <div className="text-center my-4">
                  <div className="spinner-border text-primary" role="status" />
                  <p className="mt-2">Učitavanje odobrenih zahtjeva...</p>
                </div>
              ) : approvedForSelectedTeam.length === 0 ? (
                <p className="text-muted">Nema odobrenih zahtjeva za ovaj tim.</p>
              ) : approvedFiltered.length === 0 ? (
                <p className="text-muted">Nema rezultata za “{approvedSearch}”.</p>
              ) : (
                <div className="table-responsive shadow-sm rounded border">
                  <table className="table table-hover align-middle text-center vacation-table">
                    <thead className="table-success text-dark">
                      <tr>
                        <th>Zaposlenik</th>
                        <th>Od</th>
                        <th>Do</th>
                        <th>Dana</th>
                        <th>Komentar</th>
                        <th>Rješenje</th>
                      </tr>
                    </thead>
                    <tbody>
                      {approvedRequests
                        .filter((req) => String(req.teamId) === String(selectedTeamId))
                        .map((req) => (
                          <tr key={req.id}>
                            <td>{req.employee}</td>
                            <td>{formatDate(req.startDate)}</td>
                            <td>{formatDate(req.endDate)}</td>
                            <td>{req.numberOfDays}</td>
                            <td>{req.comment || "-"}</td>
                            <td>
                              {req.approvalStatusId && req.solutionUrl ? (
                                <button
                                  className="btn btn-outline-warning btn-sm"
                                  onClick={() => openPdfInline(req.approvalStatusId)}
                                  disabled={openingId === req.approvalStatusId}
                                >
                                  {openingId === req.approvalStatusId ? (
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
                                <span className="text-muted">Nema rješenja</span>
                              )}
                            </td>
                          </tr>
                        ))}
                    </tbody>
                  </table>
                </div>
              )}
            </div>

            <div className="col-lg-4">
              <br />
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
                            <span>
                              {member.firstName} {member.lastName}
                            </span>
                            <span className="text-muted">{member.role}</span>
                          </li>
                        ))}
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              <br /> <br />
              <TeamVacationCalendar
                approvedRequests={approvedRequests.filter(
                  (req) => String(req.teamId) === String(selectedTeamId)
                )}
              />
            </div>
          </div>
        </div>
      </SidebarWrapper>

      <ApprovalModal
        show={showModal}
        onClose={() => !modalLoading && setShowModal(false)}
        status={selectedStatus}
        comment={comment}
        setComment={setComment}
        onSubmit={handleSubmitApproval}
        loading={modalLoading}
      />
    </>
  );
}

export default ManagementPage;
