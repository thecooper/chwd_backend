# candidates/
## Method: GET
## Parameters
## Response
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
## Parameters
## Response
See candidates/ endpoint