# Install 

```
  docker compose build --pull --no-cache
  docker compose up -d
```

wait for dependencies to install and then 

```
  docker compose exec php sh -c '
    set -e
    apk add openssl
    php bin/console lexik:jwt:generate-keypair
    setfacl -R -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
    setfacl -dR -m u:www-data:rX -m u:"$(whoami)":rwX config/jwt
  '
  docker compose exec php php bin/console d:m:m
```