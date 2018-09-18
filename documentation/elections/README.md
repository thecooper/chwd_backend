# elections/
## Method: GET
## Parameters
**state** - will filter election results by state (2 letter representation)

## Response

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
## Description
Select a single election record by id. If record is not found, endpoint should return a 404 error.

## Response
See /elections endpoint