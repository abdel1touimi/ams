FROM node:22-alpine

# Install pnpm
RUN corepack enable && corepack prepare pnpm@latest --activate

# Create app directory
WORKDIR /app

# Copy package files
COPY package.json pnpm-lock.yaml ./

# Set development environment by default
ARG NODE_ENV=development
ENV NODE_ENV=$NODE_ENV

# Install dependencies
RUN pnpm install --frozen-lockfile

# Copy the rest of the application
COPY . .

# Add packageManager field to package.json
RUN node -e "const pkg=require('./package.json'); pkg.packageManager='pnpm@9.12.2'; require('fs').writeFileSync('./package.json', JSON.stringify(pkg, null, 2))"

# Type check and build the application if in production
RUN if [ "$NODE_ENV" = "production" ]; then \
        echo "Running type check..." && \
        pnpm tsc --noEmit && \
        echo "Building production bundle..." && \
        pnpm build; \
    fi

EXPOSE 3000

# Start the application based on NODE_ENV
CMD if [ "$NODE_ENV" = "production" ]; then \
        pnpm start; \
    else \
        pnpm dev; \
    fi
