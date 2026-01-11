# Assignment 5 Performance Report
## Previous Results from assignment 4 Load Testing without pagination and redis caching (for read and write heavy scenarios)
### 1. Doom-Scroll (95% read, 5% write)

| Metric | Value |
|--------|-------|
| Avg Response Time | 25.94ms |
| Median Response Time | 19.44ms |
| P90 | 44.7ms |
| P95 | 61.2ms |
| Throughput | 185.39 req/s |
| Success Rate | 100% |

### 2. Live-Ticker (80% write, 20% read)

| Metric | Value |
|--------|-------|
| Avg Response Time | 61.3ms |
| Median Response Time | 34.08ms |
| P90 | 140.2ms |
| P95 | 167.76ms |
| Throughput | 173.65 req/s |
| Success Rate | 100% |


## New Results from assignment 5 Load Testing with new pagination and redis caching

### 1. Doom-Scroll (95% read, 5% write)

| Metric | Value |
|--------|-------|
| Avg Response Time | 24.51ms |
| Median Response Time | 18.26ms |
| P90 | 39.74ms |
| P95 | 57.89ms |
| Throughput | 185.36 req/s |
| Success Rate | 100% |

### 2. Live-Ticker (80% write, 20% read)

| Metric | Value |
|--------|-------|
| Avg Response Time | 58.92ms |
| Median Response Time | 35.38ms |
| P90 | 128.89ms |
| P95 | 158.55ms |
| Throughput | 174.34 req/s |
| Success Rate | 100% |


## Analysis
Only very slight improvements were observed in average response times and percentiles for both Doom-Scroll and Live-Ticker scenarios after implementing pagination and Redis caching. While the pagination should have reduced the transfered amount of data greatly, as now only 10 posts per request are fetched instead of 100, the perceived performance gains were minimal in this testing setup. Possibly, the added overhead of Redis caching and pagination logic outweighed the benefits in these specific load testing scenarios end their extents.