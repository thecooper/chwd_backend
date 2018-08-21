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
Gets the results of users 
<br>
(WIP)

## POST (Not Authenticated)

# Elections/

Available Methods: GET, POST

## GET

### Query Parameters

| Parameters | Comments |
| ---------- | -------- |
| state      | will filter election results by state (2 letter representation) |

### Returned Fields

| Field Name | Data Type |
| ---------- | --------- |
| id         | unsigned-int |
| created_at | datetime |
| updated_at | datetime |
| name       | string |
| state_abbreviation | string(2) |
| election_date | date |
| is_special | boolean |
| is_runoff | boolean |
| election_type | string |


## POST (For Testing Only, will be removed after going live)

Format: JSON (XML? - untested)

| Field Name | Data Type | Required |
| ---------- | -------- | -------- |
| name | string | yes |
| state_abbreviation | string(2) | yes |
| election_date | date | yes |
| is_special | bool | yes |
| is_runoff | bool | yes |
| election_type | string | no |