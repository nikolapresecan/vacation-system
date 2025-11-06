import React, { useEffect, useState } from "react";
import axios from "axios";
import { Link, useNavigate } from "react-router-dom";
import TopNav from "../components/TopNav";
import SidebarWrapper from "../components/SidebarWrapper";
import EditEmployeeModal from "../components/EditEmployeeModal";
import DeleteEmployeeModal from "../components/DeleteEmployeeModal";
import "../styles/AdminEmployeesPage.css";

const ROLE_LABELS = {
  "ROLE_ADMIN": "Administrator",
  "ROLE_EMPLOYEE": "Zaposlenik",
  "ROLE_TEAM LEADER": "Voditelj tima",
  "ROLE_PROJECT MANAGER": "Voditelj projekta",
};

function AdminEmployeesPage() {
  const [employees, setEmployees] = useState([]);
  const [query, setQuery] = useState("");
  const [editingEmployee, setEditingEmployee] = useState(null);
  const [deletingEmployee, setDeletingEmployee] = useState(null);
  const [loading, setLoading] = useState(true);
  const [deleting, setDeleting] = useState(false);
  const [deleteMessage, setDeleteMessage] = useState("");
  const [deleteMessageType, setDeleteMessageType] = useState("success");

  const navigate = useNavigate();
  const token = localStorage.getItem("jwt");

  const fetchEmployees = async () => {
    try {
      setLoading(true);
      const response = await axios.get("http://localhost:8000/api/admin/employees", {
        headers: { Authorization: `Bearer ${token}` },
        withCredentials: true,
      });
      setEmployees(response.data);
    } catch (error) {
      setEmployees([]);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchEmployees();
  }, []);

  const filteredEmployees = employees.filter((e) => {
    const q = query.toLowerCase();
    return (
      !e.roles.some((role) => role.includes("ADMIN")) &&
      (
        e.firstName.toLowerCase().includes(q) ||
        e.lastName.toLowerCase().includes(q) ||
        (e.team?.name?.toLowerCase().includes(q) ?? false)
      )
    );
  });

  const formatDate = (dateStr) => {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}.${month}.${year}.`;
  };

  const handleDelete = async (employeeId) => {
    try {
      setDeleting(true);
      const response = await axios.delete(
        `http://localhost:8000/api/admin/employees/employee/${employeeId}/delete`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      setDeleteMessage("Zaposlenik je uspješno obrisan.");
      setDeleteMessageType("success");
    } catch (err) {
      setDeleteMessage(
        err.response?.data?.error ||
        err.response?.data?.message ||
        "Greška pri brisanju."
      );
      setDeleteMessageType("warning");
    } finally {
      setDeleting(false);
      setDeletingEmployee(null);
      fetchEmployees();
    }
  };

  let count = 1;

  return (
    <>
      <TopNav />
      <SidebarWrapper>
        <div className="container mt-4">
          <h1 className="fw-bold mb-0 gap-2">Zaposlenici</h1>
          <br />
          <Link className="btn btn-sm btn-warning align-items-center gap-2" to="/admin/employees/new">
            Dodaj novoga zaposlenika
          </Link>
          <br />
          <div className="d-flex justify-content-end">
            <form className="d-flex" onSubmit={(e) => e.preventDefault()} autoComplete="off">
              <input
                type="text"
                className="form-control form-control-sm"
                placeholder="Pretraži zaposlenike"
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                style={{ maxWidth: "220px" }}
              />
            </form>
          </div>

          <div className="table-responsive">
            <table className="table text-center align-middle employee-table">
              <thead>
                <tr>
                  <th>Rbr.</th>
                  <th>Ime</th>
                  <th>Prezime</th>
                  <th>Datum rođenja</th>
                  <th>Uloga</th>
                  <th>Tim</th>
                  <th></th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                {loading || deleting ? (
                  <tr>
                    <td colSpan={8} className="text-center py-5">
                      <div className="spinner-border text-primary" role="status">
                        <span className="visually-hidden">Učitavanje...</span>
                      </div>
                    </td>
                  </tr>
                ) : filteredEmployees.length > 0 ? (
                  filteredEmployees.map((employee) => (
                    <tr
                      key={employee.id}
                      style={{ cursor: "pointer" }}
                      onClick={(e) => {
                        const targetTag = e.target.tagName.toLowerCase();
                        if (targetTag !== "button" && targetTag !== "svg" && targetTag !== "path") {
                          navigate(`/admin/employees/employee/${employee.id}`);
                        }
                      }}
                    >
                      <td>{count++}.</td>
                      <td>{employee.firstName}</td>
                      <td>{employee.lastName}</td>
                      <td>{formatDate(employee.birthDate)}</td>
                      <td>{employee.roles.map(role => ROLE_LABELS[role] || role).join(", ")}</td>
                      <td>
                        {employee.teams && employee.teams.length > 0
                          ? employee.teams.map(t => t.name).join(", ")
                          : "Nema tima"}
                      </td>
                      <td>
                        <button
                          className="btn btn-sm btn-warning"
                          onClick={() => setEditingEmployee(employee)}
                        >
                          Uredi
                        </button>
                      </td>
                      <td>
                        <button
                          className="btn btn-sm btn-success"
                          onClick={() => setDeletingEmployee(employee)}
                        >
                          Obriši
                        </button>
                      </td>
                    </tr>
                  ))
                ) : (
                  <tr>
                    <td colSpan={8}>Nema zaposlenika za prikaz.</td>
                  </tr>
                )}
              </tbody>
            </table>

            {deleteMessage && (
              <div className={`alert alert-${deleteMessageType} mt-3`} role="alert">
                {deleteMessage}
              </div>
            )}
          </div>

          {editingEmployee && (
            <EditEmployeeModal
              employee={editingEmployee}
              onClose={() => setEditingEmployee(null)}
              onUpdated={() => {
                setEditingEmployee(null);
                fetchEmployees();
              }}
            />
          )}

          {deletingEmployee && (
            <DeleteEmployeeModal
              employee={deletingEmployee}
              onClose={() => setDeletingEmployee(null)}
              onConfirm={handleDelete}
            />
          )}
        </div>
      </SidebarWrapper>
    </>
  );
}

export default AdminEmployeesPage;
