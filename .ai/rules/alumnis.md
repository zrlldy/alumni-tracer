---
paths:
  - 'app/Filament/Resources/Alumnis/**'
---

# Alumnis

## Keep self-service grant out of staff directory
The shared Dashboard form checks the existing alumnis page grant for my_records accounts. That grant must not open the staff AlumniResource: deny my_records at resource access so navigation and direct resource pages fail, while the owned Dashboard form remains available. Preserve the configured bootstrap Super Administrator and permitted staff scopes; test direct list/create/view/edit routes as well as navigation.
