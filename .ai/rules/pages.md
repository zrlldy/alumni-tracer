---
paths:
  - app/Filament/Pages/Dashboard.php
---

# Pages

## Keep alumni overview separate from staff widgets
The shared Filament Dashboard serves staff and alumni by existing RBAC. Only an active my_records allocation with Dashboard and Alumni page/action grants may mount the owned alumni overview; resolve data by persisted alumnis.user_id, never mutable Student ID or email. Roleless users receive a safe notice and alumni never receive the staff widget grid. Core profile completion is binary across the eight required identity/contact/academic fields; work and education are displayed separately without inferred verification.

## Require Dashboard grant for allocated accounts
The shared /online Dashboard remains reachable by roleless accounts only to show a safe access notice. An account with user_allocation_id set must pass the existing Dashboard page grant through hasPageAccess(), including direct HTTP and Livewire access. Keep the owned form's additional alumni grant, action, scope and persisted owner checks; preserve staff widgets only for active staff scopes.
