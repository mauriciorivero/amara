# Implementation Plan - Search for Embarazos y Bebes

## Goal
Add a search filter to the "Embarazos y Bebés" view to filter by mother's name and orientadora's name.

## Proposed Changes

### Backend
#### [MODIFY] `dao/EmbarazoDAO.php`
- Update `getAll($limit, $offset, $search = '')` to accept a search term.
- Modify the SQL query to include a `WHERE` clause filtering by `m.primer_nombre`, `m.primer_apellido`, or `o.nombre`.

#### [MODIFY] `dao/BebeDAO.php`
- Update `getAll($limit, $offset, $search = '')` to accept a search term.
- Modify the SQL query to include a `WHERE` clause filtering by `m.primer_nombre`, `m.primer_apellido`, or `b.nombre`.

#### [MODIFY] `api/embarazos/listar.php`
- Get `search` param from `$_GET`.
- Pass it to `getAll`.

#### [MODIFY] `api/bebes/listar.php`
- Get `search` param from `$_GET`.
- Pass it to `getAll`.

### Frontend
#### [MODIFY] `index.html`
- Add a search input field in the header of the `embarazos-bebes-view`.

#### [MODIFY] `js/visualBehavior.js`
- Update `loadGlobalEmbarazos` and `loadGlobalBebes` to accept a `search` argument and append it to the API URL.
- Add event listener to the search input to trigger these functions with the search value (with debounce).

## Verification Plan
- Type a mother's name in the search box and verify both lists update.
- Type an orientadora's name and verify the pregnancy list updates.
