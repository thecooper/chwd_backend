# /users/me [GET]
## Description
Displays the information associated with the user account for the authenticated user
## Response
### 200 - OK
```
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
}
```