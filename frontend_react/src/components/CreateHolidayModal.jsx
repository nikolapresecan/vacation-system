import React, { useEffect, useRef, useState } from "react";
import axios from "axios";
import DatePicker, { registerLocale } from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import { format } from "date-fns";
import hr from "date-fns/locale/hr";

registerLocale("hr", hr);

function CreateHolidayModal({ onClose, onSuccess }) {
  const [name, setName] = useState("");
  const [date, setDate] = useState(null);
  const [error, setError] = useState("");
  const token = localStorage.getItem("jwt");
  const inputRef = useRef(null);

  useEffect(() => {
    inputRef.current?.focus();
    document.body.style.overflow = "hidden";

    const handleEscape = (e) => {
      if (e.key === "Escape") onClose();
    };
    document.addEventListener("keydown", handleEscape);

    return () => {
      document.body.style.overflow = "";
      document.removeEventListener("keydown", handleEscape);
    };
  }, [onClose]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!name.trim() || !date) {
      setError("Naziv i datum su obavezni.");
      return;
    }

    try {
      const formattedDate = format(date, "yyyy-MM-dd");
      await axios.post(
        "http://localhost:8000/api/admin/holidays/new",
        { name, date: formattedDate },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      onSuccess();
    } catch (err) {
      console.error(err);
      setError("Greška prilikom spremanja praznika.");
    }
  };

  return (
    <>
      <div className="modal show d-block" tabIndex="-1" role="dialog">
        <div className="modal-dialog modal-dialog-centered">
          <div className="modal-content shadow rounded-3 border-0">
            <form onSubmit={handleSubmit}>
              <div className="modal-header bg-warning text-white">
                <h5 className="modal-title">Dodaj novi praznik</h5>
                <button type="button" className="btn-close" onClick={onClose}></button>
              </div>
              <div className="modal-body">
                {error && <div className="alert alert-danger">{error}</div>}
                <div className="mb-3">
                  <label className="form-label">Naziv praznika</label>
                  <input
                    ref={inputRef}
                    type="text"
                    className="form-control"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                  />
                </div>
                <div className="mb-3">
                  <label className="form-label">Datum</label>
                  <DatePicker
                    selected={date}
                    onChange={(d) => setDate(d)}
                    dateFormat="dd.MM.yyyy."
                    locale="hr"
                    className="form-control"
                    placeholderText="Odaberi datum"
                    showMonthDropdown
                    showYearDropdown
                    dropdownMode="select"
                  />
                </div>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn btn-secondary" onClick={onClose}>
                  Zatvori
                </button>
                <button type="submit" className="btn btn-warning">
                  Spremi
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div className="modal-backdrop fade show"></div>
    </>
  );
}

export default CreateHolidayModal;
