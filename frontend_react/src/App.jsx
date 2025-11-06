import React from "react";
import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import LoginPage from "./pages/LoginPage";
import AdminPage from "./pages/AdminPage";
import AdminEmployeesPage from "./pages/AdminEmployeesPage";
import AdminEmployeeNewPage from "./pages/AdminEmployeeNewPage";
import AdminEmployeeDetailsPage from "./pages/AdminEmployeeDetailsPage";
import AdminTeamsPage from "./pages/AdminTeamsPage";
import AdminTeamEditPage from "./pages/AdminTeamEditPage";
import AdminHolidaysPage from "./pages/AdminHolidaysPage";
import EmployeePage from "./pages/EmployeePage";
import EmployeeRequestNewPage from "./pages/EmployeeRequestNewPage";
import ManagementPage from "./pages/ManagementPage";
import ProfilPage from "./pages/ProfilPage";
import ResetPasswordPage from "./pages/ResetPasswordPage";

function ProtectedRoute({ children }) {
  const token = localStorage.getItem("jwt");
  if (!token) return <Navigate to="/" replace />;
  return children;
}

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<LoginPage />} />
        <Route path="/admin/dashboard" element={<ProtectedRoute><AdminPage /></ProtectedRoute>}/>
        <Route path="/admin/employees" element={<ProtectedRoute><AdminEmployeesPage /></ProtectedRoute>}/>
        <Route path="/admin/employees/new" element={<ProtectedRoute><AdminEmployeeNewPage /></ProtectedRoute>}/>
        <Route path="/admin/employees/employee/:id" element={<ProtectedRoute><AdminEmployeeDetailsPage /></ProtectedRoute>}/>
        <Route path="/admin/teams" element={<ProtectedRoute><AdminTeamsPage /></ProtectedRoute>}/>
        <Route path="/admin/teams/team/:id/edit" element={<ProtectedRoute><AdminTeamEditPage /></ProtectedRoute>}/>
        <Route path="/admin/holidays" element={<ProtectedRoute><AdminHolidaysPage /></ProtectedRoute>}/>
        <Route path="/employees/dashboard" element={<ProtectedRoute><EmployeePage /></ProtectedRoute>}/>
        <Route path="/employees/requests/new" element={<ProtectedRoute><EmployeeRequestNewPage /></ProtectedRoute>}/>
        <Route path="/management/dashboard" element={<ProtectedRoute><ManagementPage /></ProtectedRoute>}/>
        <Route path="/reset-password/:token" element={<ResetPasswordPage />} />
        <Route path="/profil" element={<ProfilPage />} />
        <Route path="*" element={<Navigate to="/" />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
