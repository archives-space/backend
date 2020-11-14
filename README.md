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