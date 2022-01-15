# Koomo Code Challenge

## About code

This project aims to cover all the requirements of the Koomo Code Challenge.
Is based on Laravel and the [Laravel Json Api](https://laraveljsonapi.io/) package, 
to support as much as possible the [Json Api](https://jsonapi.org/) specification.

## Project repository

The Git Project is on https://github.com/albertosgz-kooomo-coding/kooomo-code-challenge.
Run the next command to fetch it.

```shell
$ git clone git@github.com:albertosgz-kooomo-coding/kooomo-code-challenge.git
```

## Install

Once clone it, enter into the folder and install all dependencies:
```shell
$ cd kooomo-code-challenge/
$ composer install
```

Get up and run all containers:
```shell
$ ./vendor/bin/sail up
```
Since the Docker output takes the terminal. Another terminal window is needed.

### Database

A schema with name _kooomo_code_challenge_ is necessary in the MySql instance by default.

If you need to connect into it (the Mysql container), the connection information is:
- Host: mysql
- Port: 3306
- Username: sail 
- Username Password: password 
- Root Password: password

The previous data is available on `.env` file.

Once the schema is created, open another terminal, and run next command to migrate the database:
```shell
$ ./vendor/bin/sail artisan migrate
```

## Tests
Run tests:
```shell
$ ./vendor/bin/sail test
```
Remember to run the tests before seed the DB. Otherwise, all data in DB will be lost.

## Seed Database

Populate the database, with fake data:
```shell
$ ./vendor/bin/sail artisan db:seed
```

## Making endpoints query

### Curl
Make the first query with next command to check all is ok:
```shell
$ curl "http://localhost/api/v1/posts?page[number]=1&page[size]=5" -H "Content-Type: application/vnd.api+json"
```
You should see something like this:
```json
{
  "meta": {
    "page": {
      "currentPage": 1,
      "from": 1,
      "lastPage": 11,
      "perPage": 1,
      "to": 1,
      "total": 11
    }
  },
  "jsonapi": {
    "version": "1.0"
  },
  "links": {
    "first": "http://localhost/api/v1/posts?page%5Bnumber%5D=1&page%5Bsize%5D=1",
    "last": "http://localhost/api/v1/posts?page%5Bnumber%5D=11&page%5Bsize%5D=1",
    "next": "http://localhost/api/v1/posts?page%5Bnumber%5D=2&page%5Bsize%5D=1"
  },
  "data": [
    {
      "type": "posts",
      "id": "107",
      "attributes": {
        "createdAt": "2022-01-14T16:32:12.000000Z",
        "updatedAt": "2022-01-14T16:32:12.000000Z",
        "content": "Qui aut voluptatem alias voluptatem quibusdam sunt. Magni laudantium maiores eos voluptatum quas quae. Officiis debitis et architecto dolor odit praesentium.",
        "slug": "tempora-consequatur-quod-sapiente-temporibus-sit-impedit",
        "title": "Aut eaque minus cupiditate esse ut dolor culpa.",
        "is_published": true
      },
      "relationships": {
        "author": {
          "links": {
            "related": "http://localhost/api/v1/posts/107/author",
            "self": "http://localhost/api/v1/posts/107/relationships/author"
          }
        },
        "comments": {
          "links": {
            "related": "http://localhost/api/v1/posts/107/comments",
            "self": "http://localhost/api/v1/posts/107/relationships/comments"
          }
        }
      },
      "links": {
        "self": "http://localhost/api/v1/posts/107"
      }
    }
  ]
}
```
### Postman
A collection of example requests is available to import into a postman program.
The file `Kooomo.postman_collection.json` can be found in `/docs` folder.
Remember to setup the Bearer Token in the authorization tab in order to make use of
protected endpoints. You can find a Bearer token when running the next command `kooomo:generateUsersTokens`.

## Authentication 
In order to make use of protected endpoints, a bearer token is needed.
To generate new tokens and see its value, run:
```shell
$ vendor/bin/sail artisan kooomo:generateUsersTokens
```

Try to create Post with one of the token listed:
```shell
curl -X POST http://localhost/api/v1/posts 
  -H 'Content-Type: application/vnd.api+json' 
  -H 'Authorization: Bearer 34|rJAOmrsApUhQlg1lBnfY7K8Fv0vdf3Vei07XP9Gr' 
  -d '{"data":{"type":"posts","attributes":{"content":"My first content","slug":"hello-world","title":"Hello World","is_published":true}}}'
```

Should see something like this:
```json
{
  "jsonapi": {
    "version": "1.0"
  },
  "links": {
    "self": "http://localhost/api/v1/posts/206"
  },
  "data": {
    "type": "posts",
    "id": "206",
    "attributes": {
      "createdAt": "2022-01-14T17:20:46.000000Z",
      "updatedAt": "2022-01-14T17:20:46.000000Z",
      "content": "My first content",
      "slug": "hello-world",
      "title": "Hello World",
      "is_published": true
    },
    "relationships": {
      "author": {
        "links": {
          "related": "http://localhost/api/v1/posts/206/author",
          "self": "http://localhost/api/v1/posts/206/relationships/author"
        }
      },
      "comments": {
        "links": {
          "related": "http://localhost/api/v1/posts/206/comments",
          "self": "http://localhost/api/v1/posts/206/relationships/comments"
        }
      }
    },
    "links": {
      "self": "http://localhost/api/v1/posts/206"
    }
  }
}
```
