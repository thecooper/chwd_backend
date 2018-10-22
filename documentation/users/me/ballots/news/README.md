# /users/me/ballots/{ballot_id}/news [GET]

## Description
Gets all the news articles that relate to all the candidates that qualify for a given ballot based on *ballot_id*

## Responses
### 200 - OK
```
[
    {
        id: number,
        url: string,
        thumbnail_url: string,
        title: string,
        candidate_id: number,
        publish_date: datetime,
        created_at: datetime,
        updated_at: datetime,
        consolidated_candidate: {
          id: number,
          name: string,
          office: string
        }
    },
    { ... }
]
```
### 404 - Ballot not found