# users/ [GET]

Get all users in the database. Currently available to all authenticated users

## Response
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

## Request Body
```
{
    name: string,
    email: string*
    password: string
}
```
*can be any string, doesn't have to be email address

## Response Codes
### 201 - User created
### 500 - Generic Error
