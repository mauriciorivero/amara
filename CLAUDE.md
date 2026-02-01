# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

AMARA is a PHP/MySQL web application for managing maternal health programs. It's a monolithic application with a PHP backend (MVC-like pattern) and vanilla JavaScript frontend served as a single `index.html` page. The app tracks mothers, pregnancies, babies, programs, and allied organizations offering support services.

## Architecture

### Backend Stack
- **PHP 7.8+** with PDO for database abstraction
- **MySQL** database (configured via `.env` file in `config/env.php`)
- **Singleton pattern**: Database connection via `Database::getInstance()`
- **No framework**: Manual routing through individual API endpoints

### Directory Structure

**`/config`**
- `Database.php`: Singleton PDO connection with env variable loading
- `env.php`: Database and app configuration (DO NOT commit with real credentials)

**`/model`** - Data entities (11 files)
- Core entities: `Madre.php`, `Embarazo.php`, `Bebe.php`, `Programa.php`, `Aliado.php`, `Ayuda.php`
- Support entities: `MadrePrograma.php`, `Orientadora.php`, `Eps.php`, `Usuario.php`, `LogActividad.php`
- All model classes implement `JsonSerializable` for API responses

**`/dao`** - Data Access Objects (9 files)
- One DAO per major entity (e.g., `MadreDAO.php`, `ProgramaDAO.php`)
- Methods follow pattern: `getAll($limit, $offset, $filters = [])`, `getById($id)`, `create()`, `update()`, `delete()`
- Support filtering and pagination via query parameters
- Use prepared statements with PDO for SQL injection prevention

**`/api`** - REST API endpoints (9 directories)
- Organized by entity: `/api/madres/`, `/api/embarazos/`, `/api/programas/`, etc.
- Common endpoint patterns:
  - `listar.php`: Get paginated lists with filters
  - `obtener.php`: Get single record by ID
  - `guardar.php`: Create or update record
  - `eliminar.php`: Delete record
  - `activos.php`, `por_aliado.php`, etc.: Custom queries
  - `estadisticas.php`: Dashboard stats
- All endpoints return JSON with `success`, `data`, `pagination`, and `error` fields

**`/db_parametrizacion`** - Database schema
- `scrip_bd_amara.sql`: Main schema with all tables
- `scrip_bd_amara_with_inserts.sql`: Schema + sample data
- Per-feature updates: `tabla_programas.sql`, `tabla_ayudas.sql`, etc.

**`/css`** - Single stylesheet
- `styles.css`: All application styles (3000+ lines)
- CSS classes use descriptive names (e.g., `.module-screen`, `.detail-view-container`)

**`/js`** - Single JavaScript file
- `visualBehavior.js` (3565 lines): All frontend logic
- Manages modals, screens, filtering, pagination, AJAX calls
- Uses vanilla DOM manipulation with event listeners
- Global state management via window objects (e.g., `window.currentFilters`, `window.filtersInitialized`)

**`index.html`** - Single-page application
- Large HTML file (88KB+) with all screens and modals inline
- Structure: Dashboard card section + hidden module screens + modals
- Module screens use class `.module-screen` with subviews (list, detail)

### Data Flow Pattern

```
Frontend (index.html + visualBehavior.js)
    ↓ AJAX (fetch/XMLHttpRequest)
API endpoints (/api/*/listar.php, etc.)
    ↓ Instantiate DAO
DAO classes (/dao/*.php)
    ↓ Execute SQL queries
Model classes (/model/*.php, used for type hints)
    ↓ Database queries (via Database singleton)
MySQL (/db_parametrizacion/*.sql)
```

## Common Development Commands

### Database Setup
```bash
# Load initial schema with sample data
mysql -u adminminerva -p minerva < db_parametrizacion/scrip_bd_amara_with_inserts.sql

# Run schema-only (production)
mysql -u adminminerva -p minerva < db_parametrizacion/scrip_bd_amara.sql

# Apply feature-specific updates
mysql -u adminminerva -p minerva < db_parametrizacion/tabla_programas.sql
```

### Configuration
- Copy `.env.example` to `.env.php` (already in config/ as `env.php`)
- Update database credentials in `config/env.php` with actual values
- Values: DB_HOST, DB_NAME, DB_USERNAME, DB_PASSWORD, DB_CHARSET

### Development Server
```bash
# Using MAMP (as currently set up at /Applications/MAMP/htdocs/amara)
# Start MAMP Apache server, then visit http://localhost:8888/amara/

# Or with PHP built-in server:
php -S localhost:8000
```

### Testing API Endpoints
```bash
# List madres with pagination and filters
curl "http://localhost:8888/amara/api/madres/listar.php?page=1&limit=25&search=Maria"

# Get single record
curl "http://localhost:8888/amara/api/madres/obtener.php?id=1"

# Get programs for an ally
curl "http://localhost:8888/amara/api/programas/por_aliado.php?aliado_id=5"

# Dashboard statistics
curl "http://localhost:8888/amara/api/embarazos/estadisticas.php"
```

## Key Implementation Patterns

### DAO Methods for Listing
All `getAll()` methods accept:
- `$limit`: Records per page (default 25)
- `$offset`: Pagination offset
- `$filters`: Array of filter conditions

Returns array of model objects. Paired with `countAll($filters)` for total count.

**Example filter usage**:
```php
$filters = ['search' => 'ana', 'estado' => 'activo'];
$dao = new MadreDAO();
$madres = $dao->getAll(25, 0, $filters);
$total = $dao->countAll($filters);
```

### API Response Format
All endpoints return JSON:
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "total": 150,
    "page": 1,
    "limit": 25,
    "pages": 6
  }
}
```

Error responses:
```json
{
  "success": false,
  "error": "Description of what went wrong"
}
```

### Frontend State Management
- Filters tracked in `window.currentFilters` object
- Pagination state via page/limit parameters
- Module screens toggle via `.active` class
- Detail views rendered into `.detail-view-container` elements

### Adding a New Module (e.g., "Actividades")
1. **Create model**: `model/Actividad.php` with properties and getters/setters
2. **Create DAO**: `dao/ActividadDAO.php` with CRUD methods
3. **Create API endpoints**: `api/actividades/listar.php`, `obtener.php`, `guardar.php`, `eliminar.php`
4. **Add HTML structure** in `index.html`:
   - Dashboard card in `.info-section`
   - Module screen (`#actividades-screen`) with list and detail views
   - Modal for creating/editing (`#actividadModal`)
5. **Add JavaScript** in `js/visualBehavior.js`:
   - `openActividadesScreen()` function
   - `loadActividades()` to fetch from API
   - Modal handlers for add/edit/delete
   - Detail view rendering functions
6. **Add CSS** classes in `css/styles.css` for styling
7. **Update database**: Add new table via SQL in `db_parametrizacion/`

### Filtering Implementation
Each module with filters follows this pattern:
1. HTML has filter inputs with data attributes
2. JavaScript event listeners detect filter changes
3. On change: gather filters into object, call API with ?filters
4. API passes filters to DAO `getAll($limit, $offset, $filters)`
5. DAO builds WHERE clauses based on filter array
6. Results re-rendered in list view

**Example**: See `openMadresModal()` and `setupFilterListeners()` in `js/visualBehavior.js`

## Important Files to Know

**Critical paths for feature implementation**:
- Adding a field to a module? Update: `model/*.php`, `dao/*.php`, `api/*/guardar.php`, `index.html` forms, `db_parametrizacion/*.sql`
- Adding a filter? Update: `dao/*.php`, `api/*/listar.php`, `index.html` filter inputs, `js/visualBehavior.js` listeners
- Fixing a bug? Check: `model/` for data structure, `dao/` for query logic, `api/` for endpoint logic, `js/visualBehavior.js` for frontend rendering

**Recent feature additions**:
- `feat: Implement 'Aliados' and 'Programas'` (commit 7f6517e): Full CRUD + pagination + filtering
- `feat: Implement 'Ayudas' module` (commit 381affb): Support module with help/assistance tracking
- `feat: Add Embarazos y Bebés module` (commit 7d1ec96): Pregnancy tracking with search

## Code Style Notes

- PHP: Snake_case for database columns, camelCase for PHP properties
- Models: All properties private, accessed via getters/setters
- Filters: Passed as arrays, built into WHERE clauses with parameterized queries
- JavaScript: Functions are global (no modules/imports), use event delegation where possible
- CSS: BEM-like naming with descriptive classes, grid/flexbox for layouts

## Git Workflow

- Main branch is production-ready
- Recent commits show feature additions (modules) and bug fixes
- Implementation docs often committed alongside code (e.g., `IMPLEMENTACION_EMBARAZOS.md`, `walkthrough.md`)
- Temporary docs added to .gitignore as of commit a458b70
