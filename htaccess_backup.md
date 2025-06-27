Options -Indexes -MultiViews

<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    # Development CSP for static assets when using webpack dev server
    <If "%{HTTP_HOST} == 'localhost:3003' || %{HTTP_HOST} == '127.0.0.1:3003'">
        <FilesMatch "\.(js|css|svg|png|jpg|jpeg|gif|ico|woff|woff2|ttf|eot|mp4|webm|pdf)$">
            Header set Content-Security-Policy "default-src 'self' data: https://localhost https://localhost:3003; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://localhost https://localhost:3003; style-src 'self' 'unsafe-inline' https://localhost https://localhost:3003; img-src 'self' data: blob: https://localhost https://localhost:3003; font-src 'self' data: https://localhost https://localhost:3003; connect-src 'self' ws://localhost:3003 wss://localhost:3003 ws://127.0.0.1:3003 wss://127.0.0.1:3003 https://localhost https://localhost:3003; frame-src 'self' https://localhost https://localhost:3003; worker-src 'self' blob: https://localhost https://localhost:3003"
        </FilesMatch>
    </If>

</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Webpack DevServer Bypass
    RewriteCond %{REQUEST_URI} ^/(sockjs-node|__webpack_dev_server__|\.hot-update\.) [NC]
    RewriteRule ^ - [L]

    # Well-Known Directory
    RewriteCond %{REQUEST_URI} ^/\.well-known/
    RewriteRule ^ - [L]

    # Redirect to public folder
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]

</IfModule>
