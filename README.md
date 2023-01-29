
# Install

  

```

cp ./api/.env.example ./api/.env

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

docker compose exec php php bin/console m:mig

docker compose exec php php bin/console d:m:m

```
# Listening Stripe events locally

To use the Stripe webhooks without hosting the application, you might need to install the Stripe CLI  [https://stripe.com/docs/stripe-cli](https://stripe.com/docs/stripe-cli)

Once the settings are done, use the following command in your shell

stripe listen --skip-verify --forward-to https://localhost/webhook/stripe

## Deployement

Our deployement is using GCloud architecture.

We are using Cloud Build for creating our images and deploying them on GCloud Run.

We are using Cloud SQL for our database. (tbd: for cost usage might switch to private VPS host for database)

Docker images can be found [here]([eu.gcr.io/challenge-s1/challenge_s1_g6_api])

There is an image for the API-Platform api named challenge_s1_g6_api \
And one for the fake api used to verify freelancers info named challenge-s1-g6-kyc-api