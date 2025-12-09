# Task 1: Client side performance measurement with Google Lighthouse

Scores (loading index, range values of logged in and logged out):
- Performance: 99-100
    - FCS (First Contentful Paint): 0.4s - 0.6s
    - LCP (Largest Contentful Paint): 0.5s - 0.8s
    - CLS (Cumulative Layout Shift): 0.0001
    - TTI (Time to Interactive/ Total Blocking Time): 0 ms
    - Possible Savings:
        - Font display -- Est savings of 330-1270 ms: Is comparably expensive, but is cached and therefore negligible for revisting users. If Performance is really critical, a more lightweight or typically wide-distributed font can be used. Alternatively, the login page can be made default as first seen page for new users, using exclusively a font that is already distributed on various platforms, while loading and caching the hosted fonts in the background, after the page finished loading.
        - Render blocking requests -- Est savings of 150-270 ms: reduce weight of user.js and css (possibly again use minimal initial page to load heavy assets in the background after page rendering finished and then cache them); but negligible
        - Reduce unused CSS -- Est savings of 64 KiB: Difficult in organized manner, as we use a prepared external style sheet (pico css) -> could prune down to only used elements, but is overkill
        - Currently the limit of 100 posts shown is impractical -> use dynamic loading of posts when scrolling down or show only a (lesser) limited number until more are manually requested
- Accessability: 100
- Best Practices: 100
- SEO: 90
    - no meta description --> not a focus of the webpage

# Task 2: Load testing with k6
## Test Design Overview

Three load testing scenarios:

1. **doom-scroll** - Read-heavy pattern (95% browse, 5% post)
2. **live-ticker** - Write-heavy pattern (80% post, 20% browse)  
3. **shout-out** - Registration stress test (100% new user creation)

All tests ran with 100 Virtual Users (VUs) for 30 seconds on the remote cloud server without database resets between tests (which potentially impacts performance due to increasing database size). All VUs sleep for 0.5s between iterations.

## Test Logs
Tests on the hosted remote server on cloud bw (executed in reported order without db reset):
### doom-scrolling
```
     execution: local
        script: /scripts/doom-scroll.js
        output: -

     scenarios: (100.00%) 1 scenario, 100 max VUs, 1m0s max duration (incl. graceful stop):
              * default: 100 looping VUs for 30s (gracefulStop: 30s)



  █ TOTAL RESULTS

    checks_total.......: 197     4.429104/s
    checks_succeeded...: 100.00% 197 out of 197
    checks_failed......: 0.00%   0 out of 197

    ✓ feed loaded
    ✓ roar created

    HTTP
    http_req_duration..............: avg=4.49s  min=39.77ms med=469.2ms max=14.27s p(90)=13.71s p(95)=13.89s
      { expected_response:true }...: avg=4.49s  min=39.77ms med=469.2ms max=14.27s p(90)=13.71s p(95)=13.89s
    http_req_failed................: 0.00%  0 out of 806
    http_reqs......................: 806    18.121104/s

    EXECUTION
    iteration_duration.............: avg=18.98s min=1.87s   med=14.33s  max=44.14s p(90)=33.28s p(95)=34.89s
    iterations.....................: 197    4.429104/s
    vus............................: 6      min=6        max=100
    vus_max........................: 100    min=100      max=100

    NETWORK
    data_received..................: 5.1 MB 115 kB/s
    data_sent......................: 383 kB 8.6 kB/s




running (0m44.5s), 000/100 VUs, 197 complete and 0 interrupted iterations
default ✓ [======================================] 100 VUs  30s
```

### live-ticker
```
     execution: local
        script: /scripts/live-ticker.js
        output: -

     scenarios: (100.00%) 1 scenario, 100 max VUs, 1m0s max duration (incl. graceful stop):
              * default: 100 looping VUs for 30s (gracefulStop: 30s)



  █ TOTAL RESULTS

    checks_total.......: 122     2.500257/s
    checks_succeeded...: 100.00% 122 out of 122
    checks_failed......: 0.00%   0 out of 122

    ✓ feed loaded
    ✓ roar created

    HTTP
    http_req_duration..............: avg=9.43s  min=53.84ms med=12.7s  max=16.25s p(90)=15.7s  p(95)=15.82s
      { expected_response:true }...: avg=9.43s  min=53.84ms med=12.7s  max=16.25s p(90)=15.7s  p(95)=15.82s
    http_req_failed................: 0.00%  0 out of 419
    http_reqs......................: 419    8.586948/s

    EXECUTION
    iteration_duration.............: avg=32.99s min=13.61s  med=35.36s max=46.69s p(90)=44.32s p(95)=45.89s
    iterations.....................: 122    2.500257/s
    vus............................: 9      min=9        max=100
    vus_max........................: 100    min=100      max=100

    NETWORK
    data_received..................: 4.9 MB 101 kB/s
    data_sent......................: 292 kB 6.0 kB/s




running (0m48.8s), 000/100 VUs, 122 complete and 0 interrupted iterations
default ✓ [======================================] 100 VUs  30s
```


### shout-out
```
     execution: local
        script: /scripts/shout-out.js
        output: -

     scenarios: (100.00%) 1 scenario, 100 max VUs, 1m0s max duration (incl. graceful stop):
              * default: 100 looping VUs for 30s (gracefulStop: 30s)



  █ TOTAL RESULTS

    HTTP
    http_req_duration..............: avg=3.14s  min=37.62ms med=2.2s   max=9.49s  p(90)=7.86s  p(95)=8.3s
      { expected_response:true }...: avg=3.14s  min=37.62ms med=2.2s   max=9.49s  p(90)=7.86s  p(95)=8.3s
    http_req_failed................: 0.00%  0 out of 1144
    http_reqs......................: 1144   25.625191/s

    EXECUTION
    iteration_duration.............: avg=13.11s min=1.34s   med=15.07s max=18.14s p(90)=16.05s p(95)=16.4s
    iterations.....................: 286    6.406298/s
    vus............................: 7      min=7         max=100
    vus_max........................: 100    min=100       max=100

    NETWORK
    data_received..................: 3.7 MB 83 kB/s
    data_sent......................: 505 kB 11 kB/s




running (0m44.6s), 000/100 VUs, 286 complete and 0 interrupted iterations
default ✓ [======================================] 100 VUs  30s
```

## Analysis

### 1. Doom-Scroll (Browse-Heavy Workload)
**Configuration:** 95% read operations, 5% write operations, 0.5s sleep time

**Results:**
- **Throughput:** 197 iterations (4.43/s), 18.12 HTTP req/s
- **Reliability:** 100% success rate (197/197 checks passed)
- **Response Times (HTTP):** avg=4.49s, med=469.2ms, p90=13.71s, p95=13.89s
- **Network:** 5.1 MB received, 383 KB sent

**Assessment:** 
- The median is significantly lower than the average (469.2ms vs 4.49s), which indicates major outliers (as seen in the upper percentiles); it is unclear whether these outliers are primarily read or write operations.
- No errors occurred (100% success rate)

### 2. Live-Ticker (Write-Heavy Workload)
**Configuration:** 80% write operations, 20% read operations, 0.5s sleep time

**Results:**
- **Throughput:** 122 iterations (2.50/s), 8.59 HTTP req/s
- **Reliability:** 100% success rate (122/122 checks passed)
- **Response Times (HTTP):** avg=9.43s, med=12.7s, p90=15.7s, p95=15.82s
- **Network:** 4.9 MB received, 292 KB sent

**Assessment:**
- The average response time (9.43s) and also the median (12.7s) are very high, indicating that write operations (posting roars) are significantly more demanding on the server than read operations (when comparing to the results of the doom-scroll scenario).
- No errors occurred (100% success rate)
- The received data is only very slightly lower than in the doom-scroll scenario, despite the lower number of iterations (122 vs 197). This potentially is due to the fact that posting a roar increases the size of read operations, since every user loads the most recent 100 messages of the feed every time on a read (while there were less than 100 posts when running doom-scroll). Also write may involve an inherent read operation to load the feed after posting.

### 3. Shout-Out (Registration Stress Test)
**Configuration:** 100% new user registrations, 0.5s sleep time

**Results:**
- **Throughput:** 286 iterations (6.41/s), 25.63 HTTP req/s
- **Reliability:** 100% success rate (0% HTTP failures)
- **Response Times (HTTP):** avg=3.14s, med=2.2s, p90=7.86s, p95=8.3s
- **Network:** 3.7 MB received, 505 KB sent

**Assessment:** 
- The average (3.14s) and median (2.2s) response times are moderate and rather acceptable, especially for theoretically a one time occurence per user of registering an account. The upper percentiles (p90=7.86s, p95=8.3s) indicate some outliers but not as extreme as in the other scenarios.
- Significantly more iterations were handled as compared to the live-ticker scenario, which potentially is due to the fact, that registering a user doesn't load the feed, as the user is forwarded to the profile page first.
- No errors occurred (100% success rate)

Successfully handling 286 concurrent registrations without failures demonstrates robust session management and database write handling.

### Summary
**Performance Bottlenecks:**
- High response times: (3-10s average) indicate major optimization opportunities
- Especially bad performance regarding writes/posting roars (see live-ticker results: median 12.7s, avg 9.43s)
- User registration delays are okayish (median 2.2s, avg 3.14s) but could be improved
- Reading under load is acceptable (median 469.2ms, avg 4.49s) but with significant outliers which present inacceptable delays for a social media app

**Possible Improvements:**
- Choose more efficient database than SQLite
- Implement query result caching for more efficient feed retrieval (i.e., preparing a feed cache ever few minutes or seconds and provide the prepared feed (difficult if feeds are individual))
- Add horizontal scaling strategies (load balancers, read/write replicas)
- buffer write requests prior database insertion and return success early (writes/posting roars currently are the most demanding requests as suggested by the live-ticker scenario)
- reduce reloading of page on write operations (posting roars) by dynamically adding the post via JavaScript instead of reloading the entire feed
- redue the number of initially loaded feeds when loading the page (currently 100), and dynamically expand it when a user scrolls down
- categorize the posts so that users only load feeds they are potentially interested in


### Conclusion

We observe a 100% success rate across all scenarios, indicating the application handles concurrent operations correctly. However, the high response times, especially for roar-post operations, highlight the critical limitations of the application under load. Average response times of up 10s are inacceptable for a social media platform and should rather be targetted to be under 1s for all typical operations. Horizontal scaling and optimization strategies may reduce this time significantly.


# Task 3: Comparrison with Restful API 

In comparison to our previous implementation using a RESTful API, the current implementation using standard PHP sessions and server-side rendering shows some differences in performance and scalability.

