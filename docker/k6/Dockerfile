FROM grafana/k6:latest

WORKDIR /scripts

# Copy k6 scripts
COPY k6/*.js /scripts/

# Set entrypoint to k6
ENTRYPOINT ["k6"]
