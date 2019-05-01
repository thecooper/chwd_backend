---
title: API Reference

language_tabs:
- bash
- javascript

includes:

search: true

toc_footers:
- <a href='http://github.com/mpociot/documentarian'>Documentation Powered by Documentarian</a>
---
<!-- START_INFO -->
# Info

Welcome to the generated API reference.
[Get Postman Collection](http://localhost/docs/collection.json)

<!-- END_INFO -->

#general
<!-- START_fc1e4f6a697e3c48257de845299b71d5 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "http://localhost/api/users" 
```

```javascript
const url = new URL("http://localhost/api/users");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/users`


<!-- END_fc1e4f6a697e3c48257de845299b71d5 -->

<!-- START_8d1e53fcf4d2d02a6144ed392de856bf -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "http://localhost/api/users/me" 
```

```javascript
const url = new URL("http://localhost/api/users/me");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/users/me`


<!-- END_8d1e53fcf4d2d02a6144ed392de856bf -->

<!-- START_253131dac7435fd0a637e0c82ccea90d -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "http://localhost/api/users/me/news" 
```

```javascript
const url = new URL("http://localhost/api/users/me/news");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/users/me/news`


<!-- END_253131dac7435fd0a637e0c82ccea90d -->

<!-- START_50ccc1c682c655100dd0a4cc1e396800 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "http://localhost/api/users/me/news/1" 
```

```javascript
const url = new URL("http://localhost/api/users/me/news/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`PUT api/users/me/news/{news}`

`PATCH api/users/me/news/{news}`


<!-- END_50ccc1c682c655100dd0a4cc1e396800 -->

<!-- START_52349928d627f207da1c1acdcb1d451a -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "http://localhost/api/users/me/news/1" 
```

```javascript
const url = new URL("http://localhost/api/users/me/news/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`DELETE api/users/me/news/{news}`


<!-- END_52349928d627f207da1c1acdcb1d451a -->

<!-- START_da4bae06ff7db6a87cf881739962a11e -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "http://localhost/api/users/me/ballots" 
```

```javascript
const url = new URL("http://localhost/api/users/me/ballots");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/users/me/ballots`


<!-- END_da4bae06ff7db6a87cf881739962a11e -->

<!-- START_f7d01b74ef5c63a11a54fab889243d2e -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "http://localhost/api/users/me/ballots" 
```

```javascript
const url = new URL("http://localhost/api/users/me/ballots");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`POST api/users/me/ballots`


<!-- END_f7d01b74ef5c63a11a54fab889243d2e -->

<!-- START_5628c4a2a2b2fdc3acf1ef29160a8da6 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "http://localhost/api/users/me/ballots/1" 
```

```javascript
const url = new URL("http://localhost/api/users/me/ballots/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/users/me/ballots/{ballot}`


<!-- END_5628c4a2a2b2fdc3acf1ef29160a8da6 -->

<!-- START_47215edc1708c49bf003cea6e2752fe5 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "http://localhost/api/users/me/ballots/1" 
```

```javascript
const url = new URL("http://localhost/api/users/me/ballots/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`DELETE api/users/me/ballots/{ballot}`


<!-- END_47215edc1708c49bf003cea6e2752fe5 -->

<!-- START_21a97e31cd7aa412fcf041e7728bce78 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "http://localhost/api/users/me/ballots/1/candidates" 
```

```javascript
const url = new URL("http://localhost/api/users/me/ballots/1/candidates");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/users/me/ballots/{ballot}/candidates`


<!-- END_21a97e31cd7aa412fcf041e7728bce78 -->

<!-- START_502630bf3cc939421abfb90035ebd881 -->
## Update the specified resource in storage.

> Example request:

```bash
curl -X PUT "http://localhost/api/users/me/ballots/1/candidates/1" 
```

```javascript
const url = new URL("http://localhost/api/users/me/ballots/1/candidates/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "PUT",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`PUT api/users/me/ballots/{ballot}/candidates/{candidate}`

`PATCH api/users/me/ballots/{ballot}/candidates/{candidate}`


<!-- END_502630bf3cc939421abfb90035ebd881 -->

<!-- START_13c784454f8848b3a7775c77fa9e7f74 -->
## Remove the specified resource from storage.

> Example request:

```bash
curl -X DELETE "http://localhost/api/users/me/ballots/1/candidates/1" 
```

```javascript
const url = new URL("http://localhost/api/users/me/ballots/1/candidates/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "DELETE",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`DELETE api/users/me/ballots/{ballot}/candidates/{candidate}`


<!-- END_13c784454f8848b3a7775c77fa9e7f74 -->

<!-- START_803ce53d33729590656f47169af81b80 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "http://localhost/api/users/me/ballots/1/elections" 
```

```javascript
const url = new URL("http://localhost/api/users/me/ballots/1/elections");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/users/me/ballots/{ballot}/elections`


<!-- END_803ce53d33729590656f47169af81b80 -->

<!-- START_2357c1c1d4b309f1838c5833c7af1142 -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "http://localhost/api/users/me/ballots/1/representatives" 
```

```javascript
const url = new URL("http://localhost/api/users/me/ballots/1/representatives");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/users/me/ballots/{ballot}/representatives`


<!-- END_2357c1c1d4b309f1838c5833c7af1142 -->

<!-- START_076ba18a9143b28ea891d625a431f67b -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "http://localhost/api/elections" 
```

```javascript
const url = new URL("http://localhost/api/elections");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/elections`


<!-- END_076ba18a9143b28ea891d625a431f67b -->

<!-- START_789497c6f076987b54f03803abc3dd25 -->
## Display the specified resource.

> Example request:

```bash
curl -X GET -G "http://localhost/api/elections/1" 
```

```javascript
const url = new URL("http://localhost/api/elections/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/elections/{election}`


<!-- END_789497c6f076987b54f03803abc3dd25 -->

<!-- START_5bb0ba403a8577668cb5e7dfa158a6fe -->
## Display a listing of the resource.

> Example request:

```bash
curl -X GET -G "http://localhost/api/candidates" 
```

```javascript
const url = new URL("http://localhost/api/candidates");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/candidates`


<!-- END_5bb0ba403a8577668cb5e7dfa158a6fe -->

<!-- START_1b759188c9f983d6106bf9be66daec02 -->
## api/candidates/{candidate}
> Example request:

```bash
curl -X GET -G "http://localhost/api/candidates/1" 
```

```javascript
const url = new URL("http://localhost/api/candidates/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response (500):

```json
{
    "message": "Server Error"
}
```

### HTTP Request
`GET api/candidates/{candidate}`


<!-- END_1b759188c9f983d6106bf9be66daec02 -->

<!-- START_12e37982cc5398c7100e59625ebb5514 -->
## Store a newly created resource in storage.

> Example request:

```bash
curl -X POST "http://localhost/api/users" 
```

```javascript
const url = new URL("http://localhost/api/users");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`POST api/users`


<!-- END_12e37982cc5398c7100e59625ebb5514 -->

<!-- START_66e08d3cc8222573018fed49e121e96d -->
## Show the application&#039;s login form.

> Example request:

```bash
curl -X GET -G "http://localhost/login" 
```

```javascript
const url = new URL("http://localhost/login");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response:

```json
null
```

### HTTP Request
`GET login`


<!-- END_66e08d3cc8222573018fed49e121e96d -->

<!-- START_ba35aa39474cb98cfb31829e70eb8b74 -->
## Handle a login request to the application.

> Example request:

```bash
curl -X POST "http://localhost/login" 
```

```javascript
const url = new URL("http://localhost/login");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`POST login`


<!-- END_ba35aa39474cb98cfb31829e70eb8b74 -->

<!-- START_e65925f23b9bc6b93d9356895f29f80c -->
## Log the user out of the application.

> Example request:

```bash
curl -X POST "http://localhost/logout" 
```

```javascript
const url = new URL("http://localhost/logout");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`POST logout`


<!-- END_e65925f23b9bc6b93d9356895f29f80c -->

<!-- START_ff38dfb1bd1bb7e1aa24b4e1792a9768 -->
## Show the application registration form.

> Example request:

```bash
curl -X GET -G "http://localhost/register" 
```

```javascript
const url = new URL("http://localhost/register");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response:

```json
null
```

### HTTP Request
`GET register`


<!-- END_ff38dfb1bd1bb7e1aa24b4e1792a9768 -->

<!-- START_d7aad7b5ac127700500280d511a3db01 -->
## Handle a registration request for the application.

> Example request:

```bash
curl -X POST "http://localhost/register" 
```

```javascript
const url = new URL("http://localhost/register");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`POST register`


<!-- END_d7aad7b5ac127700500280d511a3db01 -->

<!-- START_d72797bae6d0b1f3a341ebb1f8900441 -->
## Display the form to request a password reset link.

> Example request:

```bash
curl -X GET -G "http://localhost/password/reset" 
```

```javascript
const url = new URL("http://localhost/password/reset");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response:

```json
null
```

### HTTP Request
`GET password/reset`


<!-- END_d72797bae6d0b1f3a341ebb1f8900441 -->

<!-- START_feb40f06a93c80d742181b6ffb6b734e -->
## Send a reset link to the given user.

> Example request:

```bash
curl -X POST "http://localhost/password/email" 
```

```javascript
const url = new URL("http://localhost/password/email");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`POST password/email`


<!-- END_feb40f06a93c80d742181b6ffb6b734e -->

<!-- START_e1605a6e5ceee9d1aeb7729216635fd7 -->
## Display the password reset view for the given token.

If no token is present, display the link request form.

> Example request:

```bash
curl -X GET -G "http://localhost/password/reset/1" 
```

```javascript
const url = new URL("http://localhost/password/reset/1");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response:

```json
null
```

### HTTP Request
`GET password/reset/{token}`


<!-- END_e1605a6e5ceee9d1aeb7729216635fd7 -->

<!-- START_cafb407b7a846b31491f97719bb15aef -->
## Reset the given user&#039;s password.

> Example request:

```bash
curl -X POST "http://localhost/password/reset" 
```

```javascript
const url = new URL("http://localhost/password/reset");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "POST",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```


### HTTP Request
`POST password/reset`


<!-- END_cafb407b7a846b31491f97719bb15aef -->

<!-- START_cb859c8e84c35d7133b6a6c8eac253f8 -->
## Show the application dashboard.

> Example request:

```bash
curl -X GET -G "http://localhost/home" 
```

```javascript
const url = new URL("http://localhost/home");

let headers = {
    "Accept": "application/json",
    "Content-Type": "application/json",
}

fetch(url, {
    method: "GET",
    headers: headers,
})
    .then(response => response.json())
    .then(json => console.log(json));
```

> Example response:

```json
null
```

### HTTP Request
`GET home`


<!-- END_cb859c8e84c35d7133b6a6c8eac253f8 -->


