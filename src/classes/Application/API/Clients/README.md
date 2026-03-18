# Submodule: API / Clients

## Purpose

Manages API client registrations and their API keys using the framework's
Collection/Record pattern. An API client represents an external application
or service authorized to consume the API. Each client can have multiple API
keys, each with its own activation status and method permissions.

The submodule also provides the complete admin UI for managing clients and
keys: the `APIClientsArea` screen with modes for listing, creating, and
viewing clients, plus sub-modes for key management.

---

## Key Concepts

- **API Client (Collection/Record):** `APIClientsCollection` / `APIClientRecord`
  manage client registrations stored in `api_clients`. Each client has a label,
  a unique foreign ID, activation status, and a creation audit trail.
- **API Keys (Child Collection/Record):** `APIKeysCollection` / `APIKeyRecord`
  are child records of a client. Each key is a bearer token with its own label,
  activation status, method whitelist, and audit fields. Keys are generated
  automatically on creation.
- **API Key Authentication:** `APIKeyMethodInterface` / `APIKeyMethodTrait`
  allow API methods to require bearer token authentication. The key is read
  from the `Authorization` header via the `APIKeyParam` / `APIKeyHandler`
  pipeline.
- **Method Permissions:** `APIKeyMethods` manages the whitelist of API methods
  a specific key is authorized to call.
- **Admin UI:** `APIClientsArea` provides the full screen hierarchy:
  - `ClientsListMode` — paginated client list.
  - `CreateClientMode` — creation form.
  - `ViewClientMode` — client detail view with sub-modes:
    - `ClientStatusSubmode` — activation status.
    - `ClientSettingsSubmode` — label and foreign ID editing.
    - `APIKeysSubmode` — key list and management with actions for
      creating, viewing status, and editing settings of individual keys.
- **URL Classes:** `APICollectionURLs`, `APIClientRecordURLs`,
  `APIKeyCollectionURLs`, `APIKeyURLs` provide type-safe URL building for
  all admin screens.
- **Screen Rights:** `APIScreenRights` defines the right constants for
  accessing the various client management screens.
- **Request Types:** `APIClientRequestInterface` / `APIClientRequestType` /
  `APIClientRequestTrait` provide typed access to the client record from
  admin screen request parameters.

---

## Folder Structure

| Directory | Contents |
|---|---|
| `Clients/` | Collection/Record for clients, filter criteria/settings, client exception. |
| `Clients/Keys/` | Child collection/record for API keys, filter criteria/settings, key exception, method permissions. |
| `Clients/API/` | `APIKeyMethodInterface`, `APIKeyMethodTrait` — bearer token authentication for API methods. |
| `Clients/API/Params/` | `APIKeyParam`, `APIKeyHandler` — parameter and handler for API key extraction. |
| `Admin/` | Admin screens, URL classes, screen rights, request types. |
| `Admin/Screens/` | Screen area and modes for client management. |
| `Admin/Traits/` | Shared interface/trait pairs for client mode and key action screens. |
| `Admin/RequestTypes/` | Typed request parameter access for client records. |

---

## Database Tables

| Table | Primary Key | Purpose |
|---|---|---|
| `api_clients` | `api_client_id` | API client registrations. |
| `api_keys` | `api_key_id` | API keys (child of `api_clients`). |
| `api_key_methods` | composite | Method whitelist per API key. |

---

## Integration Points

- **`api` module** provides `APIManager` and `APIMethodInterface` that client
  authentication integrates with.
- **`api-openapi`** submodule generates `securitySchemes` for key-authenticated
  methods and adds security requirements to their path items.
- **`db-helper` module** provides the Collection/Record base classes.
- **`ui` module** provides the admin screen and URL framework.
