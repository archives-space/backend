# Installation

```
composer install
# give acces to write to www-data in var directory
cp .env .env.local
```

Edit the .env.local file set your mongodb credentials

```
MONGODB_URL=mongodb://mongo_wikiarchives:27017
MONGODB_USER=root
MONGODB_PASS=0000
MONGODB_DB=wikiarchives
```

Generate JWT SSH keys.env.

For the pass phrase set the value in `JWT_PASSPHRASE` in .env.local file

```
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

```
php bin/console doctrine:mongodb:schema:update
```

## Postman URL
https://identity.getpostman.com/handover/multifactor?user=3913400&handover_token=704ff4d3-7404-4a20-b5d7-0a03a064e652

## API Errors structure

- a error has:
    - a unique 'key' field which is a string identifier
    - a optional 'propertyPath' field to quote the origin of the error in the user's input json
    - a 'message' field which describe the error in a human way

## Local SMTP development server

To test the good reception of emails you need to install on your machine an SMTP server to catch mails, here are some examples :

- [MailHog](https://github.com/mailhog/MailHog)
- [MailCatcher](https://mailcatcher.me/)