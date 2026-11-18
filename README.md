<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Transactional SMS notifications

The application can send opt-in adoption and post-adoption SMS updates through Twilio. SMS is queued and will be recorded as skipped until a provider and valid credentials are configured. No SMS account is provisioned by this repository.

Configure these server-side environment variables (never expose them to Vue):

```dotenv
SMS_PROVIDER=twilio
SMS_TIMEOUT=10
SMS_MONITORING_REMINDER_DAYS=1
SMS_MONITORING_REMINDER_TIME=09:00
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_MESSAGING_SERVICE_SID=
TWILIO_FROM=
APP_URL=https://your-public-domain
QUEUE_CONNECTION=database
```

Set either `TWILIO_MESSAGING_SERVICE_SID` or `TWILIO_FROM`. The public HTTPS `APP_URL` must be reachable by Twilio for delivery-status callbacks. Users must opt in under Account Settings; the system accepts Philippine mobile numbers in `09XXXXXXXXX` or `+639XXXXXXXXX` form and sends them to the provider in E.164 format. Monitoring reminders run on the configured day offset at the configured application-local time.

After deploying the incremental migrations, run a queue worker as a separate Railway service:

```sh
php artisan queue:work database --sleep=3 --tries=3 --timeout=30
```

Run `php artisan migrate --force` once as a deployment release task. Run Laravel's scheduler continuously in a separate Railway service:

```sh
php artisan schedule:work
```

Twilio pricing depends on destination carrier and message segments; verify current rates and sender requirements before enabling live credentials. Automated tests use mocked provider responses and do not send real SMS. The donation module currently has no administrator verification/rejection action, so donation status SMS is not triggered until that workflow is added.

See the [Twilio Messages API](https://www.twilio.com/docs/messaging/api/message-resource), [delivery status callbacks](https://www.twilio.com/docs/messaging/guides/track-outbound-message-status), and [Philippines SMS pricing](https://www.twilio.com/en-us/sms/pricing/ph) for current provider requirements and rates.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
