The following is a brief documentation of the endpoints of the API and the return values that will be returned from the API. This is a work in progress, so please be patient.

# Authentication

NOTE: Unless otherwise noted, endpoints require Basic Authentication. You can implement this by passing a base64-encoded string of username password. Example HTTP header:

```
Authorization: Basic base64(username:password)
```

An example of a HTTP header with a user with username someone@google.com and password *thisismypassword*:

```
Authorization: Basic c29tZW9uZUBnb29nbGUuY29tOnRoaXNpc215cGFzc3dvcmQ=
```

the following link can be used to base64 encode strings: https://www.base64encode.org/

# Users/
Available Methods: GET, POST

## GET
Gets the results of users (WIP)

# elections/
## Method: GET
### Parameters
**state** - will filter election results by state (2 letter representation)

### Response

```
{
    id: number,
    name: string,
    state_abbreviation: string(2),
    general_election_date: date,
    runoff_election_date: date
}
```

# elections/{id}
## Method: GET

Select a single election record by id. If record is not found, endpoint should return a 404 error.

## Response
See /elections endpoint

# candidates/
## Method: GET
### Parameters
### Response
```
{
    "id": number,
    "name": string,
    "election_id": number,
    "party_affiliation": string,
    "election_status": "On the Ballot",
    "office": stirng,
    "office_level": "Local"|"State"|"Federal",
    "is_incumbent": 0|1,
    "district_type": string*,
    "district": string,
    "district_identifier": string(4),
    "website_url": string|null,
    "donate_url": string|null,
    "facebook_profile": string|null,
    "twitter_handle": string|null
}
```

# candidates/{id}
## Method: GET
### Parameters
### Response
See candidates/ endpoint

# candidates/{id}/media/news
Gets news articles related to the candidate selected via id
## Method: GET
### Parameters
**id**: candidate id (Required)
### Response
```
{
    url: string,
    thumbnail_url: string,
    title: string,
    description: string,
    publish_date: datetime
}
```