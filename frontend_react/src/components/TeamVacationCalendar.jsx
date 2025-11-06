import React, { useMemo, useState } from "react";
import {
  addMonths,
  subMonths,
  startOfMonth,
  endOfMonth,
  startOfWeek,
  endOfWeek,
  eachDayOfInterval,
  isSameMonth,
  isSameDay,
  format,
  parseISO,
} from "date-fns";
import { hr } from "date-fns/locale";
import "../styles/ManagementPage.css";

function withinRange(date, startDate, endDate) {
  const d = new Date(date.setHours(0,0,0,0));
  const s = new Date(new Date(startDate).setHours(0,0,0,0));
  const e = new Date(new Date(endDate).setHours(0,0,0,0));
  return d >= s && d <= e;
}

export default function TeamVacationCalendar({ approvedRequests = [] }) {
  const [currentMonth, setCurrentMonth] = useState(new Date());
  const [activeDay, setActiveDay] = useState(null);

  const monthLabel = format(currentMonth, "LLLL yyyy", { locale: hr }); 

  const days = useMemo(() => {
    const start = startOfWeek(startOfMonth(currentMonth), { weekStartsOn: 1 });
    const end = endOfWeek(endOfMonth(currentMonth), { weekStartsOn: 1 });
    return eachDayOfInterval({ start, end });
  }, [currentMonth]);

  const dayToEmployees = useMemo(() => {
    const map = new Map();
    days.forEach((d) => {
      map.set(d.toDateString(), []);
    });
    approvedRequests.forEach((req) => {
      const s = typeof req.startDate === "string" ? parseISO(req.startDate) : new Date(req.startDate);
      const e = typeof req.endDate === "string" ? parseISO(req.endDate) : new Date(req.endDate);
      days.forEach((d) => {
        if (withinRange(new Date(d), s, e)) {
          const key = d.toDateString();
          const arr = map.get(key) || [];
          const initials = req.employee
            ? req.employee.split(" ").map(p => p[0]).join("").slice(0,3).toUpperCase()
            : "EMP";
          arr.push({ full: req.employee || "Zaposlenik", short: initials });
          map.set(key, arr);
        }
      });
    });
    return map;
  }, [approvedRequests, days]);

  const weekDayLabels = useMemo(() => {
    const base = eachDayOfInterval({
      start: startOfWeek(new Date(), { weekStartsOn: 1 }),
      end: endOfWeek(new Date(), { weekStartsOn: 1 }),
    });
    return base.map(d => {
      const lbl = format(d, "EEE", { locale: hr }); 
      const cap = lbl.charAt(0).toUpperCase() + lbl.slice(1,3);
      
      if (lbl.startsWith("čet")) return "Čet";
      if (lbl.startsWith("sri")) return "Sri";
      if (lbl.startsWith("sub")) return "Sub";
      if (lbl.startsWith("ned")) return "Ned";
      if (lbl.startsWith("uto")) return "Uto";
      if (lbl.startsWith("pon")) return "Pon";
      if (lbl.startsWith("pet")) return "Pet";
      return cap;
    });
  }, []);

  const handlePrev = () => setCurrentMonth((m) => subMonths(m, 1));
  const handleNext = () => setCurrentMonth((m) => addMonths(m, 1));

  const selectedDayEmployees = activeDay
    ? dayToEmployees.get(activeDay.toDateString()) || []
    : [];

  return (
    <div className="tvcal card shadow-sm border-0">
      <div className="card-body">
        <div className="d-flex justify-content-between align-items-center mb-2">
          <button className="btn btn-light btn-sm" onClick={handlePrev} aria-label="Prethodni mjesec">
            ‹
          </button>
          <h6 className="m-0 fw-semibold text-uppercase">{monthLabel}</h6>
          <button className="btn btn-light btn-sm" onClick={handleNext} aria-label="Sljedeći mjesec">
            ›
          </button>
        </div>
        
        <div className="tvcal-grid tvcal-header">
          {weekDayLabels.map((w) => (
            <div key={w} className="text-muted small text-center fw-semibold">{w}</div>
          ))}
        </div>
        
        <div className="tvcal-grid">
          {days.map((d) => {
            const inMonth = isSameMonth(d, currentMonth);
            const isToday = isSameDay(d, new Date());
            const emps = dayToEmployees.get(d.toDateString()) || [];
            const isActive = activeDay && isSameDay(d, activeDay);

            return (
              <button
                key={d.toISOString()}
                className={[
                  "tvcal-cell btn btn-sm text-start",
                  inMonth ? "bg-white" : "bg-light-subtle",
                  isToday ? "tvcal-today" : "",
                  isActive ? "tvcal-active" : "",
                ].join(" ")}
                onClick={() => setActiveDay(d)}
                title={format(d, "d. LLLL yyyy.", { locale: hr })}
              >
                <div className="d-flex justify-content-between align-items-center">
                  <span className={`tvcal-date ${inMonth ? "" : "text-muted"}`}>
                    {format(d, "d", { locale: hr })}
                  </span>
                  {emps.length > 0 && (
                    <span className="badge rounded-pill text-bg-success ms-1">{emps.length}</span>
                  )}
                </div>
                
                <div className="d-flex gap-1 flex-wrap mt-1">
                  {emps.slice(0, 3).map((e, idx) => (
                    <span key={idx} className="tvcal-pill badge text-bg-warning">{e.short}</span>
                  ))}
                  {emps.length > 3 && (
                    <span className="tvcal-pill badge text-bg-warning">+{emps.length - 3}</span>
                  )}
                </div>
              </button>
            );
          })}
        </div>
        
        <div className="mt-3">
          <div className="d-flex justify-content-between align-items-center">
            <h6 className="fw-semibold mb-2">
              {activeDay
                ? `Dan: ${format(activeDay, "d. LLLL yyyy.", { locale: hr })}`
                : "Odaberite dan"}
            </h6>
            {activeDay && (
              <button className="btn btn-outline-success btn-sm" onClick={() => setActiveDay(null)}>
                Očisti
              </button>
            )}
          </div>
          {activeDay && (
            selectedDayEmployees.length > 0 ? (
              <ul className="list-group small">
                {selectedDayEmployees.map((e, idx) => (
                  <li key={idx} className="list-group-item d-flex align-items-center">
                    <span className="badge text-bg-warning me-2">{e.short}</span>
                    <span>{e.full}</span>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="text-muted small mb-0">Nitko nije na godišnjem za odabrani dan.</p>
            )
          )}
        </div>
      </div>
    </div>
  );
}
