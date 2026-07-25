<div align="center">

# 🧠 DevMind AI

### Continuous AI-powered code intelligence — a health monitor for your codebase.

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

## 🎯 The Problem

Every time a developer opens a Pull Request, someone experienced should ideally review it and say: *"this has a performance issue"*, *"this function is undocumented"*, *"this pattern will cause trouble later"*. In practice, that depends on senior developers having free time — which they rarely do.

Editor assistants like Copilot and Claude Code help developers **while they write**, one person at a time, inside the editor. What they don't do is **watch a project over time**: keep history, measure whether quality is improving or degrading, enforce a team's specific standards, and surface metrics for the tech lead.

**DevMind fills that gap.**

## 💡 What DevMind Is

> A platform that watches a project's code over time, runs automated AI analyses on every change, and shows the evolution of quality on a dashboard — instead of helping one developer at a time in the editor, it looks after the health of the entire project, continuously.

## ⚙️ How It Works

The full flow, in four moments:

1. **Connect a repository.** Someone links a GitHub repository to DevMind. From then on, DevMind "listens" to that repo.

2. **Receive the change.** When a Pull Request is opened, GitHub notifies DevMind automatically (via a **webhook**). DevMind receives the code from that change.

3. **Analyze with AI.** DevMind sends that code to an AI with specific instructions: look for bugs, performance issues, undocumented code, security flaws. The AI returns a list of findings — each with a severity, an explanation, and a suggested fix.

4. **Store and visualize.** Everything is stored and shown on a dashboard: the findings for that PR, and — most valuably — the **evolution over time**. *"Technical debt grew 20% this month."* *"Security issues dropped."* A graph the tech lead can glance at to understand project health at once.

## 🧭 What Makes It Different

The value isn't in the AI itself — anyone can call the same OpenAI API. The value is in **everything DevMind builds around it**: the history over time, the evolution metrics, the tech-lead dashboard, the analysis triggered automatically by each PR. The AI is one piece; the product is the continuous monitoring system.

It's the difference between *"help me write this function"* and *"tell me whether my project is getting healthier or sicker week over week."*

---

## 🚦 Current Status

It's worth separating the **vision** (above) from the **present state**, so the scope is clear.

What exists and works **today** is the foundation and the skeleton — not the product features yet:

- ✅ A complete **Docker infrastructure**: web server (Nginx), application (Laravel/PHP-FPM), database (PostgreSQL) and Redis (which will manage the analysis queue). All wired together and validated.
- ✅ A responding **REST API** with a React screen confirming *"API Online"* — proof that backend and frontend communicate end to end.
- 🔨 The **database schema** (four tables: repositories, analyses, findings, users) — the design of where each piece of data lives. *In progress.*

## 🗺️ Roadmap

The project is built in four phases, deliberately ordered so the testable and impressive parts come first, leaving the parts that depend on external infrastructure (GitHub, auth) for last.

**Phase 1 — The Engine** *(current)*
Make the core beat: receive code (pasted manually at first), send it to the AI, store the findings. No GitHub, no login yet — just prove the heart works.

- [x] Containerized infrastructure (Nginx, PHP-FPM, PostgreSQL, Redis)
- [x] Versioned REST API (`/api/v1`)
- [x] React + TypeScript frontend with a layered architecture
- [x] End-to-end health check integration
- [ ] Domain schema (repositories, analyses, findings)
- [ ] Asynchronous AI analysis via Redis queue
- [ ] Structured findings from an AI provider (OpenAI)

**Phase 2 — The Dashboard**
Show those analyses and the quality trend on a polished screen.

- [ ] Analyses list & detail views
- [ ] Findings by severity
- [ ] Quality-over-time chart (technical-debt trend)

**Phase 3 — GitHub Integration**
Replace manual input with a real connection to Pull Requests via webhook.

- [ ] GitHub webhook (signed) on PR events
- [ ] Diff extraction & automatic analysis
- [ ] Provider-agnostic AI layer

**Phase 4 — Authentication & Deployment**
Wrap it up: each user sees only their own repositories, and ship it.

- [ ] Authentication (Laravel Sanctum)
- [ ] CI/CD pipeline with GitHub Actions
- [ ] Deployment to AWS (ECR → ECS/EC2)

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

The frontend runs on the host for instant HMR and talks to the API over HTTP. Inside the network, services resolve each other by name (`postgres`, `redis`), and Redis powers cache, sessions, and the queue that handles asynchronous AI requests.

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

## 📄 License

Distributed under the MIT License. See `LICENSE` for details.

---

<div align="center">

Built with care by [**@marcelomds**](https://github.com/marcelomds)

</div>
