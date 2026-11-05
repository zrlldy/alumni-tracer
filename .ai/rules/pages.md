---
paths:
  - app/Filament/Pages/Dashboard.php
---

# Pages

## Keep alumni overview separate from staff widgets
The shared Filament Dashboard serves staff and alumni by existing RBAC. Only an active my_records allocation with Dashboard and Alumni page/action grants may mount the owned alumni overview; resolve data by persisted alumnis.user_id, never mutable Student ID or email. Roleless users receive a safe notice and alumni never receive the staff widget grid. Core profile completion is binary across the eight required identity/contact/academic fields; work and education are displayed separately without inferred verification.
