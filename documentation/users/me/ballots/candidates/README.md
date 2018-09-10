# /users/me/ballots/{ballot_id}/candidates [GET]
## Description
Used to get all the candidates that are relevant to a ballot's available data, as well as their selection status on the ballot
## Responses
### 200 - OK
```
[
    {
        "district_type": [
            "race": [
                {
                    "id": number,
                    "name": string,
                    "election_id": number,
                    "party_affiliation": string,
                    "election_status": "On the Ballot",
                    "office": stirng,
                    "office_level": "Local"|"State"|"Federal",
                    "is_incumbent": 0|1,
                    "district_type": string,
                    "district": string,
                    "district_identifier": string(4),
                    "website_url": string|null,
                    "donate_url": string|null,
                    "facebook_profile": string|null,
                    "twitter_handle": string|null,
                    "selected": boolean
                }
            ]
        ]
    }
]
```
### 404 - Ballot not found or Candidate not found
### 401 - Not Authorized

# /users/me/ballots/{ballot_id}/candidates/{candidate_id} [PUT]
## Description
Used to select a candidate on the ballot based on *ballot_id*. This endpoint will de-select other candidates in the same race and select the candidate that was provided as the *candidate_id*.

## Resposnes
### 201 - Created
### 404 - Ballot not found or Candidate not found
### 401 - Not Authorized

# /users/me/ballots/{ballot_id}/candidates/{candidate_id} [DELETE]

## Description
Used to de-select a specific candidate from the ballot.

## Responses
### 201 - Created
### 404 - Ballot not found or Candidate not found or Candidate was not selected on the ballot
### 401 - Not Authorized