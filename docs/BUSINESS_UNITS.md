# Business Units

Abnaa Abu Qasaa Trading is the umbrella brand. It owns and manages several business units through one shared platform.

## Current Business Units

- Abnaa Abu Qasaa Oils & Lubricants
- Abnaa Abu Qasaa Import & Export
- Ghosoun Dates
- Abnaa Abu Qasaa Real Estate

## Future Business Units

Future business units should be created from the dashboard by selecting an activity template, enabling modules, and filling business settings. The platform must not create a new table or app for every new business.

Super Admins can select existing templates/modules only. New module behavior is added by developers in code, then exposed through the activity module catalog.

## Activity Templates

- Product Store
- Wholesale Store
- Services/RFQ
- Real Estate
- Content Only
- Hybrid

## Seeding

Run the foundation seed from `backend/`:

```bash
php artisan migrate:fresh --seed
```

This seeds the six templates, the activity module catalog, global feature flags, and the four current business units with their default modules and settings.
