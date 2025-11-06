import React, { useState } from "react";

function DeleteEmployeeModal({ employee, onClose, onConfirm }) {
  const [isDeleting, setIsDeleting] = useState(false);

  if (!employee) return null;

  const handleConfirm = async () => {
    setIsDeleting(true);
    await onConfirm(employee.id); 
  };

  return (
    <div className="modal show fade d-block" tabIndex="-1" style={{ backgroundColor: "rgba(0,0,0,0.5)" }}>
      <div className="modal-dialog">
        <div className="modal-content">
          <div className="modal-header">
            <h5 className="modal-title">Potvrda brisanja</h5>
            <button type="button" className="btn-close" onClick={onClose} disabled={isDeleting}></button>
          </div>
          <div className="modal-body">
            <p>
              Jeste li sigurni da želite obrisati zaposlenika{" "}
              <strong>{employee.firstName} {employee.lastName}</strong>?
            </p>
          </div>
          <div className="modal-footer">
            <button className="btn btn-secondary" onClick={onClose} disabled={isDeleting}>
              Odustani
            </button>
            <button className="btn btn-warning" onClick={handleConfirm} disabled={isDeleting}>
              {isDeleting ? (
                <>
                  <span className="spinner-border spinner-border-sm me-2" role="status" />
                  Brisanje...
                </>
              ) : (
                "Obriši"
              )}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default DeleteEmployeeModal;
