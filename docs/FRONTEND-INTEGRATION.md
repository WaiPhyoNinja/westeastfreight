# Vue West + Invoice App

The Vue website from `/Users/waiphyo/vue-projects/vue-west` now lives in
`resources/frontend`. Laravel serves the website at `/` and Filament at `/admin`.
The original Vue checkout is not used at runtime.

## Local development

```sh
composer install
npm ci
php artisan migrate
php artisan storage:link
php artisan site:normalize-images
npm run build
```

Open `http://invoice-app.test` with Herd, or run `php artisan serve`.
For hot reload, run `npm run dev` and open the Laravel URL (not Vite's port).
No `VITE_DIRECTUS_URL`, Directus SDK, or separate frontend server is required.
Use the same commands for deployment, with the usual production Laravel settings.
Do not run content seeders against existing content just to enable the frontend.

## Data and access

- Published Pages and Service Pages supply page blocks, translations, and SEO.
- Site Settings supply navigation, logos, contact details, and footer text.
- Images use Laravel's public storage disk and `/storage` link.
  The normalize command copies imported extensionless files to PNG/JPG paths and
  updates saved page references. It is safe to rerun and keeps the original files.
- Active Quotation Countries populate the sender and receiver country selectors.
- Customer registration, login, profile changes, and password changes use Laravel
  session cookies and CSRF protection. Customers receive no admin role.
- Quote requests are stored separately from issued invoices. Customers can list
  their own requests and edit them only while pending. Admins review requests at
  `/admin/quote-requests`. Submitting a request emails a receipt to the signed-in
  customer's account address. Staff can use **Send quotation email** on the edit
  page to write the actual quotation and optionally attach a private PDF. Successful
  sends set the request to `quoted` and record the message and send time. Changing
  the status selector alone does not send an email. Requests are not converted into invoices.
- Contact forms and newsletter signup requests are stored under
  `/admin/website-submissions`. Contact forms email a receipt to the visitor and
  create a database notification in the Filament bell for staff with
  `submissions.manage`. The notification opens the full message. The inbox has
  Contact Us and Newsletter tabs; staff can reply by email or call the submitted
  phone number. The contact response and inbox show whether the receipt was
  accepted by the mail transport, failed, or was not attempted because email
  delivery is not configured. After configuring email, staff can use
  **Resend confirmation** on an existing contact request. Newsletter signup
  does not send a campaign.
- Outgoing email, including welcome and password reset messages, uses Laravel's
  configured mail transport. The default `MAIL_MAILER=log` only writes emails to
  the local log. To deliver email, set `MAIL_MAILER=smtp`, your provider's
  `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, and a verified
  `MAIL_FROM_ADDRESS` in the server environment, then run `php artisan config:clear`.
  The admin reply actions refuse to mark messages as sent while using `log` or
  `array` mailers. Site Settings → General shows the current delivery status.
- Quote and contact emails run on the `database` connection's `mail` queue.
  `composer dev` listens to both `mail` and `default`. When serving through Herd
  without `composer dev`, run a worker separately:
  `php artisan queue:work database --queue=mail,default --tries=3 --timeout=60`.
  Keep that worker running under a process manager on a server. A worker listening
  only to `default` leaves email jobs queued without attempting delivery.

The original Vue component data shapes are provided by the explicit read-only
`/api/frontend/items/{collection}` adapter. The existing `/api/v1` content API
remains available. Customer and submission endpoints use the `web` middleware
so requests require same-origin sessions and CSRF tokens.

The original website's optional external fonts, map embeds, template artwork,
and PDF scripts still require internet access. There is no migration of accounts,
quotes, or other private records from the previous Directus service.

## Verification

```sh
npm run build
php artisan test
```

Feature tests cover published content, localized nested blocks, route separation,
registration, login/logout, profile/password changes, reset tokens, request
validation, quote ownership, edit restrictions, and public form persistence.
