
# Install

You need to have Docker on your machine.

Docker install :
```
docker compose build --pull --no-cache

docker compose up -d
```
# Listening Stripe events locally

If you want to test out stripe functionnalities in the project you'll need to create a stripe account.

Then add override the .env STRIPE variables in a .env.local or a .env.ENV_TYPE.local file.

To use the Stripe webhooks without hosting the application, you might need to install the Stripe CLI  [https://stripe.com/docs/stripe-cli](https://stripe.com/docs/stripe-cli)

Once the settings are done, use the following command in your shell

stripe listen --skip-verify --forward-to http://localhost/webhook/stripe

Don't forget to add the webhook key generated by stripe in the .env like said before.

For testing out the subscription flow, you'll need to create a price in the stripe dashboard and create a subscription in the admin pannel with the id of the price as StripeId.

# Deployement

Our deployement is using GCloud architecture.

We are using Cloud Build for creating our images and deploying them on GCloud Run.

We are using Cloud SQL for our database. (tbd: for cost usage might switch to private VPS host for database)

Docker images can be found [here]([eu.gcr.io/challenge-s1/challenge_s1_g6_api])

There is an image for the API-Platform api named challenge_s1_g6_api \
And one for the fake api used to verify freelancers info named challenge-s1-g6-kyc-api