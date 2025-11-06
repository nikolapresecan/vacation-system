import React, { useEffect, useState } from "react";
import axios from "axios";

function EditEmployeeModal({ employee, onClose, onUpdated }) {
  const [vacationDays, setVacationDays] = useState(0);
  const [jobId, setJobId] = useState("");
  const [jobs, setJobs] = useState([]);
  const [loading, setLoading] = useState(true);   // učitavanje inicijalno
  const [saving, setSaving] = useState(false);    // spremanje

  const token = localStorage.getItem("jwt");

  useEffect(() => {
    axios
      .get("http://localhost:8000/api/jobs/all", {
        headers: { Authorization: `Bearer ${token}` },
      })
      .then((res) => setJobs(res.data))
      .catch(console.error);
  }, [token]);

  useEffect(() => {
    const fetchFullEmployee = async () => {
      try {
        setLoading(true);
        const response = await axios.get(
          `http://localhost:8000/api/admin/employees/employee/${employee.id}`,
          {
            headers: { Authorization: `Bearer ${token}` },
          }
        );
        const data = response.data;
        setVacationDays(data.vacationDays ?? 0);
        setJobId(data.job?.id ?? "");
      } catch (err) {
        console.error("Greška prilikom dohvaćanja zaposlenika:", err);
      } finally {
        setLoading(false);
      }
    };

    if (employee?.id) {
      fetchFullEmployee();
    }
  }, [employee?.id]);

  const handleUpdate = async (e) => {
    e.preventDefault();
    try {
      setSaving(true);
      await axios.put(
        `http://localhost:8000/api/admin/employees/employee/${employee.id}/edit`,
        { vacationDays, job: jobId },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      await onUpdated(); // osvježavanje liste
      onClose();         // zatvaranje samo nakon uspjeha
    } catch (err) {
      console.error("Greška prilikom ažuriranja zaposlenika:", err);
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="modal show fade d-block" tabIndex="-1" style={{ backgroundColor: "rgba(0,0,0,0.5)" }}>
      <div className="modal-dialog">
        <div className="modal-content">
          {loading ? (
            <div className="d-flex justify-content-center align-items-center" style={{ height: "200px" }}>
              <div className="spinner-border text-primary" role="status" style={{ width: "3rem", height: "3rem" }} />
            </div>
          ) : (
            <form onSubmit={handleUpdate}>
              <div className="modal-header">
                <h5 className="modal-title">Uredi zaposlenika</h5>
                <button type="button" className="btn-close" onClick={onClose} disabled={saving}></button>
              </div>
              <div className="modal-body">
                <div className="mb-3">
                  <label className="form-label">Broj dana godišnjeg</label>
                  <input
                    type="number"
                    className="form-control"
                    value={vacationDays}
                    onChange={(e) => setVacationDays(Number(e.target.value))}
                    disabled={saving}
                  />
                </div>
                <div className="mb-3">
                  <label className="form-label">Pozicija</label>
                  <select
                    className="form-select"
                    value={jobId}
                    onChange={(e) => setJobId(e.target.value)}
                    disabled={saving}
                  >
                    <option value="">Odaberi posao</option>
                    {jobs.map((job) => (
                      <option key={job.id} value={job.id}>
                        {job.name}
                      </option>
                    ))}
                  </select>
                </div>
              </div>
              <div className="modal-footer">
                <button type="submit" className="btn btn-success" disabled={saving}>
                  {saving ? "Spremanje..." : "Spremi"}
                </button>
                <button type="button" className="btn btn-secondary" onClick={onClose} disabled={saving}>
                  Zatvori
                </button>
              </div>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}

export default EditEmployeeModal;
