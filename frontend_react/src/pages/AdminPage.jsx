import React, { useEffect, useState } from "react";
import SidebarWrapper from "../components/SidebarWrapper";
import TopNav from "../components/TopNav";
import {
  startOfMonth,
  endOfMonth,
  startOfWeek,
  endOfWeek,
  addDays,
  format,
  isWithinInterval,
  parseISO,
} from "date-fns";
import hr from "date-fns/locale/hr";
import "../styles/AdminPage.css";

function AdminPage() {
  const [approvedRequests, setApprovedRequests] = useState([]);
  const [calendarDays, setCalendarDays] = useState([]);
  const [selectedMonth, setSelectedMonth] = useState(new Date().getMonth());
  const [selectedYear, setSelectedYear] = useState(new Date().getFullYear());
  const token = localStorage.getItem("jwt");

  useEffect(() => {
    fetchApprovedRequests();
  }, []);

  useEffect(() => {
    generateCalendar(selectedYear, selectedMonth);
  }, [selectedMonth, selectedYear]);

  const fetchApprovedRequests = async () => {
    try {
      const res = await fetch("http://localhost:8000/api/admin/requests/approved", {
        headers: { Authorization: `Bearer ${token}` },
      });
      const data = await res.json();
      setApprovedRequests(data);
    } catch (error) {
      console.error("Greška:", error);
    }
  };

  const generateCalendar = (year, month) => {
    const baseDate = new Date(year, month);
    const monthStart = startOfMonth(baseDate);
    const monthEnd = endOfMonth(baseDate);
    const startDate = startOfWeek(monthStart, { weekStartsOn: 1 });
    const endDate = endOfWeek(monthEnd, { weekStartsOn: 1 });

    const days = [];
    let day = startDate;

    while (day <= endDate) {
      const week = [];

      for (let i = 0; i < 7; i++) {
        week.push(day);
        day = addDays(day, 1);
      }

      days.push(week);
    }

    setCalendarDays(days);
  };

  const getEmployeesForDate = (date) => {
    return approvedRequests
      .filter((req) =>
        isWithinInterval(date, {
          start: parseISO(req.startDate),
          end: parseISO(req.endDate),
        })
      )
      .map((req) => req.employee);
  };

  const handlePrevMonth = () => {
    if (selectedMonth === 0) {
      setSelectedMonth(11);
      setSelectedYear((prev) => prev - 1);
    } else {
      setSelectedMonth((prev) => prev - 1);
    }
  };

  const handleNextMonth = () => {
    if (selectedMonth === 11) {
      setSelectedMonth(0);
      setSelectedYear((prev) => prev + 1);
    } else {
      setSelectedMonth((prev) => prev + 1);
    }
  };

  const monthName = format(new Date(selectedYear, selectedMonth), "MMMM", { locale: hr });

  return (
    <>
      <TopNav />
      <SidebarWrapper>
        <div className="container mt-4">
          <div className="calendar-nav d-flex justify-content-center align-items-center mb-3 mx-auto position-relative">
            <button
              className="btn btn-outline-secondary position-absolute"
              onClick={handlePrevMonth}
              style={{ left: "200px", zIndex: 1 }}
            >
              ←
            </button>

            <h2 className="fw-bold mb-0 text-center">
              {monthName.charAt(0).toUpperCase() + monthName.slice(1)} {selectedYear}
            </h2>

            <button
              className="btn btn-outline-secondary position-absolute"
              onClick={handleNextMonth}
              style={{ right: "200px", zIndex: 1 }}
            >
              →
            </button>
          </div>

          <div className="calendar-grid border rounded">
            <div className="calendar-header bg-light fw-bold">
              {["Pon", "Uto", "Sri", "Čet", "Pet", "Sub", "Ned"].map((d) => (
                <div key={d} className="calendar-cell text-center py-2 border-bottom">
                  {d}
                </div>
              ))}
            </div>

            <div className="calendar-body">
              {calendarDays.flat().map((date, index) => {
                const isCurrentMonth = date.getMonth() === selectedMonth;
                const employees = getEmployeesForDate(date);

                return (
                  <div
                    key={index}
                    className={`calendar-cell calendar-day p-2 border ${
                      isCurrentMonth ? "" : "text-muted bg-light"
                    }`}
                  >
                    <div className="fw-bold">{format(date, "d.")}</div>
                    <div>
                      {employees.map((e, idx) => (
                        <div key={idx} className="badge bg-warning text-dark mt-1">
                          {e}
                        </div>
                      ))}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </SidebarWrapper>
    </>
  );
}

export default AdminPage;
