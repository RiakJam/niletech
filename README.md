# nileteck

## Marketplace seller plans

Run `seller-plans-migration.sql` after the existing marketplace migrations. Set `PAYSTACK_SECRET_KEY` in the main `.env`. Set `MANAGER_ADMIN_EMAIL` in `manager/.env` to the email of the account that will review free listings. Configure `https://your-domain/paystack-webhook` as the Paystack webhook URL; localhost cannot receive Paystack webhooks. The three monthly KES prices are defined in `includes/seller-plans.php` and shown at `/seller-plans`.

Free accounts can have five listings across all their storefronts. Submitted free listings enter the `/seller-moderation` queue. Paid accounts can post unlimited listings immediately. Paystack creates recurring monthly plans at checkout and renewals update membership dates through signed webhooks. The first payment is also verified server-side on return from checkout. Paid benefits stop when the recorded paid period ends.

## Web services portfolio

Run `website-projects-migration.sql` to create `website_projects` and seed the Nileteck Marketplace and E-Voting demos. The public `/website-services` page loads only rows marked `published`, ordered by `sort_order`. Future admin publishing can write to this table; `draft` rows stay hidden. Images are served from `images/portfolio/`, and demo/live URLs can be an HTTPS URL or a local path. The special `@evoting` demo URL uses `NILETECK_EVOTING_URL`.

## Manager dashboard

Open `/manager/` for the admin overview, users, product review, and seller plan revenue. Set `MANAGER_ADMIN_EMAIL` in `manager/.env` to the email of an existing Nileteck account; sign in with that account's password to view live data and review pending products. The **Open demo manager** button creates a `manager-demo@example.com` account with an unusable random password and opens a read-only preview with sample figures. The demo never displays live customer records or permits review actions.

Manager settings are documented in `manager/.env.example`. `manager/.env` is ignored by Git and denied by Apache. The old `NILETECK_ADMIN_EMAIL` in the main `.env` remains a fallback for existing installations; `MANAGER_ADMIN_EMAIL` takes priority. Set `MANAGER_DEMO_ENABLED=false` to hide and disable demo manager access.
