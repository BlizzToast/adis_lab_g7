# Assignment 4: Performance Evaluation and APIs

## Task 1: Client-Side Performance (Google Lighthouse)

### Method
- Tool: Google Lighthouse
- Target: Roary web application (logged in and logged out states)
- Platform: Desktop

### Results
| Metric | Score/Value |
|--------|------------|
| Performance | 99-100 |
| First Contentful Paint (FCP) | 0.4-0.6s |
| Largest Contentful Paint (LCP) | 0.5-0.8s |
| Cumulative Layout Shift (CLS) | 0.0001 |
| Time to Interactive (TTI) | 0ms |
| Accessibility | 100 |
| Best Practices | 100 |
| SEO | 90 |

### Findings
- Client-side performance is excellent with near-perfect scores
- Main optimization opportunities: font loading (330-1270ms potential savings) and unused CSS (64KB)
- Current 100-post limit is inefficient; dynamic loading recommended

## Task 2: Server-Side Load Testing (k6)

### Test Scenarios
All tests: 100 Virtual Users (VUs), 30 seconds duration, 0.5s sleep between iterations

#### 1. Doom-Scroll (95% read, 5% write)
| Metric | Value |
|--------|-------|
| Avg Response Time | 4.49s |
| Median Response Time | 469.2ms |
| P90 | 13.71s |
| P95 | 13.89s |
| Throughput | 18.12 req/s |
| Success Rate | 100% |

#### 2. Live-Ticker (80% write, 20% read)
| Metric | Value |
|--------|-------|
| Avg Response Time | 9.43s |
| Median Response Time | 12.7s |
| P90 | 15.7s |
| P95 | 15.82s |
| Throughput | 8.59 req/s |
| Success Rate | 100% |

#### 3. Shout-Out (100% user registration)
| Metric | Value |
|--------|-------|
| Avg Response Time | 3.14s |
| Median Response Time | 2.2s |
| P90 | 7.86s |
| P95 | 8.3s |
| Throughput | 25.63 req/s |
| Success Rate | 100% |

### Findings
- Write operations (posting) are the primary bottleneck (9.43s avg)
- Response times under load are unacceptable for social media (should be <1s)
- SQLite is insufficient for production load
- 100% reliability demonstrates correct concurrent operation handling

## Task 3: REST API Implementation and Comparison

### Implementation
- Endpoints: GET/POST `/api/posts.php`
- Format: JSON responses
- Authentication: PHP sessions

### Performance Comparison

| Metric | SSR | REST API | Improvement |
|--------|-----|----------|-------------|
| Avg Response Time | 4.49s | 8.23ms | 546x faster |
| Median Response Time | 469.2ms | 7.32ms | 64x faster |
| P95 Response Time | 13.89s | 16.84ms | 825x faster |
| Throughput | 18.12 req/s | 196.15 req/s | 10.8x higher |

### Findings
- REST API provides 2-3 orders of magnitude performance improvement
- Sub-10ms response times enable real-time user experience
- API approach is clearly superior for interactive social media applications

## Test Scripts Location
- SSR tests: `/k6/*.js`
- API tests: `/k6-rest/*.js`
- Test runner: `./run-tests.sh`
