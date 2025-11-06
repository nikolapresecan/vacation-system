import React, { useEffect, useState } from "react";
import { useNavigate, Link } from "react-router-dom";
import SidebarWrapper from "../components/SidebarWrapper";
import TopNav from "../components/TopNav";
import CreateHolidayModal from "../components/CreateHolidayModal";
import axios from "axios";

function AdminHolidaysPage() {
  const [holidays, setHolidays] = useState([]);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);
  const token = localStorage.getItem("jwt");

  const fetchHolidays = async () => {
    setLoading(true);
    try {
      const res = await axios.get("http://localhost:8000/api/holidays/all", {
        headers: { Authorization: `Bearer ${token}` },
      });
      setHolidays(res.data);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchHolidays();
  }, []);

 const formatDate = (dateStr) => {
    if (!dateStr) return "";
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const year = date.getFullYear();
    return `${day}.${month}.${year}.`;
  };

  return (
    <>
      <TopNav />
      <SidebarWrapper>
        <div className="container mt-4">
          <h1 className="fw-bold mb-0">Praznici</h1>
          <br />
          <Link
            className="btn btn-sm btn-warning"
            onClick={() => setShowModal(true)}
          >
            Dodaj novi praznik
          </Link>
          <br /><br />

          {loading ? (
            <div className="text-center mt-5">
              <div className="spinner-border text-primary" role="status">
                <span className="visually-hidden">Učitavanje...</span>
              </div>
            </div>
          ) : (
            <ul className="list-group">
              {holidays.map((h) => (
                <li key={h.id} className="list-group-item">
                  <strong>{h.name}</strong> - {formatDate(h.date)}
                </li>
              ))}
            </ul>
          )}

          {showModal && (
            <CreateHolidayModal
              onClose={() => setShowModal(false)}
              onSuccess={() => {
                setShowModal(false);
                fetchHolidays();
              }}
            />
          )}
        </div>
      </SidebarWrapper>
    </>
  );
}

export default AdminHolidaysPage;