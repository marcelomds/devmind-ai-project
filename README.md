<div align="center">

# 🧠 DevMind AI

### An AI-powered developer workspace built with a modern, containerized full-stack architecture.

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![React](https://img.shields.io/badge/React-20232A?style=for-the-badge&logo=react&logoColor=61DAFB)](https://react.dev)
[![TypeScript](https://img.shields.io/badge/TypeScript-3178C6?style=for-the-badge&logo=typescript&logoColor=white)](https://www.typescriptlang.org)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-4169E1?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![Redis](https://img.shields.io/badge/Redis-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![AWS](https://img.shields.io/badge/AWS-232F3E?style=for-the-badge&logo=amazon-aws&logoColor=white)](https://aws.amazon.com)

![Status](https://img.shields.io/badge/status-in_development-yellow?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)
![PRs](https://img.shields.io/badge/PRs-welcome-brightgreen?style=flat-square)

</div>

---

> ⚠️ **Work in progress.** DevMind AI is under active development. The foundation — containerized infrastructure, versioned REST API, and a typed React frontend — is in place. Product features are being built incrementally and this README evolves with them.

---

## 📖 Overview

**DevMind AI** is a full-stack workspace that brings AI assistance into the developer's workflow. It pairs a **Laravel** REST API with a **React + TypeScript** frontend, backed by **PostgreSQL** and **Redis**, fully orchestrated with **Docker** and designed for deployment on **AWS**.

The project is intentionally built the way a production system would be: a clean separation between backend and frontend, a versioned API (`/api/v1`), queue-ready infrastructure for long-running AI calls, and an architecture prepared for multiple AI providers.

---

## 🏗️ Architecture

```
                          ┌──────────────────────────────────────────┐
                          │              Docker Network              │
                          │                                          │
   React + TypeScript     │    ┌──────────┐      ┌──────────────┐    │
   (Vite · :5173)  ──────────▶ │  Nginx   │────▶ │  Laravel API │    │
                          │    │  (:80)   │ FCGI │   (PHP-FPM)   │    │
                          │    └──────────┘      └──────┬───────┘    │
                          │                             │            │
                          │              ┌──────────────┼──────────┐ │
                          │              ▼                          ▼ │
                          │       ┌────────────┐            ┌────────┐│
                          │       │ PostgreSQL │            │ Redis  ││
                          │       │  (:5432)   │            │(:6379) ││
                          │       └────────────┘            └────────┘│
                          │                            cache · queues │
                          └──────────────────────────────────────────┘
```

The frontend runs on the host for instant HMR and talks to the API over HTTP. Inside the network, services resolve each other by name (`postgres`, `redis`), and Redis powers cache, sessions, and the queue that will handle asynchronous AI requests.

---

## 🛠️ Tech Stack

| Layer            | Technology                                             |
| ---------------- | ------------------------------------------------------ |
| **Backend**      | Laravel (REST API) · PHP 8.3                           |
| **Frontend**     | React · TypeScript · Vite · Tailwind CSS v4            |
| **Database**     | PostgreSQL 17                                           |
| **Cache/Queues** | Redis 7                                                 |
| **Web Server**   | Nginx                                                   |
| **Containers**   | Docker · Docker Compose                                 |
| **AI**           | OpenAI (provider-agnostic architecture)                |
| **Infra**        | AWS (ECR, EC2, ECS, S3) · *planned*                    |
| **CI/CD**        | GitHub Actions · *planned*                             |

---

## 📂 Project Structure

```
devmind-ai/
│
├── backend/              ← Laravel API
├── frontend/             ← React + TypeScript (Vite)
│
├── docker/
│   ├── nginx/            ← vhost config
│   ├── php/              ← custom PHP-FPM image (Dockerfile, php.ini)
│   └── postgres/
│
├── docker-compose.yml
├── .dockerignore
└── .gitignore
```

The frontend follows a layered structure — `services/` (API clients), `types/` (data contracts), `hooks/` (stateful logic), `components/`, and `features/` — keeping data access, typing, and UI cleanly separated.

---

## 🚀 Getting Started

### Prerequisites

- [Docker](https://www.docker.com/) & Docker Compose
- [Node.js](https://nodejs.org/) 22+ (for the frontend dev server)

### 1. Clone the repository

```bash
git clone https://github.com/marcelomds/devmind-ai-project.git
cd devmind-ai-project
```

### 2. Configure environment

```bash
cp .env.example .env                     # root (Docker)
cp backend/.env.example backend/.env     # Laravel
```

### 3. Bring up the infrastructure

```bash
docker compose up -d --build
```

### 4. Set up the backend

```bash
docker compose exec php composer install
docker compose exec php php artisan key:generate
docker compose exec php php artisan migrate
```

### 5. Start the frontend

```bash
cd frontend
npm install
npm run dev
```

| Service      | URL                              |
| ------------ | -------------------------------- |
| Frontend     | http://localhost:5173            |
| API          | http://localhost:8080/api/v1     |
| Health check | http://localhost:8080/api/v1/health |

---

## 🗺️ Roadmap

- [x] Containerized infrastructure (Nginx, PHP-FPM, PostgreSQL, Redis)
- [x] Versioned REST API (`/api/v1`)
- [x] React + TypeScript frontend with a layered architecture
- [x] End-to-end health check integration
- [ ] Authentication (Laravel Sanctum)
- [ ] Core AI feature & domain modeling
- [ ] Asynchronous AI processing via Redis queues
- [ ] Provider-agnostic AI layer (OpenAI + others)
- [ ] CI/CD pipeline with GitHub Actions
- [ ] Deployment to AWS (ECR → ECS/EC2)

---

## 📄 License

Distributed under the MIT License. See `LICENSE` for details.

---

<div align="center">

Built with care by [**@marcelomds**](https://github.com/marcelomds)

</div>
