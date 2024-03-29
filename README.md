# Panama

Panama is a project which goal is to simplify the life of freelancers, and relations between them and clients. 

## Install

You need to have Docker on your machine.

Docker install :
```
docker compose build --pull --no-cache

docker compose up -d
```
## Listening Stripe events locally

If you want to test out stripe functionnalities in the project you'll need to create a stripe account.

Then add override the .env STRIPE variables in a .env.local or a .env.ENV_TYPE.local file.

To use the Stripe webhooks without hosting the application, you might need to install the Stripe CLI  [https://stripe.com/docs/stripe-cli](https://stripe.com/docs/stripe-cli)

Once the settings are done, use the following command in your shell

stripe listen --skip-verify --forward-to http://localhost/webhook/stripe

Don't forget to add the webhook key generated by stripe in the .env like said before.

For testing out the subscription flow, you'll need to create a price in the stripe dashboard and create a subscription in the admin pannel with the id of the price as StripeId.

## Account creation

By default the application uses mailcatcher when running with docker compose to catch all the emails sent by the application.

If you want to activate an account you can go to the following url : http://localhost:1080

You'll see all emails that have been sent by the application.

## Create Plan
To create a plan linked to an actual Stripe plan, you can use the following command, however, make sure you have set your STRIPE_SK environment variable first.

```
docker compose php php bin/console createSubPlan :planName :price
```
```:planName``` Replace it with the name of your choice
```:price``` Replace it with the price you want the plan to cost, default value is 9.99€

## Deployement

Our deployement is using GCloud architecture.

We are using Cloud Build for creating our images and deploying them on GCloud Run.

We are using Cloud SQL for our database. (tbd: for cost usage might switch to private VPS host for database)
