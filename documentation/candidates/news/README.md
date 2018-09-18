# candidates/{id}/news
Gets news articles related to the candidate selected via id
## Method: GET
## Parameters - Required
**id**: candidate id (Required)
## Response
### 200 - OK
```
{
    url: string,
    thumbnail_url: string,
    title: string,
    description: string,
    publish_date: datetime
}
```