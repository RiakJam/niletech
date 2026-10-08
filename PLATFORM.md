# Nileteck setup

The public site offers managed website development and maintenance, self-service Marketplace and Email Marketing, and a link to the separate E-voting application. Only Marketplace and Email Marketing appear in the user dashboard. E-voting remains a separate application with its own accounts. Configure `NILETECK_EVOTING_URL` for its production HTTPS address; local development links to `/e-voting/`. The dashboard creates one storefront automatically for an account that does not yet have one; users do not create or choose a subdomain. Visitors can follow a store through its email and consent dialog. Campaigns can be drafted in the dashboard; outbound sending is not enabled until a delivery service and unsubscribe flow are configured.

Import `database.sql` into MySQL/MariaDB. On an existing database, apply the three new `CREATE TABLE` statements for `business_domains`, `email_contacts`, and `email_campaigns`. Existing blog, event, and forum data is retained in the database but has no public navigation or creation flow.

New storefronts use `/p/marketplace/{slug}` on the main site and do not need wildcard DNS or certificates. Existing `business_domains` mappings can continue to serve earlier subdomains when their DNS, virtual host, and TLS configuration remains active.

Set database credentials in `.env`; `.env.example` lists the expected keys. The web server must deny access to `.env` and `database.sql`.

## Marketplace presentation

The public marketplace at `/p/marketplace/`, business storefronts, and product pages use a standalone layout without the main website header or footer. The category sidebar stays visible during desktop scrolling and becomes a horizontal category strip on small screens. Sellers choose a category and may upload a JPG, PNG, or WebP product image up to 5 MB. Apply `marketplace-migration.sql` to existing databases before posting products. The web server needs write access to `uploads/products/`; only image files belong in that directory. Product images and category filters appear on the marketplace, storefront, and product pages.

The sample catalog is seeded with `php scripts/seed-marketplace-demo.php`. It creates a dedicated demo store and six published sample listings. The generated photo atlas is stored at `images/demo/product-atlas.png`. Demo listings are labeled and are not for sale. Category, search, and KSh minimum/maximum amount filters apply to published products.

## Marketplace engagement and orders

Apply `marketplace-engagement-migration.sql` to an existing database. New installations receive these tables from `database.sql`.

For existing installations, apply `marketplace-location-migration.sql` before deploying location-aware accounts and listings. New registrations collect an African country, city or region, and local area. Existing members can add these under **My location** in the dashboard; existing listings keep an unspecified location until edited. New and edited listing prices use the currency of the member's saved account country. A price filter compares amounts only in the selected country's currency. Existing prices retain their recorded currency when an account country changes; no exchange conversion occurs. Signed-in members see marketplace listings only from their account country, with their city shown first. The marketplace location picker narrows results to a region or area within that country. Guest country selection is remembered in a browser cookie. Region names come from Unicode CLDR (license notice in `includes/CLDR-NOTICE.txt`); area names and listing counts come from published Nileteck listings.

Store owners can set WhatsApp and call numbers in **Dashboard → Edit store**. Public store and product pages show WhatsApp, Call, and Message order actions. Message requests appear in **Dashboard → Messages**, with an unread count and a reply by email action. Demo listings do not accept orders.

**Dashboard → Analytics** reports store visits (storefront and product page loads), estimated unique visitors (browser cookies), product impressions (a card visible at least halfway, once per browser per day), product card clicks, and product detail views. These are aggregate activity counts, so unique visitors are browsers rather than verified individuals. Browser settings and automated traffic can affect the figures.

## Demo storefront and product editing

The public sample catalog belongs to the same `nileteck-demo` storefront shown by the demo dashboard account. Existing installations with the earlier empty `store-<demo user id>` page can run `php scripts/consolidate-demo-store.php` once; the script transfers saved store contact settings and visitor counts, then removes that empty duplicate. The old `store-2` URL redirects to the catalog store in this installation.

Owners manage listings in **Dashboard → Products**. Each Edit button opens a form for the name, details, price, category, image, and publication status. Editing a name keeps the existing product URL. The sample seeder now leaves edited products unchanged on later runs.

## Google sign-in

Apply `google-auth-migration.sql` to existing databases. Create a Google OAuth web application client and set `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, and `GOOGLE_REDIRECT_URI` in `.env`. Register that exact redirect URI in Google Cloud; for the local XAMPP setup it is `http://localhost/nileteck/google-auth`. The Google button starts an authorization-code flow with state and PKCE. New Google members are sent to **My location** to set their country, city, and area before posting products. An existing password account with the same email is not automatically linked to a Google identity. Until credentials are configured, the Google button shows a setup message on the sign-in or sign-up page.
