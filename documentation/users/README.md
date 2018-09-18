# users/ [GET]
## Description
Used to get all users in the system (TODO: lock this down prior to go-live)
Get all users in the database. Currently available to all authenticated users

## Notes
Child endpoints off of the /users endpoint need to be authenticated, otherwise all calls will receive a 401 Not Authenticated response.

## Responses
### 200 - OK
```
[
    {
        id: number,
        name: string,
        email: string (unique),
        created_at: datetime,
        updated_at: datetime,
        polling_location_address_1: string,
        polling_location_address_2: string,
        polling_location_city: string,
        polling_location_state: string(2),
        polling_location_zip: string,
        polling_location_time_open: datetime,
        polling_location_time_closed: datetime 
    },
    { ... }
]
```

# /users [POST]
## Description
Used for user registration
## Request Body
```
{
    name: string,
    email: string*
    password: string
}
```
*can be any string, doesn't have to be email address

## Responses
### 201 - Created
### 500 - Generic Error
