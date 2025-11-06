# Vacation Management System

Full-stack web application for managing employee vacation requests, developed as part of the bachelor’s thesis *“Vacation Management Using Web Application and Algorithms”*.

## Overview
This project demonstrates how **modern web technologies** and **algorithms** can automate annual leave management in organizations. System digitalizes the process of planning, approving, and tracking employee vacations.  
The system is built using:

- **Backend:** Symfony 7.3 (PHP 8.3, JWT authentication)
- **Frontend:** React (Vite)
- **Database:** MySQL 8.0
- **Containerization:** Docker & Docker Compose

## Features
- **JWT Authentication** — secure login with user roles and token-based access  
- **Role Management** — Admin, Team Leader, Project Manager, and Employee roles  
- **Algorithmic Decision System:**
  - Priority Queue for fair request processing  
  - Interval Search Algorithm to prevent overlapping vacations  
  - Dynamic Programming for optimal request selection  
  - Graph Search (BFS/DFS) for team structure analysis  
- **Vacation Requests Management** — submit, approve, reject, and view history  
- **Holiday Calendar Integration** — national holidays automatically excluded from counts  
- **PDF Generation** — official vacation approval documents generated and stored automatically  
- **Email Notifications** — employees receive approval/rejection notifications with attachments  
- **Team & Employee Management** — CRUD operations for staff, roles, and teams  
- **Search & Filters** — easy data lookup by name, team, or status  
- **Responsive React UI** — modern, animated, and accessible interface  
- **Dockerized Infrastructure** — one command setup with database, backend, and frontend containers  

> **Note:** The backend may load more slowly at startup because Symfony runs inside a Docker container (initial cache build, Composer autoload, and config warm-up can take some time).

## Project Structure

- backend_symfony/ → Symfony REST API
- frontend_react/ → React (Vite) SPA
- docker-compose.yml → Docker services (MySQL, backend, worker, frontend)

## Environment Setup

1. Generate JWT Keys (Backend)
    JWT authentication requires key pairs.
    In **.env** change `JWT_PASSPHRASE`
2. Run Database Migrations
3. Change Mail credentials (used for sending reset-password and notifications) in `docker-compose.yml`
4. Build and start all services with command `docker compose up --build` or run directly in **Docker Desktop**