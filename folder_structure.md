# Project Restructure & URL Standardization - Design

## Architecture Overview

### High-Level Design

The project restructure follows a layered architecture pattern:

```
┌─────────────────────────────────────────────────────────┐
│                    Public Layer                          │
│  (public/index.php, public/dashboard/, public/assets/)  │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                    API Layer (v1)                        │
│  (src/api/auth/, src/api/reports/, src/api/stations/)  │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                 Business Logic Layer                     │
│  (src/controllers/, src/models/, src/utils/)            │
└─────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────┐
│                  Infrastructure Layer                    │
│  (src/config/, src/middleware/, Database)               │
└─────────────────────────────────────────────────────────┘
```

### Directory Structure Design

#### PowerGuide Structure
```
PowerGuide/
├── public/
│   ├── index.php                    # Main entry point
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   ├── dashboard/
│   │   ├── user.php
│   │   ├── admin.php
│   │   └── electric.php
│   └── uploads/
│
├── src/
│   ├── api/
│   │   ├── auth/
│   │   │   ├── login.php
│   │   │   ├── register.php
│   │   │   ├── logout.php
│   │   │   └── google_auth.php
│   │   ├── reports/
│   │   │   ├── list.php
│   │   │   ├── create.php
│   │   │   ├── get.php
│   │   │   ├── update.php
│   │   │   └── delete.php
│   │   ├── stations/
│   │   │   ├── list.php
│   │   │   ├── create.php
│   │   │   ├── get.php
│   │   │   ├── update.php
│   │   │   └── delete.php
│   │   └── users/
│   │       ├── get.php
│   │       ├── update.php
│   │       └── update_password.php
│   │
│   ├── config/
│   │   ├── app.php                  # App configuration
│   │   ├── connection.php           # Database connection
│   │   └── env.php                  # Environment loader
│   │
│   ├── middleware/
│   │   ├── requireAuth.php          # Authentication middleware
│   │   ├── validateJson.php         # JSON validation
│   │   └── errorHandler.php         # Error handling
│   │
│   ├── models/
│   │   ├── User.php
│   │   ├── Report.php
│   │   └── Station.php
│   │
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── ReportController.php
│   │   ├── StationController.php
│   │   └── UserController.php
│   │
│   └── utils/
│       ├── ApiClient.php            # HTTP client for API calls
│       ├── Response.php             # Response formatter
│       ├── Validator.php            # Input validation
│       └── Logger.php               # Logging utility
│
├── tests/
│   ├── unit/
│   └── integration/
│
├── .env
├── .env.example
├── composer.json
├── composer.lock
└── README.md
```

#### CrowdsourcedAPI Structure
```
CrowdsourcedAPI/
├── public/
│   └── index.php                    # Main entry point
│
├── src/
│   ├── api/
│   │   ├── reports/
│   │   │   ├── list.php
│   │   │   ├── create.php
│   │   │   ├── get.php
│   │   │   ├── update.php
│   │   │   └── delete.php
│   │   ├── stations/
│   │   │   ├── list.php
│   │   │   ├── create.php
│   │   │   ├── get.php
│   │   │   ├── update.php
│   │   │   └── delete.php
│   │   └── services/
│   │       └── geocode.php
│   │
│   ├── config/
│   │   ├── app.php
│   │   ├── connection.php
│   │   └── env.php
│   │
│   ├── middleware/
│   │   ├── requireAuth.php
│   │   ├── validateJson.php
│   │   └── errorHandler.php
│   │
│   ├── models/
│   │   ├── Report.php
│   │   └── Station.php
│   │
│   ├── controllers/
│   │   ├── ReportController.php
│   │   └── StationController.php
│   │
│   └── utils/
│       ├── Response.php
│       ├── Validator.php
│       ├── Logger.php
│       └── GeocodingService.php
│
├── database/
│   ├── powerguide.sql
│   └── migrations/
│
├── tests/
│   ├── unit/
│   └── integration/
│
├── .env
├── .env.example
├── composer.json
├── composer.lock
└── README.md
```

## API Design

### URL Structure
```
Base URL: http://localhost/ProjectName/public/api/v1
```

### Request/Response Format

#### Request Headers
```
Content-Type: application/json
Authorization: Bearer {jwt_token} (if required)
```

#### Response Format (Success)
```json
{
  "success": true,
  "data": { /* response data */ },
  "message": "Operation successful"
}
```

#### Response Format (Error)
```json
{
  "success": false,
  "error": "error_code",
  "message": "Human readable error message"
}
```

### API Endpoints

#### PowerGuide Endpoints

**Authentication**
- `POST /api/v1/auth/login` - User login
- `POST /api/v1/auth/register` - User registration
- `POST /api/v1/auth/logout` - User logout
- `POST /api/v1/auth/google` - Google OAuth login

**Reports**
- `GET /api/v1/reports` - List all reports
- `POST /api/v1/reports` - Create new report
- `GET /api/v1/reports/{id}` - Get report details
- `PUT /api/v1/reports/{id}` - Update report
- `DELETE /api/v1/reports/{id}` - Delete report

**Stations**
- `GET /api/v1/stations` - List all stations
- `POST /api/v1/stations` - Create new station
- `GET /api/v1/stations/{id}` - Get station details
- `PUT /api/v1/stations/{id}` - Update station
- `DELETE /api/v1/stations/{id}` - Delete station

**Users**
- `GET /api/v1/users/{id}` - Get user profile
- `PUT /api/v1/users/{id}` - Update user profile
- `PUT /api/v1/users/{id}/password` - Update password

#### CrowdsourcedAPI Endpoints

**Reports**
- `GET /api/v1/reports` - List all reports
- `POST /api/v1/reports` - Create new report
- `GET /api/v1/reports/{id}` - Get report details
- `PUT /api/v1/reports/{id}` - Update report
- `DELETE /api/v1/reports/{id}` - Delete report

**Stations**
- `GET /api/v1/stations` - List all stations
- `POST /api/v1/stations` - Create new station
- `GET /api/v1/stations/{id}` - Get station details
- `PUT /api/v1/stations/{id}` - Update station
- `DELETE /api/v1/stations/{id}` - Delete station

**Services**
- `GET /api/v1/services/geocode?location={location}` - Geocode location

## Configuration Design

### Environment Variables

#### PowerGuide .env
```
# Application
APP_NAME=PowerGuide
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/PowerGuide/public

# API Configuration
API_VERSION=v1
API_BASE_URL=http://localhost/PowerGuide/public/api/v1
CROWDSOURCED_API_URL=http://localhost/CrowdsourcedAPI/public/api/v1

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=powerguide
DB_USER=root
DB_PASS=

# JWT
JWT_SECRET=your_secret_key_here
JWT_ALGORITHM=HS256
JWT_EXPIRY=86400

# Google OAuth
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost/PowerGuide/public/api/v1/auth/google

# Geocoding
GEOAPIFY_GEOCODING_API_KEY=your_api_key

# Logging
LOG_LEVEL=debug
LOG_PATH=./logs
```

#### CrowdsourcedAPI .env
```
# Application
APP_NAME=CrowdsourcedAPI
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/CrowdsourcedAPI/public

# API Configuration
API_VERSION=v1
API_BASE_URL=http://localhost/CrowdsourcedAPI/public/api/v1

# Database
DB_HOST=localhost
DB_PORT=3306
DB_NAME=powerguide
DB_USER=root
DB_PASS=

# Geocoding
GEOAPIFY_GEOCODING_API_KEY=your_api_key

# Logging
LOG_LEVEL=debug
LOG_PATH=./logs
```

## Core Components Design

### 1. ApiClient (src/utils/ApiClient.php)
```php
class ApiClient {
    private $baseUrl;
    private $timeout = 30;
    private $headers = [];
    
    public function __construct($baseUrl, $headers = [])
    public function get($endpoint, $params = [])
    public function post($endpoint, $data = [])
    public function put($endpoint, $data = [])
    public function delete($endpoint)
    public function setHeader($key, $value)
    private function request($method, $endpoint, $data = [])
}
```

### 2. Response Handler (src/utils/Response.php)
```php
class Response {
    public static function success($data, $message = "Success", $code = 200)
    public static function error($error, $message = "Error", $code = 400)
    public static function json($data, $code = 200)
}
```

### 3. Validator (src/utils/Validator.php)
```php
class Validator {
    public static function required($data, $fields)
    public static function email($email)
    public static function minLength($value, $min)
    public static function maxLength($value, $max)
    public static function enum($value, $allowed)
}
```

### 4. Authentication Middleware (src/middleware/requireAuth.php)
```php
function requireAuth() {
    // Check JWT token or session
    // Return user data or throw error
}
```

## Data Flow

### Report Creation Flow
```
1. User submits form in PowerGuide dashboard
2. JavaScript sends POST to /api/v1/reports
3. PowerGuide API validates input
4. PowerGuide calls CrowdsourcedAPI /api/v1/reports
5. CrowdsourcedAPI stores in database
6. Response returned to PowerGuide
7. PowerGuide returns response to frontend
```

### Authentication Flow
```
1. User logs in via PowerGuide
2. PowerGuide validates credentials
3. JWT token generated
4. Token stored in cookie/localStorage
5. Subsequent requests include token
6. Middleware validates token
7. Request processed if valid
```

## File Migration Strategy

### Phase 1: Create New Structure
- Create all new directories
- Create placeholder files

### Phase 2: Copy and Update Files
- Copy existing files to new locations
- Update include/require paths
- Update hardcoded URLs to use environment variables

### Phase 3: Update Configuration
- Update .env files
- Update config files
- Update database connections

### Phase 4: Testing
- Test all endpoints
- Verify data flow
- Check error handling

## Error Handling Strategy

### HTTP Status Codes
- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `500` - Internal Server Error

### Error Response Format
```json
{
  "success": false,
  "error": "VALIDATION_ERROR",
  "message": "Field 'email' is required",
  "details": {
    "field": "email",
    "rule": "required"
  }
}
```

## Security Considerations

1. **Input Validation**: All inputs validated before processing
2. **SQL Injection Prevention**: Use prepared statements
3. **CORS**: Configure appropriate CORS headers
4. **JWT**: Secure token generation and validation
5. **Environment Variables**: Sensitive data in .env files
6. **Error Messages**: Don't expose sensitive information in errors

## Performance Considerations

1. **Caching**: Implement caching for frequently accessed data
2. **Database Indexing**: Index frequently queried columns
3. **API Rate Limiting**: Implement rate limiting for API endpoints
4. **Lazy Loading**: Load data on demand
5. **Compression**: Enable gzip compression for responses

## Testing Strategy

### Unit Tests
- Test individual functions and methods
- Mock external dependencies
- Test error cases

### Integration Tests
- Test API endpoints
- Test database operations
- Test inter-service communication

### Test Coverage
- Aim for 80%+ code coverage
- Focus on critical paths
- Test error scenarios

## Deployment Considerations

1. **Environment-specific Configuration**: Different .env for dev/staging/production
2. **Database Migrations**: Version control for database changes
3. **Backward Compatibility**: Maintain API versioning
4. **Monitoring**: Log all API calls and errors
5. **Documentation**: Keep API documentation updated
