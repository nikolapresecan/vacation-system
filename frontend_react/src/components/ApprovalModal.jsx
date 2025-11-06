import React from "react";
import Modal from "react-bootstrap/Modal";
import Button from "react-bootstrap/Button";
import Form from "react-bootstrap/Form";

function ApprovalModal({
  show,
  onClose,
  status,
  comment,
  setComment,
  onSubmit,
  loading = false,
}) {
  const isApprove = status === "APPROVED";

  return (
    <Modal show={show} onHide={onClose} centered backdrop="static">
      <Modal.Header closeButton={!loading} className="bg-light">
        <Modal.Title className="fw-bold text-primary">
          {isApprove ? "Odobri zahtjev" : "Odbij zahtjev"}
        </Modal.Title>
      </Modal.Header>

      <Modal.Body>
        <Form.Group controlId="commentTextarea">
          <Form.Label className="fw-semibold">Komentar (opcionalno)</Form.Label>
          <Form.Control
            as="textarea"
            rows={4}
            value={comment}
            onChange={(e) => setComment(e.target.value)}
            placeholder="Dodajte komentar koji će vidjeti zaposlenik..."
            className="shadow-sm"
          />
        </Form.Group>
      </Modal.Body>

      <Modal.Footer className="bg-light">
        <Button variant="secondary" onClick={onClose} disabled={loading}>
          Zatvori
        </Button>
        <Button
          variant={isApprove ? "success" : "warning"}
          onClick={() => !loading && onSubmit()}
          disabled={loading}
        >
          {loading ? (
            <>
              <span
                className="spinner-border spinner-border-sm me-2"
                role="status"
                aria-hidden="true"
              ></span>
              Obrada...
            </>
          ) : (
            "Potvrdi"
          )}
        </Button>
      </Modal.Footer>
    </Modal>
  );
}

export default ApprovalModal;
