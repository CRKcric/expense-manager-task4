# Security Policy

## Supported Versions
This project is in active development as part of the internship.  
Security patches are applied to the latest version only.

## Reporting a Vulnerability
If you find a security issue, please contact us immediately at:
📧 official@apexplanet.in  

We will review and fix vulnerabilities as quickly as possible.

## Security Practices in This Project
- **Password Hashing**: All passwords are securely hashed using `password_hash()` with `PASSWORD_DEFAULT`.
- **Prepared Statements**: All database queries use PDO prepared statements to prevent SQL Injection.
- **Session Security**:
  - Sessions are regenerated after login (`session_regenerate_id(true)`).
  - Session variables are validated before accessing secure pages.
- **Role-Based Access Control**: 
  - Normal users have access only to their own transactions.
  - Admins have additional access to manage users.
- **Form Validation**:
  - Server-side validation for all inputs.
  - Basic client-side validation with HTML5 attributes.
- **Error Handling**:
  - Errors are caught with try/catch blocks.
  - Database errors are not exposed directly to the user.

## Best Practices for Users
- Use a strong, unique password for your account.
- Always log out after finishing your session.
- Do not share your account credentials with others.
