/**
 * Admin Panel Architecture Design
 *
 * Following VoltAgent skill standards:
 * - ui-designer: Component-based design system, dark mode, accessibility (WCAG 2.1 AA)
 * - backend-developer: RESTful API, RBAC, audit logging, caching
 * - api-designer: Consistent naming, pagination, versioning, error responses
 * - fullstack-developer: End-to-end flows, shared types, optimistic updates
 * - database-optimizer: Indexed queries, connection pooling
 * - performance-engineer: Sub-100ms targets, cache strategies
 *
 * Architecture Layers:
 * ┌─────────────────────────────────────────────────────────────┐
 * │                      Admin Panel                            │
 * ├─────────────┬───────────────┬──────────────┬────────────────┤
 * │  Views      │  Components   │  Widgets     │  Charts        │
 * │  (Blade)    │  (Reusable)   │  (Stats)     │  (Analytics)   │
 * ├─────────────┴───────────────┴──────────────┴────────────────┤
 * │                   Controller Layer                          │
 * │         (AdminController, API endpoints)                    │
 * ├─────────────────────────────────────────────────────────────┤
 * │                   Service Layer                             │
 * │    (AdminService, UserService, RoleService, LogService)     │
 * ├─────────────────────────────────────────────────────────────┤
 * │                   Repository Layer                          │
 * │          (UserRepo, RoleRepo, AuditLogRepo)                 │
 * ├─────────────────────────────────────────────────────────────┤
 * │                   Database (SQLite/MySQL)                   │
 * └─────────────────────────────────────────────────────────────┘
 *
 * Routes Structure:
 * /admin              → Dashboard (analytics, widgets)
 * /admin/users        → User management (CRUD)
 * /admin/roles        → Role & permissions
 * /admin/settings     → System configuration
 * /admin/logs         → Audit trail
 * /admin/analytics    → Deep analytics
 *
 * API Endpoints (JSON):
 * GET    /api/admin/users           → List users (paginated)
 * POST   /api/admin/users           → Create user
 * PUT    /api/admin/users/{id}      → Update user
 * DELETE /api/admin/users/{id}      → Delete user
 * GET    /api/admin/roles           → List roles
 * POST   /api/admin/roles           → Create role
 * GET    /api/admin/settings        → Get settings
 * PUT    /api/admin/settings        → Update settings
 * GET    /api/admin/logs            → List audit logs (paginated)
 *
 * Security:
 * - All routes behind AdminMiddleware (role_id = 1)
 * - CSRF tokens on all state-changing operations
 * - Rate limiting: 100 req/min for admin, 30 for API
 * - Audit log every write operation
 * - Session timeout: 30 min for admin
 */
