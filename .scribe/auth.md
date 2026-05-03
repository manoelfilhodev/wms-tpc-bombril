# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer <token>"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Use `Authorization: Bearer <token>` com token obtido em `POST /api/v1/auth/login`.
