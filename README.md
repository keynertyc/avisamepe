# avisamepe
Script to track products of Retailers like Saga Falabella, etc. Run with parameters min-max price and you'll be notified when the product will be between the amounts. Don't forget offers!

Run:
```sh
$ composer install
$ cp .env.example .env
```
Edit file .env with your SMTP Provider

Edit your crontab:
```sh
$ EDITOR=nano crontab -e
```

Add new job (i.e every 5 minutes), argvs: < sku > < min > < max > < email >
```sh
*/5 * * * * php /home/keyner/avisamepe/index.php 48673233 600 800 keyner.peru@gmail.com
```