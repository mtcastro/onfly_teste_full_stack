# Stage 1: Build Quasar
FROM node:18-alpine AS build

WORKDIR /app

# Instala Quasar CLI e dependências
COPY package*.json ./
RUN npm install -g @quasar/cli && npm install


# Copia o código do frontend
COPY . .

# Build com publicPath em /front/
RUN quasar build -m spa

# Stage 2: Serve com Nginx
FROM nginx:alpine

# Remove configuração default
RUN rm /etc/nginx/conf.d/default.conf

# Copia build para pasta do Nginx
COPY --from=build /app/dist/spa /usr/share/nginx/html/front

# Cria configuração mínima do Nginx
RUN echo 'server { \
    listen 80; \
    server_name localhost; \
    root /usr/share/nginx/html; \
    index index.html; \
    location ${SUBFOLDER} { \
         try_files \$uri \$uri/index.html;\ 
    } \
}' > /etc/nginx/conf.d/default.conf

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
