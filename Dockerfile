# Multi-stage Dockerfile following docker-expert skill
# Stage 1: Dependencies
FROM node:22-slim AS deps
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --omit=dev

# Stage 2: Production
FROM node:22-slim AS production
LABEL maintainer="kangguircm"
LABEL org.opencontainers.image.source="https://github.com/Lungouhen/Kangguircm"

# Security: non-root user
RUN groupadd -r app && useradd -r -g app -d /app app

WORKDIR /app

# Copy dependencies and app
COPY --from=deps /app/node_modules ./node_modules
COPY --chown=app:app . .

# Security: restrict permissions
RUN chmod -R 755 /app && \
    chown -R app:app /app/storage

# Environment
ENV NODE_ENV=production
ENV PORT=3000

# Health check
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD node -e "fetch('http://localhost:3000/login').then(r=>r.ok?process.exit(0):process.exit(1)).catch(()=>process.exit(1))"

# Expose only needed port
EXPOSE 3000

# Run as non-root
USER app

CMD ["node", "server.js"]
