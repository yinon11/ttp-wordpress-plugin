# WordPress OAuth Migration Guide: POST → GET Redirect

## Overview

This document outlines the changes needed to migrate from server-to-server POST to browser-based GET redirect for WordPress OAuth, meeting WordPress.org security requirements.

---

## 1. Updated Java Backend Code

### File: `smartTerminalServerJava/smart/src/main/java/com/smartTerminal/smart/public_controllers/WordPressOAuthController.java`

**Replace the `authorize()` method (lines 110-255) with:**

```java
/**
 * Step 2: Create API key and redirect back to WordPress with credentials in URL
 * Called when user clicks "Authorize" button
 */
@PostMapping("/api/connect/wordpress/authorize")
public ResponseEntity<?> authorize(
        HttpSession session,
        HttpServletRequest request) {

    String clientIp = getClientIp(request);

    // ===== VERIFY USER IS AUTHENTICATED =====
    User currentUser = authService.getUserFromRequest(request);
    if (currentUser == null) {
        logger.warn("❌ WordPress OAuth: Authorize called without authentication from {}", clientIp);
        return ResponseEntity.status(HttpStatus.UNAUTHORIZED)
                .body(Map.of("error", "Authentication required"));
    }

    // ===== GET SESSION DATA =====
    String redirectUri = (String) session.getAttribute("wp_redirect_uri");
    String secret = (String) session.getAttribute("wp_secret");
    String siteUrl = (String) session.getAttribute("wp_site_url");
    String siteName = (String) session.getAttribute("wp_site_name");
    String wpNonce = (String) session.getAttribute("wp_nonce"); // NEW: Get nonce from session

    if (redirectUri == null || secret == null || siteUrl == null) {
        logger.warn("❌ WordPress OAuth: Missing session data for user {} from {}",
                currentUser.getTtpid(), clientIp);
        return ResponseEntity.badRequest()
                .body(Map.of("error", "Invalid session. Please restart the connection process."));
    }

    // ===== CLEAR SESSION DATA (one-time use) =====
    session.removeAttribute("wp_redirect_uri");
    session.removeAttribute("wp_secret");
    session.removeAttribute("wp_site_url");
    session.removeAttribute("wp_site_name");
    session.removeAttribute("wp_nonce");

    logger.info("🔑 WordPress OAuth: Creating API key for user {} for site: {}",
            currentUser.getTtpid(), siteUrl);

    // ===== CREATE API KEY =====
    String hostname;
    try {
        hostname = new URL(siteUrl).getHost();
    } catch (MalformedURLException e) {
        hostname = siteUrl;
    }

    String keyName = "WordPress: " + (siteName != null && !siteName.isEmpty() ? siteName : hostname);
    String keyDescription = "API key for WordPress integration (" + siteUrl + ")";

    ApiKey apiKey;
    String plainTextKey;
    try {
        Map<String, Object> createResult = apiKeyService.createApiKey(
                currentUser,
                keyName,
                keyDescription,
                Arrays.asList("read", "write"),
                "wordpress",
                hostname
        );
        apiKey = (ApiKey) createResult.get("apiKey");
        plainTextKey = (String) createResult.get("plainTextKey");

        if (apiKey == null || plainTextKey == null) {
            throw new RuntimeException("Failed to create API key: invalid response from service");
        }

        logger.info("🔑 WordPress OAuth: Created API key ID: {} for user: {}", 
                apiKey.getId(), currentUser.getTtpid());
    } catch (Exception e) {
        logger.error("❌ WordPress OAuth: Failed to create API key for user {}: {}",
                currentUser.getTtpid(), e.getMessage());
        return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                .body(Map.of("error", "Failed to create API key: " + e.getMessage()));
    }

    // ===== BUILD REDIRECT URL WITH CREDENTIALS =====
    try {
        // Parse the redirect URI to add query parameters
        URL redirectUrl = new URL(redirectUri);
        StringBuilder redirectBuilder = new StringBuilder(redirectUrl.getProtocol())
                .append("://")
                .append(redirectUrl.getHost());
        if (redirectUrl.getPort() != -1) {
            redirectBuilder.append(":").append(redirectUrl.getPort());
        }
        redirectBuilder.append(redirectUrl.getPath());

        // Build query string with credentials
        Map<String, String> queryParams = new LinkedHashMap<>();
        queryParams.put("api_key", plainTextKey);
        queryParams.put("app_id", currentUser.getAppId());
        queryParams.put("email", currentUser.getEmail());
        queryParams.put("secret", secret);
        
        // CRITICAL: Include WordPress nonce if present
        if (wpNonce != null && !wpNonce.isEmpty()) {
            queryParams.put("_wpnonce", wpNonce);
        }

        // URL-encode all parameters
        StringBuilder queryString = new StringBuilder();
        boolean first = true;
        for (Map.Entry<String, String> entry : queryParams.entrySet()) {
            if (!first) queryString.append("&");
            queryString.append(URLEncoder.encode(entry.getKey(), StandardCharsets.UTF_8))
                      .append("=")
                      .append(URLEncoder.encode(entry.getValue(), StandardCharsets.UTF_8));
            first = false;
        }

        String finalRedirectUrl = redirectBuilder.toString() + "?" + queryString.toString();

        logger.info("📤 WordPress OAuth: Redirecting user {} to WordPress with credentials (site: {})",
                currentUser.getTtpid(), siteUrl);

        // Option 1: Return 302 redirect (browser will follow automatically)
        // This is the preferred method - browser handles redirect automatically
        return ResponseEntity.status(HttpStatus.FOUND)
                .header(HttpHeaders.LOCATION, finalRedirectUrl)
                .build();
        
        // Option 2: Return redirect URL in JSON (if Option 1 doesn't work due to CORS)
        // Uncomment this and comment Option 1 if browser doesn't follow redirect:
        // return ResponseEntity.ok(Map.of(
        //     "redirect_url", finalRedirectUrl,
        //     "success", true
        // ));

    } catch (Exception e) {
        logger.error("❌ WordPress OAuth: Failed to build redirect URL: {}", e.getMessage());

        // Rollback: delete the API key
        try {
            boolean deleted = apiKeyService.deleteApiKey(apiKey.getId(), currentUser.getTtpid());
            if (!deleted) {
                logger.warn("⚠️ WordPress OAuth: API key deletion returned false during rollback");
            }
        } catch (Exception rollbackError) {
            logger.error("❌ WordPress OAuth: Failed to rollback API key: {}", rollbackError.getMessage());
        }

        return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                .body(Map.of("error", "Failed to build redirect URL: " + e.getMessage()));
    }
}
```

**Also update `validateRequest()` to store the nonce:**

```java
@PostMapping("/api/connect/wordpress/validate")
public ResponseEntity<?> validateRequest(
        @RequestBody Map<String, String> requestBody,
        HttpSession session,
        HttpServletRequest request) {

    String clientIp = getClientIp(request);

    String redirectUri = requestBody.get("redirect_uri");
    String secret = requestBody.get("secret");
    String siteUrl = requestBody.get("site_url");
    String siteName = requestBody.get("site_name");
    String wpNonce = requestBody.get("_wpnonce"); // NEW: Get nonce from request

    logger.info("🔌 WordPress OAuth: Validation request from {} for site: {}", clientIp, siteUrl);

    // Check required fields
    if (redirectUri == null || secret == null || siteUrl == null) {
        logger.warn("❌ WordPress OAuth: Missing required fields from {}", clientIp);
        return ResponseEntity.badRequest()
                .body(Map.of("valid", false, "error", "Missing required fields"));
    }

    try {
        // ===== VALIDATION =====
        validateWordPressConnectRequest(redirectUri, siteUrl, secret, null);

        // ===== STORE IN SESSION =====
        session.setAttribute("wp_redirect_uri", redirectUri);
        session.setAttribute("wp_secret", secret);
        session.setAttribute("wp_site_url", siteUrl);
        session.setAttribute("wp_site_name", siteName);
        if (wpNonce != null && !wpNonce.isEmpty()) {
            session.setAttribute("wp_nonce", wpNonce); // NEW: Store nonce
        }

        logger.info("✅ WordPress OAuth: Validated and stored session for site: {}", siteUrl);

        // Return success
        Map<String, Object> result = new HashMap<>();
        result.put("valid", true);
        result.put("site_url", siteUrl);
        result.put("site_name", siteName);

        return ResponseEntity.ok(result);

    } catch (SecurityException e) {
        logger.warn("❌ WordPress OAuth: Validation failed from {}: {}", clientIp, e.getMessage());
        return ResponseEntity.badRequest()
                .body(Map.of("valid", false, "error", e.getMessage()));

    } catch (Exception e) {
        logger.error("❌ WordPress OAuth: Unexpected error from {}", clientIp, e);
        return ResponseEntity.status(HttpStatus.INTERNAL_SERVER_ERROR)
                .body(Map.of("valid", false, "error", "An unexpected error occurred"));
    }
}
```

**Add import at top of file:**

```java
import java.net.URLEncoder;
import java.nio.charset.StandardCharsets;
import java.util.LinkedHashMap;
```

---

## 2. Backend Files/Methods to REMOVE or DEPRECATE

### File: `WordPressOAuthController.java`

| Method/Code | Lines | What to Remove | Reason |
|------------|-------|----------------|--------|
| `restTemplate.postForEntity()` call | 200-204 | POST request to WordPress `redirect_uri` | Replaced with HTTP 302 redirect |
| `HttpEntity<MultiValueMap>` POST body | 187-196 | Form data preparation for POST | No longer needed - using URL params |
| `MediaType.APPLICATION_FORM_URLENCODED` | 194 | POST content type | Not needed for redirect |
| POST response handling | 206-225 | Handling WordPress POST response | Replaced with redirect |
| Rollback on POST failure | 213-221, 242-250 | API key deletion on POST failure | Rollback now happens on redirect build failure |

**Note:** The `RestTemplate` field and constructor can be removed if not used elsewhere.

---

## 3. Backend Public Endpoints - NO REMOVALS NEEDED

**All endpoints remain active:**

| Endpoint | Method | Status | Notes |
|----------|--------|--------|-------|
| `/api/connect/wordpress/validate` | POST | **KEEP** | Still needed - validates request and stores in session |
| `/api/connect/wordpress/authorize` | POST | **KEEP** | Still needed - behavior changes from POST to redirect |
| `/api/public/wordpress/agents` | GET/POST/PUT | **KEEP** | Used by WordPress plugin for agent management |
| `/api/public/wordpress/voices` | GET | **KEEP** | Used by WordPress plugin |
| `/api/public/wordpress/credits` | GET | **KEEP** | Used by WordPress plugin |
| `/api/public/wordpress/generate-prompt` | POST | **KEEP** | Used by WordPress plugin |

**No endpoints need to be removed** - the OAuth flow still uses the same endpoints, just changes the callback mechanism.

---

## 4. Frontend Files/Methods to REMOVE or UPDATE

### File: `smart_terminal_browser/src/pages/WordPressConnect.jsx`

| Component/Method | Lines | What to Change | Reason |
|-----------------|-------|----------------|--------|
| `handleAuthorize()` | 234-280 | **UPDATE**: Remove POST completion waiting logic | Backend now redirects immediately, no need to wait |
| Success state UI | 341-393 | **REMOVE**: "Return to WordPress" button and success page | User is automatically redirected, never sees this page |
| `handleReturnToWordPress()` | 290-294 | **REMOVE**: Function no longer needed | Redirect happens automatically |
| Progress animation | 164-232 | **SIMPLIFY**: Remove POST completion tracking | No longer waiting for POST response |
| `backendFinishedRef` | 24-26 | **REMOVE**: Refs for tracking POST completion | No longer needed |
| `backendResultRef` | 24-26 | **REMOVE**: Refs for tracking POST completion | No longer needed |
| `backendErrorRef` | 24-26 | **REMOVE**: Refs for tracking POST completion | No longer needed |
| `connectedSite` state | 15 | **REMOVE**: No longer needed | User redirected immediately |
| "Continue in background" button | 434-436 | **REMOVE**: No longer needed | Redirect is immediate |

**Updated `handleAuthorize()` method:**

```javascript
const handleAuthorize = async () => {
  // Check if user needs to agree to TOS/Privacy first
  if (needsAgreement && (!agreements.termsOfService || !agreements.privacyPolicy)) {
    setError('Please agree to the Terms of Service and Privacy Policy to continue');
    return;
  }
  
  setIsAuthorizing(true);
  setStatus('authorizing');
  setProgress(50);
  setProgressMessage('Creating API key and redirecting...');
  
  try {
    const response = await fetch(buildApiUrl('/api/connect/wordpress/authorize'), {
      method: 'POST',
      credentials: 'include',
      headers: {
        ...buildAuthenticatedHeaders(),
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        agreedToTerms: needsAgreement ? agreements.termsOfService : undefined,
        agreedToPrivacy: needsAgreement ? agreements.privacyPolicy : undefined,
        agreementDate: needsAgreement ? new Date().toISOString() : undefined,
      }),
      redirect: 'follow', // Follow redirects automatically
    });

    // If backend returns 302, browser will automatically follow it
    // If backend returns JSON with redirect_url (fallback), handle it
    if (response.ok) {
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('application/json')) {
        // Fallback: Backend returned JSON with redirect_url
        const data = await response.json();
        if (data.redirect_url) {
          window.location.href = data.redirect_url;
          return;
        }
      }
      // If we get here and response is ok, browser should have redirected
      // If not, something went wrong
      throw new Error('Redirect did not occur');
    } else {
      const data = await response.json();
      throw new Error(data.error || 'Authorization failed');
    }
  } catch (err) {
    console.error('Authorization failed:', err);
    setError(err.message || 'Failed to authorize. Please try again.');
    setStatus('error');
    setIsAuthorizing(false);
  }
};
```

**Note:** The `redirect: 'follow'` option tells fetch to automatically follow 302 redirects. The browser will handle the redirect to WordPress automatically. If CORS prevents automatic redirect following, use Option 2 in the backend (return JSON with redirect_url) and handle it in the frontend.

---

## 5. Summary Table

| File | Type | What to Remove/Change | Reason |
|------|------|----------------------|--------|
| `WordPressOAuthController.java` | Backend | Replace POST logic with 302 redirect | WordPress.org requires authenticated redirect, not server-to-server POST |
| `WordPressOAuthController.java` | Backend | Remove `RestTemplate.postForEntity()` | No longer POSTing to WordPress |
| `WordPressOAuthController.java` | Backend | Remove POST response handling | Redirect replaces POST response |
| `WordPressOAuthController.java` | Backend | Add nonce storage/forwarding | WordPress requires nonce verification |
| `WordPressConnect.jsx` | Frontend | Remove success page UI | User redirected automatically, never sees success page |
| `WordPressConnect.jsx` | Frontend | Remove "Return to WordPress" button | Not needed - redirect is automatic |
| `WordPressConnect.jsx` | Frontend | Remove POST completion tracking refs | No longer waiting for POST response |
| `WordPressConnect.jsx` | Frontend | Simplify `handleAuthorize()` | Just call endpoint, browser handles redirect |

---

## 6. Additional Changes Needed

### Database Changes
- **None** - No database schema changes required

### Configuration Changes
- **None** - No config file changes needed

### Testing Checklist
- [ ] Test OAuth flow with valid WordPress site
- [ ] Test OAuth flow with invalid/missing nonce
- [ ] Test OAuth flow with expired secret
- [ ] Test OAuth flow with user not logged in (should redirect to login first)
- [ ] Verify nonce is passed correctly in redirect URL
- [ ] Verify all URL parameters are properly encoded
- [ ] Verify 302 status code is returned
- [ ] Test rollback on API key creation failure
- [ ] Test rollback on redirect URL build failure
- [ ] Verify WordPress callback receives all required parameters

### Security Considerations
1. **Nonce Verification**: WordPress will verify the nonce on callback - ensure it's passed correctly
2. **URL Encoding**: All parameters must be properly URL-encoded to prevent injection
3. **HTTPS Only**: Redirect must use HTTPS (already enforced in validation)
4. **Domain Matching**: Redirect URI domain must match site URL (already enforced)
5. **One-Time Secret**: Secret is still validated and deleted after use
6. **Session Cleanup**: All session attributes cleared after use

### Migration Notes
- **Backward Compatibility**: Old WordPress plugins using POST will fail - this is expected
- **WordPress Plugin Version**: WordPress plugin must be updated to v1.9.58+ to use new flow
- **User Experience**: Users will be redirected immediately after clicking "Authorize" - smoother UX
- **Error Handling**: If redirect fails, API key is rolled back automatically

---

## 7. Implementation Steps

1. **Update Backend** (`WordPressOAuthController.java`)
   - Replace `authorize()` method with redirect logic
   - Update `validateRequest()` to store nonce
   - Add URL encoding imports

2. **Update Frontend** (`WordPressConnect.jsx`)
   - Simplify `handleAuthorize()` to just call endpoint
   - Remove success page UI
   - Remove POST completion tracking

3. **Test Thoroughly**
   - Test all scenarios in testing checklist
   - Verify nonce flow works correctly
   - Test error cases

4. **Deploy**
   - Deploy backend changes first
   - Deploy frontend changes
   - Monitor for errors

5. **Update Documentation**
   - Update API documentation
   - Update WordPress plugin README
   - Update changelog

---

## 8. Rollback Plan

If issues occur:
1. Revert `WordPressOAuthController.java` to POST logic
2. Revert `WordPressConnect.jsx` to success page
3. WordPress plugin will continue working with old flow

**Note:** WordPress plugin v1.9.58+ requires new backend flow. Older plugin versions will fail with new backend.
