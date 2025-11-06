import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import SidebarWrapper from "../components/SidebarWrapper";
import TopNav from "../components/TopNav";
import axios from "axios";
import DatePicker, { registerLocale } from "react-datepicker";
import "react-datepicker/dist/react-datepicker.css";
import hr from "date-fns/locale/hr";

registerLocale("hr", hr);

function AdminEmployeeNewPage() {
  const [formData, setFormData] = useState({
    firstName: "",
    lastName: "",
    birthDate: null,
    email: "",
    username: "",
    password: "",
    job: "",
    oib: "",
    serviceYears: null,
  });

  const [jobs, setJobs] = useState([]);
  const [message, setMessage] = useState("");
  const [errors, setErrors] = useState({});
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const token = localStorage.getItem("jwt");
    axios
      .get("http://localhost:8000/api/jobs/all", {
        headers: { Authorization: `Bearer ${token}` },
      })
      .then((res) => setJobs(res.data))
      .catch(() => setJobs([]));
  }, []);
  
  const isValidOib = (oib) => {
    if (!/^\d{11}$/.test(oib)) return false;
    let a = 10;
    for (let i = 0; i < 10; i++) {
      a = (a + parseInt(oib[i], 10)) % 10;
      if (a === 0) a = 10;
      a = (a * 2) % 11;
    }
    let control = 11 - a;
    if (control === 10) control = 0;
    return control === parseInt(oib[10], 10);
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    let v = value;
    if (name === "serviceYears") {
      v = Math.max(0, parseInt(value || "0", 10));
    }
    if (name === "oib") {
      v = value.replace(/\D/g, "").slice(0, 11); 
    }
    setFormData((prev) => ({ ...prev, [name]: v }));
    setErrors((prev) => ({ ...prev, [name]: "" }));
  };

  const handleDateChange = (date) => {
    setFormData((prev) => ({ ...prev, birthDate: date }));
    setErrors((prev) => ({ ...prev, birthDate: "" }));
  };

  const validate = () => {
    const newErrors = {};
    if (!formData.firstName) newErrors.firstName = "Ime je obavezno.";
    if (!formData.lastName) newErrors.lastName = "Prezime je obavezno.";
    if (!formData.birthDate) newErrors.birthDate = "Datum rođenja je obavezan.";
    if (!formData.email) newErrors.email = "Email je obavezan.";
    else if (!/\S+@\S+\.\S+/.test(formData.email)) newErrors.email = "Email nije ispravan.";
    if (!formData.username) newErrors.username = "Korisničko ime je obavezno.";
    if (!formData.password) newErrors.password = "Lozinka je obavezna.";
    if (!formData.oib) newErrors.oib = "OIB je obavezan.";
    else if (!isValidOib(formData.oib)) newErrors.oib = "OIB nije valjan.";
    if (formData.serviceYears < 0) newErrors.serviceYears = "Godine staža moraju biti ≥ 0.";
    return newErrors;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const validationErrors = validate();
    if (Object.keys(validationErrors).length > 0) {
      setErrors(validationErrors);
      return;
    }

    try {
      setLoading(true);
      const params = new URLSearchParams();

      Object.entries(formData).forEach(([key, value]) => {
        if (key === "birthDate" && value instanceof Date) {
          const isoDate = value.toISOString().split("T")[0];
          params.append(key, isoDate);
        } else {
          params.append(key, value);
        }
      });

      const token = localStorage.getItem("jwt");
      const res = await axios.post("http://localhost:8000/api/admin/employees/new",
        params,
        { headers: { Authorization: `Bearer ${token}` } }
      );

      setMessage(res.data.message);
      setFormData({
        firstName: "",
        lastName: "",
        birthDate: null,
        email: "",
        username: "",
        password: "",
        job: "",
        oib: "",
        serviceYears: 0,
      });
    } catch (err) {
      if (err.response?.data?.errors) {
        setMessage(err.response.data.errors.join(", "));
      } else {
        setMessage("Greška prilikom dodavanja.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <TopNav />
      <SidebarWrapper>
        <div className="container mt-4">
          <h2>Kreiraj zaposlenika</h2>
          <form onSubmit={handleSubmit} className="mt-3">
            <div className="row">
              <div className="col-md-6">
                {errors.firstName && <div className="text-danger small">{errors.firstName}</div>}
                <input
                  name="firstName"
                  value={formData.firstName}
                  onChange={handleChange}
                  placeholder="Ime"
                  className="form-control mb-2"
                />
              </div>

              <div className="col-md-6">
                {errors.lastName && <div className="text-danger small">{errors.lastName}</div>}
                <input
                  name="lastName"
                  value={formData.lastName}
                  onChange={handleChange}
                  placeholder="Prezime"
                  className="form-control mb-2"
                />
              </div>

              <div className="col-md-6">
                {errors.oib && <div className="text-danger small">{errors.oib}</div>}
                <input
                  name="oib"
                  value={formData.oib}
                  onChange={handleChange}
                  placeholder="OIB (11 znamenki)"
                  className="form-control mb-2"
                  inputMode="numeric"
                />
              </div>

              <div className="col-md-6">
                {errors.birthDate && <div className="text-danger small">{errors.birthDate}</div>}
                <div className="mb-2">
                  <div className="form-control d-flex align-items-center p-0" style={{ height: "38px" }}>
                    <DatePicker
                      selected={formData.birthDate}
                      onChange={handleDateChange}
                      dateFormat="dd.MM.yyyy."
                      placeholderText="Datum rođenja"
                      className="border-0 px-2 w-100 h-100"
                      locale="hr"
                      showMonthDropdown
                      showYearDropdown
                      dropdownMode="select"
                      maxDate={new Date()}
                    />
                  </div>
                </div>
              </div>

              <div className="col-md-6">
                {errors.email && <div className="text-danger small">{errors.email}</div>}
                <input
                  name="email"
                  value={formData.email}
                  onChange={handleChange}
                  placeholder="Email"
                  className="form-control mb-2"
                />
              </div>

              <div className="col-md-6">
                {errors.username && <div className="text-danger small">{errors.username}</div>}
                <input
                  name="username"
                  value={formData.username}
                  onChange={handleChange}
                  placeholder="Korisničko ime"
                  className="form-control mb-2"
                />
              </div>

              <div className="col-md-6">
                {errors.password && <div className="text-danger small">{errors.password}</div>}
                <input
                  type="password"
                  name="password"
                  value={formData.password}
                  onChange={handleChange}
                  placeholder="Lozinka"
                  className="form-control mb-2"
                />
              </div>

              <div className="col-md-6">
                {errors.serviceYears && <div className="text-danger small">{errors.serviceYears}</div>}
                <input
                  type="number"
                  name="serviceYears"
                  value={formData.serviceYears}
                  onChange={handleChange}
                  placeholder="Godine staža (ukupno)"
                  className="form-control mb-2"
                  min={0}
                />
              </div>

              <div className="col-md-6">
                <select
                  name="job"
                  value={formData.job}
                  onChange={handleChange}
                  className="form-control mb-3"
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

            <div className="col-12 text-center mt-4">
              {loading ? (
                <div className="spinner-border text-warning" role="status">
                  <span className="visually-hidden">Učitavanje...</span>
                </div>
              ) : (
                <button type="submit" className="btn btn-warning px-5">
                  Dodaj zaposlenika
                </button>
              )}
            </div>

            {message && <p className="mt-3 text-center fw-semibold text-success">{message}</p>}
          </form>
        </div>
      </SidebarWrapper>
    </>
  );
}

export default AdminEmployeeNewPage;
