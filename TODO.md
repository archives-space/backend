# API docs api.wikiarchives.space

## Structure BDD

FileObject: { bucketId: string, objectId: string }

## users
```
OK - _id: ObjectId,

OK - username: string,
OK - password: string,
OK - email: string,
OK - role: contributor | moderator | administrator,

OK - isLocked: bool,
OK - isVerified: bool,
OK - isDeleted: bool,
OK - score: int,

OK - lastLoginAt: DATE_TIME,
OK - createdAt: DATE_TIME,
OK - updatedAt: DATE_TIME,
OK - deletedAt: DATE_TIME,

avatar: FileObject,
OK - publicName: string,
OK - location: string,
OK - biography: string
```

## changes

```
_id: ObjectId,

type: 'catalog' | 'picture',
refId: ObjectId,
fields: {
    key: value
}
validationState: 'pending'|'rejected'|'accepted',
validationComment: string,

createdAt: datetime,
validatedAt: datetime,

userId: ObjectId
```

## catalogs

```
OK - _id: ObjectId

OK - name: string
OK - description: string
OK - parentId: ObjectId
thumbnailId: ObjectId

OK - createdAt: DATE_TIME,
OK - updatedAt: DATE_TIME
```

## pictures

```
OK - _id: ObjectId,

OK - catalogId: ObjectId,

# métas entré par l'user
OK - name: string, # ou id_agency
OK - description: string,
OK - source: string,
placeId: ObjectId,
OK - position: { lat: float, lng: float },
OK - takenAt: datetime,
OK - exif: {
    model: string,
    manufacturer: string,
    aperture: string,
    iso: int,
    exposure: string,
    focalLength: float,
    flash: bool
    ...
}

OK - license: {
    name: Can be one of: [
      null,
      'CC BY',
      'CC BY-SA',
      'CC BY-ND',
      'CC BY-NC',
      'CC BY-NC-SA',
      'CC BY-NC-ND',
      'CC BY-SA 3.0 IGO',
      'All rights reserved'
      'Public Domain' 
    ]
    isModified: boolean (default: false)
}

# autogénéré
OK - typeMime: string,
OK - hash: string, # sha256
OK - originalFileName: string

OK - resolutions: [
    {
        file: FileObject,
        width: int,
        height: int,
        size: int,
        label: 'xs'|'m'|'xl'|'original'
    }
]

OK - createdAt: DATE_TIME,
OK - updatedAt: DATE_TIME
```

### places

```

_id: string

name: string,
description: string,
pictureId: ObjectID,
wikidata???

position: { lat: float, lng: float },

createdAt: DATE_TIME,
updatedAt: DATE_TIME

```

## Identification

* POST /login
* POST /register
* POST /password-reset

* GET /users
* GET /users/:id
* POST /users/:id/avatar
* POST /users/:id/password
* POST /users
```
{
}
```
* DELETE /users/:id

## Catalog photos

* GET /catalogs
* GET /catalogs/rando
* GET /catalogs/:id
* GET /catalogs/:id/photos
* 
