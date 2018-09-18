# users/me/ballots/{id}/elections [GET]
## Description
Endpoint that returns the valid elections that are available to the User Ballot based on the granularity of data on the ballot object. For instance, if the *county* property is null on the ballot object, then no races at the county level will be returned.
## Responses
### 200 - OK
```
[
    {
        id: number,
        name: string,
        state_abbreviation: string(2),
        general_election_date: date,
        runoff_election_date: date,
        created_at: datetime,
        updated_at: datetime
    },
    { ... }
]
```
### 401 - Not Authenticated
### 404 - Ballot not found