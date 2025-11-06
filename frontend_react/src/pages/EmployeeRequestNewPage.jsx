import React, { useEffect, useState } from "react";
import { useNavigate, useSearchParams } from "react-router-dom";
import axios from "axios";
import { DateRange } from "react-date-range";
import { eachDayOfInterval, isWeekend, format } from "date-fns";
import { hr } from "date-fns/locale";
import "react-date-range/dist/styles.css";
import "react-date-range/dist/theme/default.css";
import TopNav2 from "../components/TopNav2";
import SidebarWrapper from "../components/SidebarWrapper";
import "../styles/EmployeePage.css";

function EmployeeRequestNewPage() {
  const [range, setRange] = useState([
    { startDate: new Date(), endDate: new Date(), key: "selection" },
  ]);
  const [comment, setComment] = useState("");
  const [holidays, setHolidays] = useState([]);
  const [vacationDays, setVacationDays] = useState(0);
  const [workingDays, setWorkingDays] = useState(1);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const token = localStorage.getItem("jwt");
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const teamId = searchParams.get("teamId");

  useEffect(() => {
    const fetchData = async () => {
      try {
        const [holidayRes, profileRes] = await Promise.all([
          axios.get("http://localhost:8000/api/holidays/all", {
            headers: { Authorization: `Bearer ${token}` },
          }),
          axios.get("http://localhost:8000/api/profil/all", {
            headers: { Authorization: `Bearer ${token}` },
          }),
        ]);

        const holidayDates = holidayRes.data.map((h) =>
          format(new Date(h.date), "yyyy-MM-dd")
        );
        setHolidays(holidayDates);
        setVacationDays(profileRes.data.vacationDays || 0);
      } catch (err) {
        console.error("Greška pri dohvaćanju podataka:", err);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, [token]);

  useEffect(() => {
    const days = eachDayOfInterval({
      start: range[0].startDate,
      end: range[0].endDate,
    });
    const working = days.filter((d) => {
      const f = format(d, "yyyy-MM-dd");
      return !isWeekend(d) && !holidays.includes(f);
    }).length;
    setWorkingDays(working);
  }, [range, holidays]);

  const renderDayContent = (day) => {
    const dateStr = format(day, "yyyy-MM-dd");
    const isHoliday = holidays.includes(dateStr);
    const weekend = isWeekend(day);

    return (
      <div
        style={{
          color: "#000",
          textDecoration: weekend ? "line-through" : "none",
          backgroundColor: isHoliday ? "#f8d7da" : "transparent",
          borderRadius: "50%",
          width: "100%",
          height: "100%",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          fontWeight: "bold",
        }}
      >
        {format(day, "d")}
      </div>
    );
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError("");

    const start = range[0].startDate;
    const end = range[0].endDate;

    if (end < start) {
      setError("Datum završetka ne može biti prije početka.");
      setSubmitting(false);
      return;
    }

    if (!teamId) {
      setError("Nije odabran tim.");
      setSubmitting(false);
      return;
    }

    if (workingDays > vacationDays) {
      setError(
        `Imate ${vacationDays} dana, a pokušavate iskoristiti ${workingDays}.`
      );
      setSubmitting(false);
      return;
    }

    try {
      await axios.post(
        `http://localhost:8000/api/employees/requests/team/${teamId}/new`,
        {
          startDate: format(start, "yyyy-MM-dd"),
          endDate: format(end, "yyyy-MM-dd"),
          comment,
        },
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );
      navigate("/employees/dashboard");
    } catch (err) {
      setError("Greška kod slanja zahtjeva.");
      console.error(err);
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <>
      <TopNav2 />
      <SidebarWrapper>
        <div className="container mt-4">
          <h2 className="fw-bold mb-4 text-center">Novi zahtjev za godišnji odmor</h2>
          <div className="container mt-5 p-4 bg-light rounded shadow-sm">
            {loading ? (
              <div className="text-center py-5">
                <div className="spinner-border text-primary" role="status" />
                <p className="mt-3">Učitavanje podataka...</p>
              </div>
            ) : (
              <form onSubmit={handleSubmit}>
                <label className="form-label">Odaberi datume:</label>
                <div className="d-flex justify-content-center">
                  <DateRange
                    ranges={range}
                    onChange={(item) => setRange([item.selection])}
                    locale={hr}
                    minDate={new Date()}
                    months={2}
                    direction="horizontal"
                    showDateDisplay={true}
                    dayContentRenderer={renderDayContent}
                    className="mb-4"
                  />
                </div>

                <div className="mb-3">
                  <p className="mb-1">
                    Radnih dana: <strong>{workingDays}</strong>
                  </p>
                  <p>
                    Preostalo dana godišnjeg: <strong>{vacationDays}</strong>
                  </p>
                </div>

                <div className="mb-3">
                  <label className="form-label">Komentar:</label>
                  <textarea
                    className="form-control"
                    value={comment}
                    onChange={(e) => setComment(e.target.value)}
                    rows={3}
                    placeholder="Npr. Obiteljski odmor..."
                    required
                  />
                </div>

                {error && <div className="alert alert-danger mt-3">{error}</div>}

                <button
                  type="submit"
                  className="btn btn-warning w-100 mt-3 py-2 fw-semibold"
                  disabled={workingDays > vacationDays || submitting}
                >
                  {submitting && (
                    <span
                      className="spinner-border spinner-border-sm me-2"
                      role="status"
                    ></span>
                  )}
                  Pošalji zahtjev
                </button>
              </form>
            )}
          </div>
        </div>
      </SidebarWrapper>
    </>
  );
}

export default EmployeeRequestNewPage;
