# /users/me/ballots [GET]
## Responses
### 200 - OK
```
[
    {
        id: number,
        user_id: number,
        address_line_1: string|null,
        address_line_2: string|null,
        city: string|null,
        zip: string,
        county: string,
        state_abbreviation: string(2),
        congressional_district: string|null,
        state_legislative_district: string|null,
        state_house_district: string|null,
        created_at: datetime,
        updated_at: datetime
    },
    { ... }
]
```
### 401 - Not Authenticated
### 404 - Ballot not found*

# /users/me/ballots/{id} [GET]
## Responses
### 200 - OK
```
{
    id: number,
    user_id: number,
    address_line_1: string|null,
    address_line_2: string|null,
    city: string|null,
    zip: string,
    county: string,
    state_abbreviation: string(2),
    congressional_district: string|null,
    state_legislative_district: string|null,
    state_house_district: string|null,
    created_at: datetime,
    updated_at: datetime
}
```
### 401 - Not Authorized
### 404 - Ballot Not Found*

# /users/me/ballots/ [POST]
## Request
```
{
    address_line_1: string,
    address_line_2: string,
    city: string,
    state: string,
    zip: string
}
```
## Responses
### 201 - Created
```
{
    id: number,
    address_line_1: string|null,
    address_line_2: string|null,
    city: string,
    state_abbreviation: string,
    zip: string,
    county: string|null,
    congressional_district: string|null,
    state_legislative_district: string|null,
    state_house_district: string|null,
    created_at: datetime,
    updated_at: datetime,
}
```
### 401 - Unathenticated
### 500 - Generic Error

# /users/me/ballots/{id} [DELETE]
## Responses
### 202 - Command Accepted
### 401 - Unathenticated
### 404 - Ballot not found*

<hr/>
*or ballot doesn't belong to authenticated user